<?php

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

function isNeighbour($a, $b) {
    $a = explode(',', $a);
    $b = explode(',', $b);
    
    return ($a[0] == $b[0] && abs($a[1] - $b[1]) == 1) || 
           ($a[1] == $b[1] && abs($a[0] - $b[0]) == 1) || 
           ($a[0] + $a[1] == $b[0] + $b[1]);
}

function hasNeighBour($a, $board) {
    foreach (array_keys($board) as $b) {
        if (isNeighbour($a, $b)) {
            return true;
        }
    }
}

function neighboursAreSameColor($player, $a, $board) {
    foreach ($board as $b => $st) {
        if (!$st) {
            continue;
        }
        $c = $st[count($st) - 1][0];
        if ($c != $player && isNeighbour($a, $b)) {
            return false;
        }
    }
    return true;
}

function len($tile) {
    return $tile ? count($tile) : 0;
}

function slide($board, $from, $to) {
    if (!hasNeighBour($to, $board) || !isNeighbour($from, $to)) {
        return false;
    }
    $b = explode(',', $to);
    $common = [];
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $b[0] + $pq[0];
        $q = $b[1] + $pq[1];
        if (isNeighbour($from, $p.",".$q)) {
            $common[] = $p.",".$q;
        }
    }
    if (!isset($board[$common[0]])
        && !isset($board[$common[1]])
        && !isset($board[$from])
        && !isset($board[$to])
    ) {
        return false;
    }
    return min(len($board[$common[0]] ?? []), len($board[$common[1]] ?? [])) <= max(len($board[$from] ?? []), len($board[$to] ?? []));
}

function moveGrasshopper($board, $from, $to) {
    $from = explode(',', $from);
    $to = explode(',', $to);
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        // if first tile is empty, return false
        $p = $from[0] + $pq[0];
        $q = $from[1] + $pq[1];
        if (!isset($board["$p,$q"])) {
            return false;
        }
        // return true if first upcoming empty tile is the destination
        while (isset($board["$p,$q"])) {
            $p += $pq[0];
            $q += $pq[1];
            if ($p == $to[0] && $q == $to[1] && !isset($board["$p,$q"])) {
                return true;
            }
        }
        return false;
    }
}

function moveAnt($board, $from, $to) {
    $from = explode(',', $from);
    $to = explode(',', $to);
    // cannot move to an occupied field
    if (isset($board["$to[0],$to[1]"])) {
        return false;
    }
    $visited = [];
    $queue = [$from];
    // use BFS to find path, if path cannot be found, return false
    return antBfs($board, $from, $to, $visited, $queue);
}

function antBfs($board, $from, $to, $visited, $queue) {
    // can move using slide function
    if (slide($board, "$from[0],$from[1]", "$to[0],$to[1]")) {
        return true;
    }
    $visited[] = $from;
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $from[0] + $pq[0];
        $q = $from[1] + $pq[1];
        if (isset($board["$p,$q"]) && !in_array([$p, $q], $visited)) {
            $queue[] = [$p, $q];
        }
    }
    if ($queue) {
        return antBfs($board, array_shift($queue), $to, $visited, $queue);
    }
    return false;
}

function moveSpider($board, $from, $to) {
    $from = explode(',', $from);
    $to = explode(',', $to);
    // cannot move to an occupied field
    if (isset($board["$to[0],$to[1]"])) {
        return false;
    }
    $visited = [];
    $queue = [$from];
    // use BFS to find a path of max 3 steps
    return spiderBfs($board, $from, $to, $visited, $queue, 3);
}

function spiderBfs($board, $from, $to, $visited, $queue, $steps) {
    if ($steps < 0) {
        return false;
    }
    if ($from[0] == $to[0] && $from[1] == $to[1] && $steps == 0) {
        return true;
    }
    $visited[] = $from;
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $from[0] + $pq[0];
        $q = $from[1] + $pq[1];
        if (isset($board["$p,$q"]) && !in_array([$p, $q], $visited)) {
            $queue[] = [$p, $q];
        }
    }
    if ($queue) {
        return spiderBfs($board, array_shift($queue), $to, $visited, $queue, $steps - 1);
    }
}

function getAvailablePieces($hand) {
    $available = [];
    foreach ($hand as $piece => $count) {
        if ($count) {
            $available[] = $piece;
        }
    }
    return $available;
}

function getValidPlacements($player, $hand, $board) {
    $validPlacements = [];
    // if boeard is empty, return center position
    if (!$board) {
        return ['0,0'];
    }
    foreach ($board as $pos => $tile) {
        // for surrounding empty positions
        $neighbours = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = explode(',', $pos)[0] + $pq[0];
            $q = explode(',', $pos)[1] + $pq[1];
            if (array_sum($hand) < 11 && !neighboursAreSameColor($player, "$p,$q", $board)) {
                continue;
            }
            if (!isset($board["$p,$q"])) {
                $neighbours[] = "$p,$q";
            }
        }
        $validPlacements = array_merge($validPlacements, $neighbours);
    }
    return $validPlacements;
}

function getOwnTiles($player, $board) {
    $ownTiles = [];
    foreach ($board as $pos => $tile) {
        if ($tile && $tile[count($tile) - 1][0] == $player) {
            $ownTiles[] = $pos;
        }
    }
    return $ownTiles;
}

function mayPass($player, $hand, $board) {
    if (array_sum($hand) > 0) {
        return false;
    }
    $ownTiles = getOwnTiles($player, $board);
    // get all empty neighbour positions
    $validPlacements = getValidPlacements($player, $hand, $board);
    // for every tile, check if it can be placed at any empty neighbour position
    foreach ($ownTiles as $pos) {
        foreach ($validPlacements as $placement) {
            // check tile type and if it can be placed at the position
            if (isset($board[$pos]) && ($board[$pos][count($board[$pos]) - 1][1] == "Q" || $board[$pos][count($board[$pos]) - 1][1] == "B")) {
                if (slide($board, $pos, $placement)) {
                    return false;
                }
            } elseif ($board[$pos][count($board[$pos]) - 1][1] == "G") {
                if (moveGrasshopper($board, $pos, $placement)) {
                    return false;
                }
            } elseif ($board[$pos][count($board[$pos]) - 1][1] == "A") {
                if (moveAnt($board, $pos, $placement)) {
                    return false;
                }
            } elseif ($board[$pos][count($board[$pos]) - 1][1] == "S") {
                if (moveSpider($board, $pos, $placement)) {
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 * @param array $board
 * @return int 1 if white wins, 2 if black wins, 3 if the game ended in a draw, 0 if the game is not over
 */
function endOfGame($board) {
    // find queens of both players
    $whiteQueen = null;
    $blackQueen = null;
    $winState = 0;
    foreach ($board as $pos => $tile) {
        if ($tile && $tile[count($tile) - 1][1] == "Q") {
            if ($tile[count($tile) - 1][0] == 0) {
                $whiteQueen = $pos;
            } else {
                $blackQueen = $pos;
            }
        }
    }
    // if one of the queens is surrounded by tiles, the other player wins
    if ($whiteQueen && !hasNeighBour($whiteQueen, $board)) {
        $winState = 2;
    }
    if ($blackQueen && !hasNeighBour($blackQueen, $board)) {
        $winState = 1;
    }
    // if both queens are surrounded by tiles, the game is a draw
    if ($whiteQueen && $blackQueen && !hasNeighBour($whiteQueen, $board) && !hasNeighBour($blackQueen, $board)) {
        $winState = 3;
    }
    return $winState;
}
    
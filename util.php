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
    // TODO: implement
}

function moveSpider($board, $from, $to) {
    // TODO: implement
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
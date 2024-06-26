<?php

session_start();

include_once 'util.php';

$from = $_POST['from'];
$to = $_POST['to'];

$player = $_SESSION['player'];
$board = $_SESSION['board'];
$hand = $_SESSION['hand'][$player];
unset($_SESSION['error']);

if (!isset($board[$from])) {
    $_SESSION['error'] = 'Board position is empty';
} elseif ($board[$from][count($board[$from])-1][0] != $player) {
    $_SESSION['error'] = "Tile is not owned by player";
} elseif ($hand['Q']){
    $_SESSION['error'] = "Queen bee is not played";
} else {
    $tile = array_pop($board[$from]);
    if (!hasNeighBour($to, $board)) {
        $_SESSION['error'] = "Move would split hive";
    } else {
        $all = array_keys($board);
        $queue = [array_shift($all)];
        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                if (in_array("$p,$q", $all)) {
                    $queue[] = "$p,$q";
                    $all = array_diff($all, ["$p,$q"]);
                }
            }
        }
        if ($all) {
            $_SESSION['error'] = "Move would split hive";
        } else {
            if ($from == $to) {
                $_SESSION['error'] = 'Tile must move';
            } elseif (isset($board[$to]) && len($board[$to]) > 0 && $tile[1] != "B") {
                $_SESSION['error'] = 'Tile not empty';
            } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                if (!slide($board, $from, $to)) {
                    $_SESSION['error'] = 'Tile must slide';
                }
            } elseif (!$tile[1] == "G") {
                if (!moveGrasshopper($board, $from, $to)) {
                    $_SESSION['error'] = 'Grasshopper cannot move to this position';
                }
            } elseif (!$tile[1] == "A") {
                if (!moveAnt($board, $from, $to)) {
                    $_SESSION['error'] = 'Ant cannot move to this position';
                }
            } elseif (!$tile[1] == "S") {
                if (!moveSpider($board, $from, $to)) {
                    $_SESSION['error'] = 'Spider cannot move to this position';
                }
            }
        }
    }
    if (isset($_SESSION['error'])) {
        if (isset($board[$from])) {
            array_push($board[$from], $tile);
        } else {
            $board[$from] = [$tile];
        }
        header('Location: index.php');
        exit();
    } else {
        if (isset($board[$to])) {
            array_push($board[$to], $tile);
        } else {
            $board[$to] = [$tile];
        }
        $_SESSION['player'] = 1 - $_SESSION['player'];        
        $db = include_once 'database.php';
        $stmt = $db->prepare(
            'INSERT INTO moves
                (game_id, type, move_from, move_to, previous_id, state)
                VALUES (?, "move", ?, ?, ?, ?)'
        );
        $state = getState();
        $stmt->bind_param('issis', $_SESSION['game_id'], $from, $to, $_SESSION['last_move'], $state);
        $stmt->execute();
        $_SESSION['last_move'] = $db->insert_id;
    }
    $_SESSION['board'] = $board;
    // Check if the game is over
    $endOfGameStatus = endOfGame($board);
    if ($endOfGameStatus) {
        switch ($endOfGameStatus) {
            case 1:
                $_SESSION['error'] = 'White wins';
                break;
            case 2:
                $_SESSION['error'] = 'Black wins';
                break;
            case 3:
                $_SESSION['error'] = 'Draw';
                break;
        }
        header('Location: index.php');
        exit();
    }
}

header('Location: aimove.php');
<?php

session_start();

$db = include_once 'database.php';
if ($_SESSION['last_move'] == 0) {
    $_SESSION['error'] = 'No moves to undo';
    header('Location: index.php');
    return;
}
$stmt = $db->prepare('SELECT * FROM moves WHERE id = ?');
$stmt->bind_param('i', $_SESSION['last_move']);
$stmt->execute();
$result = $stmt->get_result();
$move = $result->fetch_assoc();
$board = $_SESSION['board'];
$hand = $_SESSION['hand'];
$player = $_SESSION['player'];
if ($move['type'] == 'move') {
    $from = $move['move_from'];
    $to = $move['move_to'];
    $tile = array_pop($board[$to]);
    if (isset($board[$from])) {
        array_push($board[$from], $tile);
    } else {
        $board[$from] = [$tile];
    }
    $_SESSION['player'] = 1 - $_SESSION['player'];
} else {
    $to = $move['move_to'];
    $piece = $move['move_from'];
    $hand[$player][$piece]++;
    unset($board[$to]);
    $_SESSION['player'] = 1 - $_SESSION['player'];
}
$stmt = $db->prepare('DELETE FROM moves WHERE id = ?');
$stmt->bind_param('i', $_SESSION['last_move']);
$stmt->execute();
$_SESSION['last_move'] = $move['previous_id'];
$_SESSION['board'] = $board;
$_SESSION['hand'] = $hand;
header('Location: index.php');

?>

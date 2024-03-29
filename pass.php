<?php

session_start();

include_once 'util.php';
if (!mayPass($_SESSION['player'], $_SESSION['hand'], $_SESSION['board'])) {
    $_SESSION['error'] = 'You shall not pass!';
    header('Location: index.php');
    exit();
}
$db = include_once 'database.php';
$stmt = $db->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');
$state = getState();
$stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], $state);
$stmt->execute();
$_SESSION['last_move'] = $db->insert_id;
$_SESSION['player'] = 1 - $_SESSION['player'];

header('Location: aimove.php');
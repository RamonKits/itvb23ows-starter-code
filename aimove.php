<?php

session_start();
$db = include_once 'database.php';

$ch = curl_init('hive-ai:5000');
$body = json_encode(['move_number' => $_SESSION['last_move'], 'board' => $_SESSION['board'], 'hand' => $_SESSION['hand']]);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => $body
]);
$response = json_decode(curl_exec($ch), true);

$state = getState();
$stmt = $db->prepare( 'INSERT INTO moves (game_id, type, move_from, move_to, previous_id, state) VALUES (?, ?,  ?, ?, ?, ?)');

if ($response[0] == 'pass') {
    $stmt->bind_param('isssis', $_SESSION['game_id'], $response[0], null, null, $_SESSION['last_move'], $state);
} elseif ($response[0] == 'play') {
    $_SESSION['board'][$response[2]] = [[$_SESSION['player'], $response[1]]];
    $_SESSION['hand'][$_SESSION['player']][$response[1]]--;
    $stmt->bind_param('isssis', $_SESSION['game_id'], $response[0], $response[1], $response[2], $_SESSION['last_move'], $state);
} else {
    $tile = array_pop($_SESSION['board'][$response[1]]);
    if (isset($_SESSION['board'][$response[2]])) {
        array_push($_SESSION['board'][$response[2]], $tile);
    } else {
        $_SESSION['board'][$response[2]] = [$tile];
    }
    $stmt->bind_param('isssis', $_SESSION['game_id'], $response[0], $response[1], $response[2], $_SESSION['last_move'], $state);
}
$stmt->execute();
$_SESSION['player'] = 1 - $_SESSION['player'];
$_SESSION['last_move'] = $db->insert_id;

header('Location: index.php');
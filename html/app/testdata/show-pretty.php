<?php
// Convert raw json to pretty json
// Input argv[1] name of file to convert

$file = $argv[1];

$raw = file_get_contents($file);

$processed = json_decode($raw, true);

echo json_encode($processed, JSON_PRETTY_PRINT);

?>

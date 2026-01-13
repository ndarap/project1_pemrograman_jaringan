<?php
require __DIR__ . '/../vendor/autoload.php';


use Google\Client;

$client = new Client();
$client->setAuthConfig(__DIR__ . '/credentials.json');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->setRedirectUri('http://localhost/emeeting_db/public/google-callback.php');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
?>

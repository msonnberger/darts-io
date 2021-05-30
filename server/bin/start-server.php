<?php
// IMPRESSUM:
// (c) Martin Sonnberger 2021
// Dieses Projekt ist im Rahmen des 
// MultiMediaTechnology Bachelorstudiums
// an der FH Salzburg entstanden.
// Kontakt: msonnberger.mmt-b2020@fh-salzburg.ac.at

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use RemoteDarts\App;

require dirname(__DIR__) . '/vendor/autoload.php';

$PORT = 9000;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new App()
        )
    ),
    $PORT
);
echo "Listening on Port $PORT..\n";
$server->run();

?>
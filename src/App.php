<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface as Conn;
use Ds\Set;

class App implements MessageComponentInterface {
    protected $clients;
    private $rooms;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = array();
    }

    public function onOpen(Conn $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
        echo $this->clients[$conn];
    }

    public function onMessage(Conn $from, $msg) {
        $msgObj = json_decode($msg);
        $cmd = $msgObj->cmd;

        switch ($cmd) {
            case 'joinRoom':
                $roomId = $msgObj->id;
                $this->joinRoom($from, $roomId);
                break;
            case 'createRoom':
                $this->createRoomAndJoin($from);
                break;
            case 'leaveRoom':
                $this->leaveRoom($from);
                break;
            case 'msg':
                $this->broadcast($msgObj);
                break;
            default:
                $this->sendError($from, 'This command does not exist.');
                break;
        }
    }

    public function onClose(Conn $conn) {
        if ($this->clients[$conn] !== null) {
            $this->leaveRoom($conn);
        }
        
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(Conn $conn, \Exception $e) {
        echo "Something went wrong: {$e->getMessage()}\n";

        $conn->close();
    }

    private function broadcastRoom($roomId, $msg) {
        $clients = $this->rooms[$roomId];

        foreach ($clients as $client) {
            $client->send(json_encode($msg));
        }
    }

    private function broadcast($msg) {
        foreach ($this->clients as $client) {
            $client->send(json_encode($msg));
        }
    }

    private function getRandomString($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) { 
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    private function createRoomAndJoin(Conn $client) {
        do {
            $roomId = $this->getRandomString(6);
        } while (array_key_exists($roomId, $this->rooms));
        
        $this->rooms[$roomId] = new Set();
        echo "$client->resourceId created Room $roomId\n";

        $this->joinRoom($client, $roomId);

        $successMsg = array('cmd' => 'createdRoom', 'id' => $roomId);
        $this->broadcastRoom($roomId, $successMsg);
    }

    private function joinRoom(Conn $client, $roomId) {
        if (array_key_exists($roomId, $this->rooms)) {
            if ($this->clients[$client] === null) {
                $this->rooms[$roomId]->add($client);
                $this->clients[$client] = $roomId;

                $successMsg = array('cmd' => 'joinedRoom', 'roomId' => $roomId, 'client' => $client->resourceId);
                $this->broadcastRoom($roomId, $successMsg);
                echo "$client->resourceId joined Room $roomId\n";
            } else {
                $this->sendError($client, 'You are already connected to another room.');
            }
        } else {
            $this->sendError($client, 'This room does not exist.');
        }
    }

    private function leaveRoom(Conn $client) {
        if ($this->clients[$client] !== null) {
            $roomId = $this->clients[$client];
            $this->rooms[$roomId]->remove($client);
            $successMsg = array('cmd' => 'leftRoom', 'roomId' => $roomId, 'client' => $client->resourceId);
            $this->broadcastRoom($roomId, $successMsg);
        } else {
            $this->sendError($client, 'You are currently in no room.');
            return;
        }
        
        if ($this->rooms[$roomId]->isEmpty()) {
            unset($this->rooms[$roomId]);
        }
        $this->clients[$client] = null;
        echo "$client->resourceId left Room $roomId\n";
    }

    private function sendError (Conn $client, $errorMessage) {
        $error = array('cmd' => 'error', 'errorMessage' => $errorMessage);
        $client->send(json_encode($error));
        echo "ERROR: $errorMessage\n";
    }
}

?>
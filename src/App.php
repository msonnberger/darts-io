<?php
namespace RemoteDarts;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface as Conn;
use RemoteDarts\GameRoom;
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
            case 'score':
                $from->send($msg);
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
        $clients = $this->rooms[$roomId]->getPlayers();

        foreach ($clients as $client) {
            $client->send(json_encode($msg));
        }
    }

    private function broadcast($msg) {
        foreach ($this->clients as $client) {
            $client->send(json_encode($msg));
        }
    }

    private function createRoomAndJoin(Conn $client) {
        $room = new GameRoom($client);
        $roomId = $room->getId();
        $this->rooms[$roomId] = $room;
        $this->clients[$client] = $roomId;
        
        $successMsg = array('cmd' => 'createdRoom', 'id' => $roomId);
        $this->broadcastRoom($roomId, $successMsg);
    }

    private function joinRoom(Conn $client, $roomId) {
        if (array_key_exists($roomId, $this->rooms)) {
            if ($this->clients[$client] === null) {
                try {
                    $this->rooms[$roomId]->addPlayer($client);
                    $this->clients[$client] = $roomId;

                    $successMsg = array('cmd' => 'joinedRoom', 'roomId' => $roomId, 'client' => $client->resourceId);
                    $this->broadcastRoom($roomId, $successMsg);
                } catch (\Exception $e) {
                    $this->sendError($client, $e->getMessage());
                }
                
            } else {
                $this->sendError($client, 'You are already connected to a room.');
            }
        } else {
            $this->sendError($client, 'This room does not exist.');
        }
    }

    private function leaveRoom(Conn $client) {
        if ($this->clients[$client] !== null) {
            $roomId = $this->clients[$client];
            $this->rooms[$roomId]->removePlayer($client);
            $successMsg = array('cmd' => 'leftRoom', 'roomId' => $roomId, 'client' => $client->resourceId);
            $this->broadcastRoom($roomId, $successMsg);
        } else {
            $this->sendError($client, 'You are currently in no room.');
            return;
        }
        
        if (count($this->rooms[$roomId]->getPlayers()) === 0) {
            unset($this->rooms[$roomId]);
            echo "Room $roomId deleted\n";
        }
        $this->clients[$client] = null;
    }

    private function sendError (Conn $client, $errorMessage) {
        $error = array('cmd' => 'error', 'errorMessage' => $errorMessage);
        $client->send(json_encode($error));
        echo "ERROR: $errorMessage\n";
    }
}

?>
<?php
namespace RemoteDarts;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface as Conn;
use RemoteDarts\GameRoom;

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
                $this->createRoomAndJoin($from, $msgObj->settings);
                break;
            case 'leaveRoom':
                $this->leaveRoom($from);
                break;
            case 'throw':
                $this->addThrow($from, $msgObj->throws);
                break;
            case 'newGame':
                $this->newGame($from);
                break;
            case 'changeSettings':
                $this->changeSettings($from, $msgObj->settings);
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
            $client->send($msg);
        }
    }

    private function broadcastRoomExceptFrom(Conn $from, $msg) {
        $roomId = $this->clients[$from];
        $clients = $this->rooms[$roomId]->getPlayers();

        foreach ($clients as $client) {
            if ($client->resourceId !== $from->resourceId) {
                $client->send($msg);
            }
        }
    }

    private function createRoomAndJoin(Conn $client, $settings) {
        if ($this->clients[$client] === null) {
            $room = new GameRoom($client, $settings->pointMode, $settings->outMode, $settings->inMode);
            $roomId = $room->getId();
            $this->rooms[$roomId] = $room;
            $this->clients[$client] = $roomId;
            $msgContent = array('id' => $roomId);
            $this->broadcastRoom($roomId, $this->createMessageString('createdRoom', $msgContent));
        } else {
            $this->sendError($client, 'You are already connected to a room.');
        }
    }

    private function joinRoom(Conn $client, $roomId) {
        if (array_key_exists($roomId, $this->rooms)) {
            if ($this->clients[$client] === null) {
                try {
                    $room = $this->rooms[$roomId];
                    $room->addPlayer($client);
                    $this->clients[$client] = $roomId;
                    $settings = $room->getSettings();
                    $scoreboards = $room->getScores($client);

                    $msgContent = array('roomId' => $roomId, 'settings' => $settings, 'scoreboards' => $scoreboards);
                    $client->send($this->createMessageString('joinedRoom', $msgContent));
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
            $msgContent = array('roomId' => $roomId);
            $client->send($this->createMessageString('leftRoom', $msgContent));
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

    private function addThrow(Conn $client, $throw) {
        $room = $this->getClientRoom($client);

        try {
            $scoreboard = $room->newScore($client, $throw);
            $msgContent = array('scoreboard' => $scoreboard, 'isOwn' => true);
            $client->send($this->createMessageString('scoreboard', $msgContent));
            $msgContent = array('scoreboard' => $scoreboard, 'isOwn' => false);
            $this->broadcastRoomExceptFrom($client, $this->createMessageString('scoreboard', $msgContent));
        } catch (\Exception $e) {
            $this->sendError($client, $e->getMessage());
        }
    }

    private function newGame(Conn $client) {
        $room = $this->getClientRoom($client);
        $room->newGame();

        $msgContent = array('settings' => $room->getSettings());
        $this->broadcastRoom($room->getId(), $this->createMessageString('newGame', $msgContent));
    }

    private function changeSettings(Conn $client, $settings) {
        $room = $this->getClientRoom($client);
        $room->changeSettings($settings);
    }

    private function getClientRoom(Conn $client) {
        return $this->rooms[$this->clients[$client]];
    }

    private function sendError(Conn $client, $errorMessage, $isFatal = false) {
        $error = array('cmd' => 'error', 'errorMessage' => $errorMessage, 'isFatal' => $isFatal);
        $client->send(json_encode($error));
        echo "ERROR: $errorMessage\n";
    }

    private function createMessageString($messageCommand, $content) {
        $msg = array('cmd' => $messageCommand, 'content' => $content);
        return json_encode($msg);
    }
}

?>
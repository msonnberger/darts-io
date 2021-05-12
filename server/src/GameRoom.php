<?php
namespace RemoteDarts;
use Ratchet\ConnectionInterface as Conn;
use stdClass;

class GameRoom {
    private $id;
    private $players;
    private $settings;
    private $game;

    public function __construct(Conn $player1, $points = 501, $outMode = "double", $inMode = "single") {
        $this->id = $this->getRandomString(6);
        echo "Created Room {$this->getId()}\n";
        
        $this->settings = array();
        $this->settings['pointMode'] = $points;
        $this->settings['outMode'] = $outMode;
        $this->settings['inMode'] = $inMode;
        $this->players = array();
        $this->addPlayer($player1);

        $this->game = new Game($this->settings);
    }

    public function getPlayers() {
        return $this->players;
    }

    public function getScores() {
        return $this->game->getScoreboards();
    }

    public function getScore(Conn $player) {
        $playerIndex = array_search($player, $this->players);
        return $this->game->getScoreboard($playerIndex);
    }

    public function getId() {
        return $this->id;
    }

    public function addPlayer(Conn $player) {
        if (count($this->players) >= 2) {
            throw new \Exception('Player Limit is reached!');
        }
        $this->players[] = $player;
        echo "Connection $player->resourceId joined Room {$this->getId()}\n";
    }

    public function removePlayer(Conn $player) {
        if (count($this->players) === 0) {
            throw new \Exception('No Players in this Room!');
        }
        unset($this->players[$player->resourceId]);
        echo "Connection $player->resourceId left Room {$this->getId()}\n";
    }

    public function newScore(Conn $player, $throws) {
        $playerIndex = array_search($player, $this->players);
        return $this->game->newScore($playerIndex, $throws);
    }

    public function changeSettings($pointMode, $outMode, $inMode) {
        $this->settings['pointMode'] = $pointMode;
        $this->settings['outMode'] = $outMode;
        $this->settings['inMode'] = $inMode;
    }

    public function newGame() {
        $this->game = new Game($this->settings);
    }

    private function getRandomString($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) { 
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}
?>
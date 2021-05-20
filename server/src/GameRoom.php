<?php
// IMPRESSUM:
// (c) Martin Sonnberger 2021
// Dieses Projekt ist im Rahmen des 
// MultiMediaTechnology Bachelorstudiums
// an der FH Salzburg entstanden.
// Kontakt: msonnberger.mmt-b2020@fh-salzburg.ac.at

namespace RemoteDarts;
use Ratchet\ConnectionInterface as Conn;

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

    public function getScores(Conn $player) {
        $playerIndex = array_search($player, $this->players);
        $scoreboards = array();
        $scoreboards[0] = $this->game->getScoreboard($playerIndex);
        $scoreboards[1] = $this->game->getScoreboard($playerIndex === 0 ? 1 : 0);
        return $scoreboards;
    }

    public function getScore(Conn $player) {
        $playerIndex = array_search($player, $this->players);
        return $this->game->getScoreboard($playerIndex);
    }

    public function getId() {
        return $this->id;
    }

    public function getSettings() {
        return $this->settings;
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
        $index = array_search($player, $this->players);
        unset($this->players[$index]);
        $this->players = array_values($this->players); 
        echo "Connection $player->resourceId left Room {$this->getId()}\n";
    }

    public function newScore(Conn $player, $throws) {
        $playerIndex = array_search($player, $this->players);
        return $this->game->newScore($playerIndex, $throws);
    }

    public function changeSettings($settings) {
        $this->settings['pointMode'] = $settings->pointMode;
        $this->settings['outMode'] = $settings->outMode;
        $this->settings['inMode'] = $settings->inMode;
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
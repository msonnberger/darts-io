<?php
namespace RemoteDarts;
use Ratchet\ConnectionInterface as Conn;

class GameRoom {
    private $id;
    private $players;
    private $scores;
    private $pointMode;
    private $outMode;
    private $inMode;

    public function __construct(Conn $player1, $points = 501, $outMode = "double", $inMode = "single") {
        $this->id = $this->getRandomString(6);
        echo "Created Room {$this->getId()}\n";
        $this->scores = array();
        $this->pointMode = $points;
        $this->outMode = $outMode;
        $this->inMode = $inMode;
        $this->players = array();
        $this->addPlayer($player1);
    }

    public function getPlayers() {
        return $this->players;
    }

    public function getScores() {
        return $this->scores;
    }

    public function getScore(Conn $player) {
        return $this->scores[$player->resourceId];
    }

    private function setScore(Conn $player, $score) {
        $this->scores[$player->resourceId] = $score;
    }

    public function getId() {
        return $this->id;
    }

    public function addPlayer(Conn $player) {
        if (count($this->players) >= 2) {
            throw new \Exception('Player Limit is reached!');
        }
        $this->players[$player->resourceId] = $player;
        $this->scores[$player->resourceId] = $this->pointMode;
        echo "Connection $player->resourceId joined Room {$this->getId()}\n";
    }

    public function removePlayer(Conn $player) {
        if (count($this->players) === 0) {
            throw new \Exception('No Players in this Room!');
        }
        unset($this->players[$player->resourceId]);
        unset($this->scores[$player->resourceId]);
        echo "Connection $player->resourceId left Room {$this->getId()}\n";
    }

    public function newScore(Conn $player, $throws) {
        $score = $this->calcScore($throws);
        if ($score === -1) {
            throw new \Exception('Invalid Score!');
        }

        $currScore = $this->getScore($player);

        // first throw
        if ($currScore === $this->pointMode) {
            $firstMulti = $throws[0]->multiplier;

            if ($this->inMode === 'double' and $firstMulti !== 2) {
                throw new \Exception('No Score!');
            } else if ($this->inMode === 'triple' and $firstMulti !== 3) {
                throw new \Exception('No Score!');
            } else if ($this->inMode === 'master' and ($firstMulti !== 2 or $firstMulti !== 3)) {
                throw new \Exception('No Score!');
            }
        }

        if ($currScore - $score < 0 or $currScore - $score === 1) {
            throw new \Exception('No Score');
        }

        // last throw
        if ($currScore - $score === 0) {
            $lastMulti = $throws[2]->multiplier;

            if ($this->outMode === 'double' and $lastMulti !== 2) {
                throw new \Exception('No Score!');
            } else if ($this->outMode === 'triple' and $lastMulti !== 3) {
                throw new \Exception('No Score!');
            } else if ($this->outMode === 'master' and ($lastMulti !== 2 or $lastMulti !== 3)) {
                throw new \Exception('No Score!');
            }
        }

        $this->setScore($player, $currScore - $score);
    }

    private function calcScore($throws) {
        $score = 0;
        $invalidScores = array(163, 166, 169, 172, 173, 175, 176, 178, 179);
        
        foreach ($throws as $throw) {
            $multi = $throw->multiplier;
            $value = $throw->value;

            if (!is_int($multi) or $multi < 1 or $multi > 3) {
                return -1;
            }
            if (!is_int($value) or $value < 0 or ($value > 20 and $value !== 25 and $value !== 50)) {
                return -1;
            }

            $score += $multi * $value;
        }

        if (in_array($score, $invalidScores)) {
            return -1;
        }

        return $score;
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
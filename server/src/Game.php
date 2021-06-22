<?php
// IMPRESSUM:
// (c) Martin Sonnberger 2021
// Dieses Projekt ist im Rahmen des 
// MultiMediaTechnology Bachelorstudiums
// an der FH Salzburg entstanden.
// Kontakt: msonnberger.mmt-b2020@fh-salzburg.ac.at

namespace RemoteDarts;

class Game {
    private $scoreboards;
    private $rounds;
    private $pointMode;
    private $outMode;
    private $inMode;

    public function __construct($settings) {
        $points = $settings['pointMode'];
        $this->scoreboards = array();
        $this->scoreboards[0] = array();
        $this->scoreboards[1] = array();

        $this->scoreboards[0]['points'] = $points;
        $this->scoreboards[1]['points'] = $points;

        $this->scoreboards[0]['highScorings'] = array('180' => 0, '160' => 0, '140' => 0, '120' => 0, '100' => 0);
        $this->scoreboards[1]['highScorings'] = array('180' => 0, '160' => 0, '140' => 0, '120' => 0, '100' => 0);
        
        $this->rounds = array(0, 0);
        $this->pointMode = $points;
        $this->outMode = $settings['outMode'];
        $this->inMode = $settings['inMode'];
    }

    public function __destruct() {

        if ($this->scoreboards[0]['points'] === 0 || $this->scoreboards[1]['points'] === 0) {
            include "functions.php";
            $sth = $dbh->prepare("INSERT INTO game(point_mode, in_mode, out_mode, rounds_player1, rounds_player2, avg_player1, avg_player2) VALUES (?, ?, ?, ?, ?, ?, ?);");

            $point_mode = intval($this->pointMode);
            $in_mode = $this->inMode;
            $out_mode = $this->outMode;
            $rounds_player1 = $this->rounds[0];
            $rounds_player2 = $this->rounds[1];
            $avg_player1 = $this->scoreboards[0]['average'];
            $avg_player2 = $this->scoreboards[1]['average'];
            $values = array($point_mode, $in_mode, $out_mode, $rounds_player1, $rounds_player2, $avg_player1, $avg_player2);

            $sth->execute($values);
        }
    }

    public function getScoreboards() {
        return $this->scoreboards;
    }

    public function getScoreboard($index) {
        return $this->scoreboards[$index];
    }

    public function newScore($playerIndex, $throws) {
        if ($playerIndex !== 0 && $playerIndex !== 1) {
            throw new \Exception('Dieser Spieler existiert nicht.');
        }

        if ($playerIndex === 0) {
            if ($this->rounds[0] !== $this->rounds[1]) {
                throw new \Exception('Du bist nicht an der Reihe');
            }
        } else {
            if ($this->rounds[1] !== $this->rounds[0] - 1) {
                throw new \Exception('Du bist nicht an der Reihe.');
            }
        }

        $score = $this->calcScore($throws);
        $realScore = $score;

        if ($score === -1) {
            throw new \Exception('Ungültiger Score!');
        }

        $currScore = $this->getScoreboard($playerIndex)['points'];

        // first throw
        if ($currScore === $this->pointMode) {
            $firstMulti = intval($throws[0]->multiplier);

            if ($this->inMode === 'Double' and $firstMulti !== 2) {
                $realScore = 0; // No score because of double-in rule
            } else if ($this->inMode === 'Triple' and $firstMulti !== 3) {
                $realScore = 0; // No score because of triple-in rule
            } else if ($this->inMode === 'Master' and !($firstMulti === 2 or $firstMulti === 3)) {
                $realScore = 0; // No score because of master-in rule
            }
        }

        if ($currScore - $score < 0) {
            $realScore = 0; // No score: overthrown
        }

        if ($currScore - $score === 1 and ($this->outMode === 'Double' or $this->outMode === 'Master')) {
            $realScore = 0; // No score: impossible checkout
        }

        if ($currScore - $score === 2 and $this->outMode === 'Triple') {
            $realScore = 0; // No score: impossible checkout
        }

        // last throw
        if ($currScore - $score === 0) {
            $lastMulti = intval($throws[2]->multiplier);

            if ($this->outMode === 'Double' and $lastMulti !== 2) {
                $realScore = 0; // No score because of double-out rule;
            } else if ($this->outMode === 'Triple' and $lastMulti !== 3) {
                $realScore = 0; // No score because of triple-out rule;
            } else if ($this->outMode === 'Master' and !($lastMulti === 2 or $lastMulti === 3)) {
                $realScore = 0; // No score becasue of master-out rule;
            }
        }

        if ($realScore >= 100) {
            $floor = floor($realScore / 20) * 20;
            $this->scoreboards[$playerIndex]['highScorings']["$floor"]++;
        }

        $this->scoreboards[$playerIndex]['points'] = $currScore - $realScore;
        $this->scoreboards[$playerIndex]['lastThrow'] = $throws;
        $this->rounds[$playerIndex]++;
        $this->updateAverage($playerIndex);

        return $this->scoreboards[$playerIndex];
    }

    private function calcScore($throws) {
        $score = 0;
        $invalidScores = array(163, 166, 169, 172, 173, 175, 176, 178, 179);
        
        foreach ($throws as $throw) {
            $multi = intval($throw->multiplier);
            $value = intval($throw->value);

            if ($multi < 1 or $multi > 3) {
                return -1;
            }
            if ($value < 0 or ($value > 20 and $value !== 25 and $value !== 50)) {
                return -1;
            }

            $score += $multi * $value;
        }

        if (in_array($score, $invalidScores)) {
            return -1;
        }

        if ($score > 180) {
            return -1;
        }

        return $score;
    }

    private function updateAverage($playerIndex) {
        $points = $this->scoreboards[$playerIndex]['points'];
        $rounds = $this->rounds[$playerIndex];
        $avg = number_format(round(($this->pointMode - $points) / $rounds, 1), 1);
        $this->scoreboards[$playerIndex]['average'] = $avg;
    }
}

?>
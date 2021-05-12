<?php

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
        
        $this->rounds = array(0, 0);
        $this->pointMode = $points;
        $this->outMode = $settings['outMode'];
        $this->inMode = $settings['inMode'];
    }

    public function getScoreboards() {
        return $this->scoreboards;
    }

    public function getScoreboard($index) {
        return $this->scoreboards[$index];
    }

    public function newScore($playerIndex, $throws) {
        if ($playerIndex !== 0 && $playerIndex !== 1) {
            throw new \Exception('This player does not exist.');
        }

        if ($playerIndex === 0) {
            if ($this->rounds[0] !== $this->rounds[1]) {
                throw new \Exception('Wrong player.');
            }
        } else {
            if ($this->rounds[1] !== $this->rounds[0] - 1) {
                throw new \Exception('Wrong player.');
            }
        }

        $score = $this->calcScore($throws);
        $realScore = $score;

        if ($score === -1) {
            throw new \Exception('Invalid Score!');
        }

        $currScore = $this->getScoreboard($playerIndex)['points'];

        // first throw
        if ($currScore === $this->pointMode) {
            $firstMulti = intval($throws[0]->multiplier);

            if ($this->inMode === 'double' and $firstMulti !== 2) {
                $realScore = 0; // No score because of double-in rule
            } else if ($this->inMode === 'triple' and $firstMulti !== 3) {
                $realScore = 0; // No score because of triple-in rule
            } else if ($this->inMode === 'master' and ($firstMulti !== 2 or $firstMulti !== 3)) {
                $realScore = 0; // No score because of master-in rule
            }
        }

        if ($currScore - $score < 0) {
            $realScore = 0; // No score: overthrown
        }

        if ($currScore - $score === 1 and ($this->outMode === 'double' or $this->outMode === 'master')) {
            $realScore = 0; // No score: impossible checkout
        }

        if ($currScore - $score === 2 and $this->outMode === 'triple') {
            $realScore = 0; // No score: impossible checkout
        }

        // last throw
        if ($currScore - $score === 0) {
            $lastMulti = intval($throws[2]->multiplier);

            if ($this->outMode === 'double' and $lastMulti !== 2) {
                $realScore = 0; // No score because of double-out rule;
            } else if ($this->outMode === 'triple' and $lastMulti !== 3) {
                $realScore = 0; // No score because of triple-out rule;
            } else if ($this->outMode === 'master' and ($lastMulti !== 2 or $lastMulti !== 3)) {
                $realScore = 0; // No score becasue of master-out rule;
            }
        }

        $this->scoreboards[$playerIndex]['points'] = $currScore - $realScore;
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

    private function updateAverage($playerIndex) {
        $points = $this->scoreboards[$playerIndex]['points'];
        $rounds = $this->rounds[$playerIndex];
        $avg = round($points / $rounds, 1);
        $this->scoreboards[$playerIndex]['average'] = $avg;
    }
}

?>
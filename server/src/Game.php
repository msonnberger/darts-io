<?php

namespace RemoteDarts;

class Game {
    private $scores;
    private $rounds;
    private $pointMode;
    private $outMode;
    private $inMode;

    public function __construct($settings) {
        $points = $settings['pointMode'];
        $this->scores = array($points, $points);
        $this->rounds = array(0, 0);
        $this->pointMode = $points;
        $this->outMode = $settings['outMode'];
        $this->inMode = $settings['inMode'];
    }

    public function getScores() {
        return $this->scores;
    }

    public function getScore($index) {
        return $this->scores[$index];
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
        if ($score === -1) {
            throw new \Exception('Invalid Score!');
        }

        $currScore = $this->getScore($playerIndex);

        // first throw
        if ($currScore === $this->pointMode) {
            $firstMulti = intval($throws[0]->multiplier);

            if ($this->inMode === 'double' and $firstMulti !== 2) {
                throw new \Exception('Double-In!');
            } else if ($this->inMode === 'triple' and $firstMulti !== 3) {
                throw new \Exception('Triple-In!');
            } else if ($this->inMode === 'master' and ($firstMulti !== 2 or $firstMulti !== 3)) {
                throw new \Exception('Master-In!');
            }
        }

        if ($currScore - $score < 0 or $currScore - $score === 1) {
            var_dump($this->scores);
            echo "$score\n";
            throw new \Exception('No Score!');
        }

        // last throw
        if ($currScore - $score === 0) {
            $lastMulti = intval($throws[2]->multiplier);

            if ($this->outMode === 'double' and $lastMulti !== 2) {
                throw new \Exception('Double-Out!');
            } else if ($this->outMode === 'triple' and $lastMulti !== 3) {
                throw new \Exception('Triple-Out!');
            } else if ($this->outMode === 'master' and ($lastMulti !== 2 or $lastMulti !== 3)) {
                throw new \Exception('Master-Out!');
            }
        }

        $this->scores[$playerIndex] = $currScore - $score;
        $this->rounds[$playerIndex]++;
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
}

?>
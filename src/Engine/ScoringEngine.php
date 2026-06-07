<?php
namespace VG\GravitySpamShield\Engine;

use VG\GravitySpamShield\Support\Submission;

class ScoringEngine {
    /**
     * @param array<int,Signal> $signals
     */
    public function __construct(
        private array $signals,
        private int $threshold
    ) {
    }

    public function evaluate(Submission $submission): Verdict {
        $score = 0;
        $reasons = [];
        $blocked = false;

        foreach ($this->signals as $signal) {
            $result = $signal->score($submission);
            if (!$result->matched()) {
                continue;
            }
            $score += $result->points;
            $reasons[] = $result->reason;
            if ($result->block) {
                $blocked = true;
            }
        }

        $isSpam = $blocked || $score >= $this->threshold;

        return new Verdict($isSpam, $score, $reasons);
    }
}

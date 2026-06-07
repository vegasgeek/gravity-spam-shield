<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\Support\Submission;

class GibberishSignal implements Signal {
    private const MIN_LENGTH = 6;
    private const MAX_CONSONANT_RUN = 4;
    private const MIN_VOWEL_RATIO = 0.18;

    public function __construct(private int $weight) {
    }

    public function key(): string {
        return 'gibberish';
    }

    public function score(Submission $submission): SignalResult {
        $tokens = [];
        if ($submission->name !== '') {
            $tokens = preg_split('/\s+/', $submission->name) ?: [];
        }
        foreach ($tokens as $token) {
            if ($this->looksGibberish($token)) {
                return new SignalResult($this->weight, 'Gibberish name: "' . $token . '"');
            }
        }
        return SignalResult::none();
    }

    private function looksGibberish(string $token): bool {
        $token = strtolower(preg_replace('/[^a-z]/i', '', $token) ?? '');
        $len = strlen($token);
        if ($len < self::MIN_LENGTH) {
            return false;
        }

        $vowels = preg_match_all('/[aeiou]/', $token);
        if ($vowels / $len < self::MIN_VOWEL_RATIO) {
            return true;
        }

        $longestRun = 0;
        $run = 0;
        foreach (str_split($token) as $ch) {
            if (strpos('aeiou', $ch) === false) {
                $run++;
                $longestRun = max($longestRun, $run);
            } else {
                $run = 0;
            }
        }
        return $longestRun > self::MAX_CONSONANT_RUN;
    }
}

<?php
namespace VG\GravitySpamShield\Engine;

class Verdict {
    /**
     * @param array<int,string> $reasons Human-readable reason strings
     */
    public function __construct(
        public readonly bool $isSpam,
        public readonly int $score,
        public readonly array $reasons
    ) {
    }
}

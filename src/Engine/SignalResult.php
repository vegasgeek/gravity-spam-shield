<?php
namespace VG\GravitySpamShield\Engine;

class SignalResult {
    public function __construct(
        public readonly int $points,
        public readonly string $reason,
        public readonly bool $block = false
    ) {
    }

    public static function none(): self {
        return new self(0, '', false);
    }

    public function matched(): bool {
        return $this->points > 0 || $this->block;
    }
}

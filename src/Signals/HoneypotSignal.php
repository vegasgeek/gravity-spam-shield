<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\Support\Submission;

class HoneypotSignal implements Signal {
    public function __construct(private int $weight) {
    }

    public function key(): string {
        return 'honeypot';
    }

    public function score(Submission $submission): SignalResult {
        if ($submission->honeypotFilled) {
            return new SignalResult($this->weight, 'Honeypot field was filled');
        }
        return SignalResult::none();
    }
}

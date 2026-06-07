<?php
namespace VG\GravitySpamShield\Engine;

use VG\GravitySpamShield\Support\Submission;

interface Signal {
    /** Stable machine key, e.g. "gibberish", used in logs and reasons. */
    public function key(): string;

    public function score(Submission $submission): SignalResult;
}

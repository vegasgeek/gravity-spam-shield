<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\History\SubmissionHistory;
use VG\GravitySpamShield\Support\Submission;

class TimingSignal implements Signal {
    public function __construct(
        private SubmissionHistory $history,
        private int $weight,
        private int $minSeconds,
        private int $velocityMax,
        private int $velocityWindow
    ) {
    }

    public function key(): string {
        return 'timing';
    }

    public function score(Submission $submission): SignalResult {
        if ($submission->elapsedSeconds !== null && $submission->elapsedSeconds < $this->minSeconds) {
            return new SignalResult(
                $this->weight,
                'Submitted too fast (' . $submission->elapsedSeconds . 's)'
            );
        }

        if ($submission->ip !== '') {
            $count = $this->history->countRecentByIp($submission->ip, $this->velocityWindow);
            if ($count > $this->velocityMax) {
                return new SignalResult(
                    $this->weight,
                    'Burst from IP (' . $count . ' recent submissions)'
                );
            }
        }

        return SignalResult::none();
    }
}

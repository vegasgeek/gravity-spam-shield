<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\History\SubmissionHistory;
use VG\GravitySpamShield\Support\Submission;

class DuplicateSignal implements Signal {
    public function __construct(
        private SubmissionHistory $history,
        private int $weight,
        private int $maxRepeats,
        private int $windowSeconds
    ) {
    }

    public function key(): string {
        return 'duplicate';
    }

    public function score(Submission $submission): SignalResult {
        $candidates = [
            'email' => $submission->email,
            'name'  => $submission->name,
            'phone' => $submission->phone,
        ];
        foreach ($candidates as $field => $value) {
            if (trim($value) === '') {
                continue;
            }
            $count = $this->history->countRecentByField($field, $value, $this->windowSeconds);
            if ($count > $this->maxRepeats) {
                return new SignalResult(
                    $this->weight,
                    'Repeated ' . $field . ' (' . $count . ' recent submissions)'
                );
            }
        }
        return SignalResult::none();
    }
}

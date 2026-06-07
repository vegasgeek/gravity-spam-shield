<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\Support\Submission;

class KeywordBlocklistSignal implements Signal {
    /** @var array<int,string> */
    private array $keywords;

    /**
     * @param array<int,string> $keywords
     */
    public function __construct(array $keywords) {
        $this->keywords = array_values(array_filter(array_map(
            static fn ($k) => strtolower(trim((string) $k)),
            $keywords
        )));
    }

    public function key(): string {
        return 'keyword_blocklist';
    }

    public function score(Submission $submission): SignalResult {
        $text = $submission->allText();
        foreach ($this->keywords as $word) {
            if ($word === '') {
                continue;
            }
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $text) === 1) {
                return new SignalResult(0, 'Blocked keyword: "' . $word . '"', true);
            }
        }
        return SignalResult::none();
    }
}

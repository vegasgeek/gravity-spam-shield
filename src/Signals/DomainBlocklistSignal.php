<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\Support\Submission;

class DomainBlocklistSignal implements Signal {
    /** @var array<int,string> */
    private array $domains;

    /**
     * @param array<int,string> $domains
     */
    public function __construct(array $domains) {
        $this->domains = array_values(array_filter(array_map(
            static fn ($d) => strtolower(trim((string) $d)),
            $domains
        )));
    }

    public function key(): string {
        return 'domain_blocklist';
    }

    public function score(Submission $submission): SignalResult {
        $domain = $submission->emailDomain();
        if ($domain === '') {
            return SignalResult::none();
        }
        foreach ($this->domains as $pattern) {
            if ($pattern === '') {
                continue;
            }
            if (str_starts_with($pattern, '*.')) {
                $suffix = substr($pattern, 1); // ".ru"
                if (str_ends_with($domain, $suffix)) {
                    return new SignalResult(0, 'Blocked email domain: ' . $domain, true);
                }
            } elseif ($domain === $pattern) {
                return new SignalResult(0, 'Blocked email domain: ' . $domain, true);
            }
        }
        return SignalResult::none();
    }
}

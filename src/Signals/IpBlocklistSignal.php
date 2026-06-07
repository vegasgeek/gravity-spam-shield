<?php
namespace VG\GravitySpamShield\Signals;

use VG\GravitySpamShield\Engine\Signal;
use VG\GravitySpamShield\Engine\SignalResult;
use VG\GravitySpamShield\Support\Submission;

class IpBlocklistSignal implements Signal {
    /** @var array<int,string> */
    private array $entries;

    /**
     * @param array<int,string> $entries IPs or CIDR ranges
     */
    public function __construct(array $entries) {
        $this->entries = array_values(array_filter(array_map(
            static fn ($e) => trim((string) $e),
            $entries
        )));
    }

    public function key(): string {
        return 'ip_blocklist';
    }

    public function score(Submission $submission): SignalResult {
        $ip = $submission->ip;
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return SignalResult::none();
        }
        foreach ($this->entries as $entry) {
            if (str_contains($entry, '/')) {
                if ($this->inCidr($ip, $entry)) {
                    return new SignalResult(0, 'Blocked IP: ' . $ip, true);
                }
            } elseif ($entry === $ip) {
                return new SignalResult(0, 'Blocked IP: ' . $ip, true);
            }
        }
        return SignalResult::none();
    }

    private function inCidr(string $ip, string $cidr): bool {
        [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, '');
        $bits = (int) $bits;
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        // IPv4-only CIDR support; IPv6 ranges fall through to exact match elsewhere.
        if ($ipLong === false || $subnetLong === false || $bits < 0 || $bits > 32) {
            return false;
        }
        if ($bits === 0) {
            return true;
        }
        $mask = (-1 << (32 - $bits)) & 0xFFFFFFFF;
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}

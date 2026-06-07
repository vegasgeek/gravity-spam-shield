<?php
namespace VG\GravitySpamShield\Settings;

class Settings {
    public const DEFAULT_THRESHOLD = 5;

    /** Signal keys that are on by default. */
    private const DEFAULT_SIGNALS = ['gibberish', 'honeypot', 'timing', 'duplicate'];

    /**
     * @param array<int,string> $recipients
     * @param array<int,string> $ipBlocklist
     * @param array<int,string> $domainBlocklist
     * @param array<int,string> $keywordBlocklist
     * @param array<int,int> $excludedForms
     * @param array<string,bool> $signalToggles
     * @param array<string,int> $weights
     */
    public function __construct(
        public readonly bool $detectionEnabled,
        public readonly bool $digestEnabled,
        public readonly int $threshold,
        public readonly array $recipients,
        public readonly array $ipBlocklist,
        public readonly array $domainBlocklist,
        public readonly array $keywordBlocklist,
        public readonly array $excludedForms,
        public readonly array $signalToggles,
        public readonly array $weights
    ) {
    }

    /**
     * @param array<string,mixed> $raw
     */
    public static function fromArray(array $raw): self {
        return new self(
            detectionEnabled: self::boolOr($raw, 'detection_enabled', true),
            digestEnabled: self::boolOr($raw, 'digest_enabled', true),
            threshold: max(1, (int) ($raw['threshold'] ?? self::DEFAULT_THRESHOLD)),
            recipients: self::emails($raw['recipients'] ?? ''),
            ipBlocklist: self::lines($raw['ip_blocklist'] ?? ''),
            domainBlocklist: self::lines($raw['domain_blocklist'] ?? ''),
            keywordBlocklist: self::lines($raw['keyword_blocklist'] ?? ''),
            excludedForms: self::ints($raw['excluded_forms'] ?? ''),
            signalToggles: is_array($raw['signals'] ?? null) ? self::bools($raw['signals']) : [],
            weights: is_array($raw['weights'] ?? null) ? self::intMap($raw['weights']) : []
        );
    }

    public function signalEnabled(string $key): bool {
        if (array_key_exists($key, $this->signalToggles)) {
            return $this->signalToggles[$key];
        }
        return in_array($key, self::DEFAULT_SIGNALS, true);
    }

    public function signalWeight(string $key, int $default): int {
        return isset($this->weights[$key]) ? max(0, $this->weights[$key]) : $default;
    }

    public function appliesToForm(int $formId): bool {
        return !in_array($formId, $this->excludedForms, true);
    }

    /** @param array<string,mixed> $raw */
    private static function boolOr(array $raw, string $key, bool $default): bool {
        if (!array_key_exists($key, $raw)) {
            return $default;
        }
        return (bool) $raw[$key];
    }

    /** @return array<int,string> */
    private static function emails(mixed $value): array {
        $parts = preg_split('/[,\n]/', (string) $value) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $email = trim($part);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[] = $email;
            }
        }
        return array_values($out);
    }

    /** @return array<int,string> */
    private static function lines(mixed $value): array {
        $parts = preg_split('/[\r\n,]+/', (string) $value) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $line = trim($part);
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return array_values($out);
    }

    /** @return array<int,int> */
    private static function ints(mixed $value): array {
        $out = [];
        foreach (preg_split('/[,\s]+/', (string) $value) ?: [] as $part) {
            $part = trim($part);
            if ($part !== '' && ctype_digit($part)) {
                $out[] = (int) $part;
            }
        }
        return array_values($out);
    }

    /**
     * @param array<string,mixed> $value
     * @return array<string,bool>
     */
    private static function bools(array $value): array {
        $out = [];
        foreach ($value as $k => $v) {
            $out[(string) $k] = (bool) $v;
        }
        return $out;
    }

    /**
     * @param array<string,mixed> $value
     * @return array<string,int>
     */
    private static function intMap(array $value): array {
        $out = [];
        foreach ($value as $k => $v) {
            $out[(string) $k] = (int) $v;
        }
        return $out;
    }
}

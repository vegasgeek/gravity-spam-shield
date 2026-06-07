<?php
namespace VG\GravitySpamShield\Log;

class LogRecord {
    /**
     * @param array<int,string> $reasons
     */
    public function __construct(
        public readonly int $formId,
        public readonly int $entryId,
        public readonly int $score,
        public readonly array $reasons,
        public readonly string $ip,
        public readonly string $email,
        public readonly string $name,
        public readonly string $phone,
        public readonly ?int $id = null,
        public readonly string $createdAt = ''
    ) {
    }
}

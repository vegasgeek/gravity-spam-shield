<?php
namespace VG\GravitySpamShield\Support;

class Submission {
    /**
     * @param array<string,string> $fields Field label => string value
     */
    public function __construct(
        public readonly int $formId,
        public readonly array $fields,
        public readonly string $email,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $ip,
        public readonly ?int $elapsedSeconds,
        public readonly bool $honeypotFilled
    ) {
    }

    public function allText(): string {
        return strtolower(trim(implode(' ', array_values($this->fields))));
    }

    public function emailDomain(): string {
        $at = strrpos($this->email, '@');
        if ($at === false) {
            return '';
        }
        return strtolower(substr($this->email, $at + 1));
    }
}

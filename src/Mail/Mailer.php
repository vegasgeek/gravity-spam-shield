<?php
namespace VG\GravitySpamShield\Mail;

interface Mailer {
    /**
     * @param array<int,string> $to
     */
    public function send(array $to, string $subject, string $htmlBody): bool;
}

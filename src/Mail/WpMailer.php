<?php
namespace VG\GravitySpamShield\Mail;

class WpMailer implements Mailer {
    public function send(array $to, string $subject, string $htmlBody): bool {
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Send from the site admin address so we don't rely on WordPress's
        // default "wordpress@<host>" From, which PHPMailer rejects when the host
        // has no TLD (e.g. "wordpress@localhost" on local dev), causing wp_mail
        // to fail before sending. A real, valid From is also better for delivery.
        $from = get_option('admin_email');
        if (is_string($from) && is_email($from)) {
            $name = (string) get_bloginfo('name');
            $headers[] = sprintf('From: %s <%s>', $name !== '' ? $name : 'WordPress', $from);
        }

        return (bool) wp_mail($to, $subject, $htmlBody, $headers);
    }
}

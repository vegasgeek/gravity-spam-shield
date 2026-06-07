<?php
namespace VG\GravitySpamShield\Digest;

use VG\GravitySpamShield\Log\LogRecord;

class DigestBuilder {
    private string $siteName;
    private string $adminUrl;

    public function __construct(string $siteName, string $adminUrl) {
        $this->siteName = $siteName;
        $this->adminUrl = rtrim($adminUrl, '/') . '/';
    }

    /**
     * @param array<int,LogRecord> $records
     */
    public function subject(array $records): string {
        return sprintf('[%s] Spam Shield blocked %d submission(s)', $this->siteName, count($records));
    }

    /**
     * @param array<int,LogRecord> $records
     */
    public function body(array $records): string {
        $esc = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $rows = '';
        foreach ($records as $r) {
            $rows .= '<tr>'
                . '<td>' . $esc($r->name) . '</td>'
                . '<td>' . $esc($r->email) . '</td>'
                . '<td>' . $esc($r->phone) . '</td>'
                . '<td style="text-align:center">' . (int) $r->score . '</td>'
                . '<td>' . $esc(implode('; ', $r->reasons)) . '</td>'
                . '</tr>';
        }

        return '<p>Spam Shield quarantined <strong>' . count($records) . '</strong> submission(s) in the last 24 hours. '
            . 'They were marked as spam and did <em>not</em> trigger admin notification emails.</p>'
            . '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse">'
            . '<thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Score</th><th>Reasons</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table>'
            . '<p>Review or recover any false positives in the Gravity Forms spam folder: '
            . '<a href="' . $esc($this->adminUrl) . 'admin.php?page=gf_entries">Form Entries &rarr; Spam</a>.</p>';
    }
}

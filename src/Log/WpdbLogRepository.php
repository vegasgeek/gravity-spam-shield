<?php
namespace VG\GravitySpamShield\Log;

class WpdbLogRepository implements LogRepository {
    private string $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'vggss_log';
    }

    public function insert(LogRecord $record): void {
        global $wpdb;
        $wpdb->insert(
            $this->table,
            [
                'created_at' => current_time('mysql', true),
                'form_id'    => $record->formId,
                'entry_id'   => $record->entryId,
                'score'      => $record->score,
                'reasons'    => (string) wp_json_encode($record->reasons),
                'ip'         => $record->ip,
                'email'      => $record->email,
                'name'       => $record->name,
                'phone'      => $record->phone,
                'digested'   => 0,
            ],
            ['%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d']
        );
    }

    public function undigested(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT * FROM {$this->table} WHERE digested = 0 ORDER BY created_at DESC",
            ARRAY_A
        );
        return $this->mapRows(is_array($rows) ? $rows : []);
    }

    public function recent(int $limit): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d", $limit),
            ARRAY_A
        );
        return $this->mapRows(is_array($rows) ? $rows : []);
    }

    public function markDigested(array $ids): void {
        global $wpdb;
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $wpdb->query(
            $wpdb->prepare("UPDATE {$this->table} SET digested = 1 WHERE id IN ($placeholders)", $ids)
        );
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array<int,LogRecord>
     */
    private function mapRows(array $rows): array {
        $out = [];
        foreach ($rows as $row) {
            $reasons = json_decode((string) ($row['reasons'] ?? '[]'), true);
            $out[] = new LogRecord(
                formId: (int) ($row['form_id'] ?? 0),
                entryId: (int) ($row['entry_id'] ?? 0),
                score: (int) ($row['score'] ?? 0),
                reasons: is_array($reasons) ? array_map('strval', $reasons) : [],
                ip: (string) ($row['ip'] ?? ''),
                email: (string) ($row['email'] ?? ''),
                name: (string) ($row['name'] ?? ''),
                phone: (string) ($row['phone'] ?? ''),
                id: isset($row['id']) ? (int) $row['id'] : null,
                createdAt: (string) ($row['created_at'] ?? '')
            );
        }
        return $out;
    }
}

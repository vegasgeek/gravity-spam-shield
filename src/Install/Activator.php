<?php
namespace VG\GravitySpamShield\Install;

class Activator {
    public const CRON_HOOK = 'vggss_daily_digest';

    public function activate(): void {
        $this->createTable();
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK);
        }
    }

    private function createTable(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'vggss_log';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            form_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            entry_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            score INT NOT NULL DEFAULT 0,
            reasons LONGTEXT NULL,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            name VARCHAR(255) NOT NULL DEFAULT '',
            phone VARCHAR(64) NOT NULL DEFAULT '',
            digested TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY digested (digested),
            KEY ip (ip)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

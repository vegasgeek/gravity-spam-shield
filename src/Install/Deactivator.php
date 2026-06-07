<?php
namespace VG\GravitySpamShield\Install;

class Deactivator {
    public function deactivate(): void {
        $timestamp = wp_next_scheduled(Activator::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, Activator::CRON_HOOK);
        }
    }
}

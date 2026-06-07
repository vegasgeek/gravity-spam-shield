<?php
namespace VG\GravitySpamShield\Admin;

use VG\GravitySpamShield\Log\LogRepository;

class LogViewer {
    public const SLUG = 'vggss-log';

    public function __construct(private LogRepository $repository) {
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $records = $this->repository->recent(100);
        require VGGSS_PLUGIN_DIR . 'src/Views/log-viewer.php';
    }
}

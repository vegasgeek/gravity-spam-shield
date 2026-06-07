<?php
namespace VG\GravitySpamShield\Admin;

use VG\GravitySpamShield\Settings\SettingsStore;

class SettingsPage {
    public const SLUG = 'vggss-settings';

    public function __construct(private SettingsStore $store) {
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $this->maybeSave();
        $settings = $this->store->load();
        $raw = get_option(SettingsStore::OPTION, []);
        $raw = is_array($raw) ? $raw : [];
        require VGGSS_PLUGIN_DIR . 'src/Views/settings.php';
    }

    private function maybeSave(): void {
        if (($_POST['vggss_action'] ?? '') !== 'save') {
            return;
        }
        check_admin_referer('vggss_save_settings');

        $clean = [
            'detection_enabled' => isset($_POST['detection_enabled']) ? 1 : 0,
            'digest_enabled'    => isset($_POST['digest_enabled']) ? 1 : 0,
            'threshold'         => (int) ($_POST['threshold'] ?? 5),
            'recipients'        => sanitize_textarea_field(wp_unslash($_POST['recipients'] ?? '')),
            'ip_blocklist'      => sanitize_textarea_field(wp_unslash($_POST['ip_blocklist'] ?? '')),
            'domain_blocklist'  => sanitize_textarea_field(wp_unslash($_POST['domain_blocklist'] ?? '')),
            'keyword_blocklist' => sanitize_textarea_field(wp_unslash($_POST['keyword_blocklist'] ?? '')),
            'excluded_forms'    => sanitize_text_field(wp_unslash($_POST['excluded_forms'] ?? '')),
            'signals'           => $this->cleanSignals(),
            'weights'           => $this->cleanWeights(),
        ];
        $this->store->save($clean);
        add_settings_error('vggss', 'saved', __('Settings saved.', 'vg-gravity-spam-shield'), 'updated');
    }

    /** @return array<string,bool> */
    private function cleanSignals(): array {
        $keys = ['gibberish', 'honeypot', 'timing', 'duplicate'];
        $posted = is_array($_POST['signals'] ?? null) ? $_POST['signals'] : [];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = isset($posted[$key]);
        }
        return $out;
    }

    /** @return array<string,int> */
    private function cleanWeights(): array {
        $keys = ['gibberish', 'honeypot', 'timing', 'duplicate'];
        $posted = is_array($_POST['weights'] ?? null) ? $_POST['weights'] : [];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = max(0, (int) ($posted[$key] ?? 0));
        }
        return $out;
    }
}

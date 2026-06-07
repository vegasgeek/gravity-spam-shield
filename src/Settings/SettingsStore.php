<?php
namespace VG\GravitySpamShield\Settings;

class SettingsStore {
    public const OPTION = 'vggss_settings';

    public function load(): Settings {
        $raw = get_option(self::OPTION, []);
        return Settings::fromArray(is_array($raw) ? $raw : []);
    }

    /**
     * @param array<string,mixed> $raw Already-sanitized raw settings array.
     */
    public function save(array $raw): void {
        update_option(self::OPTION, $raw, true);
    }
}

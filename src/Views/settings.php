<?php
/** @var \VG\GravitySpamShield\Settings\Settings $settings */
/** @var array<string,mixed> $raw */
if (!defined('ABSPATH')) { exit; }
$rawGet = static fn (string $k): string => esc_textarea((string) ($raw[$k] ?? ''));
$weights = is_array($raw['weights'] ?? null) ? $raw['weights'] : [];
$labels = [
    'gibberish' => 'Gibberish text',
    'honeypot'  => 'Honeypot field',
    'timing'    => 'Submit timing & velocity',
    'duplicate' => 'Duplicate / repeated values',
];
?>
<div class="wrap">
    <h1><?php esc_html_e('Gravity Spam Shield', 'vg-gravity-spam-shield'); ?></h1>
    <?php settings_errors('vggss'); ?>
    <form method="post">
        <?php wp_nonce_field('vggss_save_settings'); ?>
        <input type="hidden" name="vggss_action" value="save" />

        <h2><?php esc_html_e('Notifications', 'vg-gravity-spam-shield'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Daily digest', 'vg-gravity-spam-shield'); ?></th>
                <td><label><input type="checkbox" name="digest_enabled" value="1" <?php checked($settings->digestEnabled); ?> /> <?php esc_html_e('Send a daily summary of blocked spam', 'vg-gravity-spam-shield'); ?></label></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Recipients', 'vg-gravity-spam-shield'); ?></th>
                <td><textarea name="recipients" rows="2" cols="50" placeholder="admin@example.com, owner@example.com"><?php echo $rawGet('recipients'); ?></textarea>
                <p class="description"><?php esc_html_e('Comma or newline separated. Leave empty to disable sending.', 'vg-gravity-spam-shield'); ?></p></td>
            </tr>
        </table>

        <h2><?php esc_html_e('Detection', 'vg-gravity-spam-shield'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Enable detection', 'vg-gravity-spam-shield'); ?></th>
                <td><label><input type="checkbox" name="detection_enabled" value="1" <?php checked($settings->detectionEnabled); ?> /> <?php esc_html_e('Score and quarantine spam submissions', 'vg-gravity-spam-shield'); ?></label></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Score threshold', 'vg-gravity-spam-shield'); ?></th>
                <td><input type="number" min="1" name="threshold" value="<?php echo esc_attr((string) $settings->threshold); ?>" /></td>
            </tr>
            <?php foreach ($labels as $key => $label) : ?>
            <tr>
                <th><?php echo esc_html($label); ?></th>
                <td>
                    <label><input type="checkbox" name="signals[<?php echo esc_attr($key); ?>]" value="1" <?php checked($settings->signalEnabled($key)); ?> /> <?php esc_html_e('Enabled', 'vg-gravity-spam-shield'); ?></label>
                    &nbsp; <?php esc_html_e('Weight', 'vg-gravity-spam-shield'); ?>
                    <input type="number" min="0" style="width:5em" name="weights[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) ($weights[$key] ?? '')); ?>" placeholder="3" />
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <th><?php esc_html_e('Exclude form IDs', 'vg-gravity-spam-shield'); ?></th>
                <td><input type="text" name="excluded_forms" value="<?php echo esc_attr((string) ($raw['excluded_forms'] ?? '')); ?>" placeholder="2, 5" /></td>
            </tr>
        </table>

        <h2><?php esc_html_e('Blocklists', 'vg-gravity-spam-shield'); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e('IP / CIDR', 'vg-gravity-spam-shield'); ?></th><td><textarea name="ip_blocklist" rows="4" cols="40" placeholder="203.0.113.5&#10;10.0.0.0/8"><?php echo $rawGet('ip_blocklist'); ?></textarea></td></tr>
            <tr><th><?php esc_html_e('Email domains', 'vg-gravity-spam-shield'); ?></th><td><textarea name="domain_blocklist" rows="4" cols="40" placeholder="xyz.com&#10;*.ru"><?php echo $rawGet('domain_blocklist'); ?></textarea></td></tr>
            <tr><th><?php esc_html_e('Keywords', 'vg-gravity-spam-shield'); ?></th><td><textarea name="keyword_blocklist" rows="4" cols="40" placeholder="viagra&#10;casino"><?php echo $rawGet('keyword_blocklist'); ?></textarea></td></tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>

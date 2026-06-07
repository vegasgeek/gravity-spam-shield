<?php
/**
 * Plugin Name: Gravity Forms Spam Shield
 * Plugin URI: https://vegasgeek.com
 * Description: Scores Gravity Forms submissions, quarantines spam as native GF spam (no per-submission admin email), logs it, and sends one daily digest.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: VegasGeek
 * Author URI: https://vegasgeek.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vg-gravity-spam-shield
 *
 * @package vg-gravity-spam-shield
 */

if (!defined('ABSPATH')) {
    exit;
}

define('VGGSS_VERSION', '1.0.0');
define('VGGSS_PLUGIN_FILE', __FILE__);
define('VGGSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VGGSS_PLUGIN_URL', plugin_dir_url(__FILE__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'VG\\GravitySpamShield\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = VGGSS_PLUGIN_DIR . 'src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($path)) {
        require_once $path;
    }
});

register_activation_hook(__FILE__, static function (): void {
    (new VG\GravitySpamShield\Install\Activator())->activate();
});

register_deactivation_hook(__FILE__, static function (): void {
    (new VG\GravitySpamShield\Install\Deactivator())->deactivate();
});

add_action('plugins_loaded', static function (): void {
    (new VG\GravitySpamShield\Plugin())->boot();
});

// GitHub-based plugin updates (Plugin Update Checker, bundled in vendor/).
require __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';

$vggssUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/vegasgeek/gravity-spam-shield/',
    __FILE__,
    'vg-gravity-spam-shield'
);

// Pull updates from GitHub Releases and install the attached zip asset (our
// clean dist build) instead of the raw repo source. No setBranch().
$vggssUpdateChecker->getVcsApi()->enableReleaseAssets();

// Private repo only: define VGGSS_GITHUB_TOKEN in each site's wp-config.php.
// NEVER hardcode a token here — this file ships to every site.
if (defined('VGGSS_GITHUB_TOKEN') && VGGSS_GITHUB_TOKEN) {
    $vggssUpdateChecker->setAuthentication(VGGSS_GITHUB_TOKEN);
}

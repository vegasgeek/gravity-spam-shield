<?php
// Removes plugin data on uninstall.

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('vggss_settings');

global $wpdb;
$table = $wpdb->prefix . 'vggss_log';
$wpdb->query("DROP TABLE IF EXISTS {$table}");

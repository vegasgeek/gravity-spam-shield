<?php
namespace VG\GravitySpamShield\Admin;

class Menu {
    public function __construct(
        private SettingsPage $settingsPage,
        private LogViewer $logViewer
    ) {
    }

    public function register(): void {
        // Priority 99 so Gravity Forms (which registers its top-level "Forms"
        // menu on admin_menu at the default priority 10) has already created the
        // gf_edit_forms parent. Registering earlier computes our submenu's
        // page-hook against the wrong parent and produces a broken menu URL
        // (/wp-admin/<slug> instead of /wp-admin/admin.php?page=<slug>).
        add_action('admin_menu', [$this, 'addMenus'], 99);
    }

    public function addMenus(): void {
        add_submenu_page(
            'gf_edit_forms',
            __('Spam Shield', 'vg-gravity-spam-shield'),
            __('Spam Shield', 'vg-gravity-spam-shield'),
            'manage_options',
            SettingsPage::SLUG,
            [$this->settingsPage, 'render']
        );
        add_submenu_page(
            'gf_edit_forms',
            __('Spam Shield Log', 'vg-gravity-spam-shield'),
            __('Spam Shield Log', 'vg-gravity-spam-shield'),
            'manage_options',
            LogViewer::SLUG,
            [$this->logViewer, 'render']
        );
    }
}

<?php
/** @var array<int,\VG\GravitySpamShield\Log\LogRecord> $records */
if (!defined('ABSPATH')) { exit; }
?>
<div class="wrap">
    <h1><?php esc_html_e('Spam Shield Log', 'vg-gravity-spam-shield'); ?></h1>
    <table class="widefat striped">
        <thead><tr>
            <th><?php esc_html_e('When', 'vg-gravity-spam-shield'); ?></th>
            <th><?php esc_html_e('Name', 'vg-gravity-spam-shield'); ?></th>
            <th><?php esc_html_e('Email', 'vg-gravity-spam-shield'); ?></th>
            <th><?php esc_html_e('Score', 'vg-gravity-spam-shield'); ?></th>
            <th><?php esc_html_e('Reasons', 'vg-gravity-spam-shield'); ?></th>
        </tr></thead>
        <tbody>
        <?php if ($records === []) : ?>
            <tr><td colspan="5"><?php esc_html_e('No spam logged yet.', 'vg-gravity-spam-shield'); ?></td></tr>
        <?php else : foreach ($records as $r) : ?>
            <tr>
                <td><?php echo esc_html($r->createdAt); ?></td>
                <td><?php echo esc_html($r->name); ?></td>
                <td><?php echo esc_html($r->email); ?></td>
                <td><?php echo (int) $r->score; ?></td>
                <td><?php echo esc_html(implode('; ', $r->reasons)); ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

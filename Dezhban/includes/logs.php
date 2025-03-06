<?php
if (!defined('ABSPATH')) {
    exit;
}

// ایجاد جدول لاگ‌ها هنگام فعال‌سازی پلاگین
function dejban_create_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dejban_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        event_type varchar(255) NOT NULL,
        event_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        user_ip varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'dejban_create_logs_table');

// ذخیره لاگ در دیتابیس
function dejban_log_event($event_type) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dejban_logs';
    $user_ip = $_SERVER['REMOTE_ADDR'];

    $wpdb->insert($table_name, array(
        'event_type' => $event_type,
        'user_ip' => $user_ip
    ));
}

// مثال: ثبت ورودهای ناموفق
function dejban_failed_login($username) {
    dejban_log_event('ورود ناموفق: ' . $username);
}
add_action('wp_login_failed', 'dejban_failed_login');

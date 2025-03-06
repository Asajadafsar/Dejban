<?php
if (!defined('ABSPATH')) {
    exit;
}

function dejban_block_rest_users($response, $server, $request) {
    // بررسی آیا تنظیم فعال است یا نه
    $rest_protection = get_option('dejban_rest_protection', 'enabled');

    if ($rest_protection === 'enabled' && strpos($request->get_route(), '/wp/v2/users') !== false) {
        return new WP_Error(
            'rest_forbidden',
            __('دسترسی غیرمجاز!', 'dejban-security'),
            array('status' => 404)
        );
    }
    return $response;
}
add_filter('rest_pre_dispatch', 'dejban_block_rest_users', 10, 3);
// اجرای محافظت از بروت فورس در ورود به وردپرس
function dejban_check_bruteforce() {
    if (get_option('dejban_bruteforce_enabled') !== 'enabled') {
        return;
    }

    $max_attempts = get_option('dejban_bruteforce_attempts', 5);
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $failed_attempts = get_transient('dejban_bruteforce_' . $user_ip) ?: 0;

    if ($failed_attempts >= $max_attempts) {
        wp_die('<h1>🚫 دسترسی شما موقتا مسدود شده است!</h1><p>به دلیل تلاش‌های زیاد برای ورود، دسترسی شما برای مدتی محدود شده است.</p>', 'خطای امنیتی');
    }
}
add_action('wp_login_failed', 'dejban_check_bruteforce');

// ثبت تلاش‌های ناموفق در دیتابیس
function dejban_track_failed_login($username) {
    if (get_option('dejban_bruteforce_enabled') !== 'enabled') {
        return;
    }

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $failed_attempts = get_transient('dejban_bruteforce_' . $user_ip) ?: 0;
    $failed_attempts++;
    set_transient('dejban_bruteforce_' . $user_ip, $failed_attempts, 3600);
}
add_action('wp_login_failed', 'dejban_track_failed_login');

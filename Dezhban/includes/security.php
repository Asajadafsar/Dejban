<?php
if (!defined('ABSPATH')) {
    exit;
}

// حذف نمایش نسخه وردپرس از هدر و سورس HTML
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// مسدود کردن دسترسی به REST API برای دریافت اطلاعات کاربران
function dejban_block_rest_users($response, $server, $request) {
    $rest_protection = get_option('dejban_rest_protection', 'enabled');

    if ($rest_protection === 'enabled' && strpos($request->get_route(), '/wp/v2/users') !== false) {
        return new WP_Error(
            'rest_forbidden',
            __('🚫 دسترسی غیرمجاز!', 'dejban-security'),
            array('status' => 403) // Changed status from 404 to 403 for unauthorized access
        );
    }
    return $response;
}
add_filter('rest_pre_dispatch', 'dejban_block_rest_users', 10, 3);

// غیرفعال کردن REST API برای کاربران غیر لاگین شده
function dejban_disable_rest_api($access) {
    if (!is_user_logged_in()) {
        return new WP_Error('rest_disabled', __('REST API فقط برای کاربران احراز هویت‌شده در دسترس است.'), array('status' => 403));
    }
    return $access;
}
add_filter('rest_authentication_errors', 'dejban_disable_rest_api');

// محافظت در برابر حملات Brute Force روی ورود به وردپرس
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

// ثبت تلاش‌های ناموفق برای ورود
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

// حذف لینک‌های REST API از هدر و سورس HTML
remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');

// حذف لینک‌های کوتاه (shortlink) از هدر
remove_action('wp_head', 'wp_shortlink_wp_head', 10);
remove_action('template_redirect', 'wp_shortlink_header', 11);

// حذف لینک‌های RSD (Remote Service Discovery)
remove_action('wp_head', 'rsd_link');

// حذف لینک‌های Windows Live Writer
remove_action('wp_head', 'wlwmanifest_link');

// حذف لینک‌های RSS اضافی
remove_action('wp_head', 'feed_links_extra', 3);

// حذف لینک‌های Emoji از هدر و سورس صفحه
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

// حذف لینک‌های oEmbed از هدر
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

// غیرفعال کردن XML-RPC برای امنیت بیشتر
add_filter('xmlrpc_enabled', '__return_false');

// حذف X-Pingback از هدر HTTP
function remove_x_pingback($headers) {
    unset($headers['X-Pingback']);
    return $headers;
}
add_filter('wp_headers', 'remove_x_pingback');

// جلوگیری از نمایش فهرست فایل‌ها در سرور (Apache/Nginx)
function disable_directory_listing() {
    if (file_exists(ABSPATH . '.htaccess')) {
        $htaccess = ABSPATH . '.htaccess';
        $rules = "\n# Disable directory listing\nOptions -Indexes\n";
        if (strpos(file_get_contents($htaccess), 'Options -Indexes') === false) {
            file_put_contents($htaccess, $rules, FILE_APPEND);
        }
    }
}
add_action('init', 'disable_directory_listing');

// جلوگیری از نمایش سورس کد و ابزارهای توسعه مرورگر
function dejban_disable_view_source() {
    ?>
    <script type="text/javascript">
        document.addEventListener("contextmenu", function(e) {
            e.preventDefault();
            return false;
        });
    </script>
    <?php
}
add_action('wp_footer', 'dejban_disable_view_source');
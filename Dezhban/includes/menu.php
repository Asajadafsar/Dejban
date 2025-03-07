<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add the plugin's main menu
function dejban_add_admin_menu() {
    add_menu_page(
        'دژبان امنیتی', 
        '🛡️ دژبان امنیتی', 
        'manage_options', 
        'dejban_security', 
        'dejban_dashboard_page', 
        'dashicons-shield',
        80
    );
    
    add_submenu_page(
        'dejban_security',
        'داشبورد',
        'داشبورد',
        'manage_options',
        'dejban_security',
        'dejban_dashboard_page'
    );
    
    add_submenu_page(
        'dejban_security',
        'امنیت کاربران',
        'امنیت کاربران',
        'manage_options',
        'dejban_user_security',
        'dejban_user_security_page'
    );

    add_submenu_page(
        'dejban_security',
        'محافظت از بروت فورس',
        'بروت فورس',
        'manage_options',
        'dejban_bruteforce_protection',
        'dejban_bruteforce_protection_page'
    );

    add_submenu_page(
        'dejban_security',
        'غیرفعال کردن نسخه وردپرس',
        'غیرفعال کردن نسخه وردپرس',
        'manage_options',
        'dejban_disable_wp_version',
        'dejban_disable_wp_version_page'
    );
}
add_action('admin_menu', 'dejban_add_admin_menu');

// Dashboard
function dejban_dashboard_page() {
    ?>
    <div class="wrap dejban-dashboard">
        <h1>🛡️ داشبورد دژبان</h1>
        <p>به افزونه امنیتی دژبان خوش آمدید!</p>
    </div>
    <?php
}

// settings
function dejban_user_security_page() {
    $rest_protection = get_option('dejban_rest_protection', 'enabled');
    ?>
    <div class="wrap dejban-settings">
        <h1>🛡️ تنظیمات امنیت کاربران</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('dejban_settings_group');
            do_settings_sections('dejban_settings_group');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">🔒 بستن REST API برای کاربران</th>
                    <td>
                        <input type="checkbox" name="dejban_rest_protection" value="enabled" <?php checked($rest_protection, 'enabled'); ?> />
                        <label>غیرفعال کردن دسترسی به REST API برای لیست کاربران</label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// bruteforce
function dejban_bruteforce_protection_page() {
    $bruteforce_enabled = get_option('dejban_bruteforce_enabled', 'enabled');
    $bruteforce_attempts = get_option('dejban_bruteforce_attempts', 5);
    ?>
    <div class="wrap dejban-settings">
        <h1>🚨 تنظیمات محافظت از بروت فورس</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('dejban_bruteforce_settings_group');
            do_settings_sections('dejban_bruteforce_settings_group');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">🔒 فعال‌سازی محافظت از بروت فورس</th>
                    <td>
                        <input type="checkbox" name="dejban_bruteforce_enabled" value="enabled" <?php checked($bruteforce_enabled, 'enabled'); ?> />
                        <label>فعال کردن محافظت از ورودهای مکرر</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">🚨 حداکثر تعداد تلاش ناموفق</th>
                    <td>
                        <input type="number" name="dejban_bruteforce_attempts" value="<?php echo esc_attr($bruteforce_attempts); ?>" min="1" />
                        <label>تعداد دفعات قبل از مسدود شدن</label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Disable WP version settings
function dejban_disable_wp_version_page() {
    $version_disable = get_option('dejban_disable_wp_version', 'enabled');
    ?>
    <div class="wrap dejban-settings">
        <h1>⚙️ تنظیمات غیرفعال کردن نسخه وردپرس</h1>
        <p>این گزینه به‌طور پیش‌فرض فعال است تا از هکرها و ربات‌ها جلوگیری کند. در اینجا دلایل مختلفی برای فعال بودن این ویژگی آورده شده است:</p>
        <ul>
            <li>🚫 جلوگیری از شناسایی نسخه دقیق وردپرس شما که می‌تواند به هکرها کمک کند تا آسیب‌پذیری‌های خاص نسخه شما را شناسایی کنند.</li>
            <li>⚡️ بهبود امنیت سایت شما با پنهان کردن اطلاعات اضافی که به‌طور بالقوه توسط مهاجمین سوءاستفاده می‌شود.</li>
            <li>🔒 در صورت غیرفعال کردن نمایش نسخه، هکرها نمی‌توانند بدانند که شما از وردپرس استفاده می‌کنید یا نه.</li>
        </ul>
        <label><strong>فعال کردن این گزینه به دلیل دلایل بالا به‌طور پیش‌فرض توصیه می‌شود.</strong></label>
    </div>
    <?php
}

// save to database
function dejban_register_settings() {
    register_setting('dejban_settings_group', 'dejban_rest_protection');
    register_setting('dejban_bruteforce_settings_group', 'dejban_bruteforce_enabled');
    register_setting('dejban_bruteforce_settings_group', 'dejban_bruteforce_attempts');
    register_setting('dejban_version_settings_group', 'dejban_disable_wp_version');
}
add_action('admin_init', 'dejban_register_settings');

// نمایش پیام موفقیت بعد از ذخیره تنظیمات
function dejban_admin_notice() {
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        ?>
        <div class="updated notice is-dismissible">
            <p>✅ تنظیمات ذخیره شد رفیقم! 🎉</p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'dejban_admin_notice');

// Disable WP version from HTTP header when option is enabled
function dejban_check_disable_wp_version() {
    $version_disable = get_option('dejban_disable_wp_version', 'enabled');
    if ($version_disable == 'enabled') {
        remove_action('wp_head', 'wp_generator');
    }
}
add_action('wp', 'dejban_check_disable_wp_version');
?>

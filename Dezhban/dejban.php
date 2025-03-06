<?php
/**
 * Plugin Name: Dejban Security
 * Plugin URI:  https://sajadafsar
 * Description: افزونه امنیتی برای محافظت از وردپرس
 * Version:     1.0
 * Author:      سجاد و معین
 * Author URI:  https://sajadafsar
 * License:     GPL2
 */

if (!defined('ABSPATH')) {
    exit; 
}


define('DEJBAN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEJBAN_PLUGIN_URL', plugin_dir_url(__FILE__));


require_once DEJBAN_PLUGIN_DIR . 'includes/menu.php';


require_once DEJBAN_PLUGIN_DIR . 'includes/security.php';


require_once DEJBAN_PLUGIN_DIR . 'includes/logs.php';

function dejban_admin_styles() {
    wp_enqueue_style('dejban-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'dejban_admin_styles');

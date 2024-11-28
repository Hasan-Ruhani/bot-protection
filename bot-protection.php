<?php
/**
 * Plugin Name: Bot-Protection
 * Description: A plugin to protect forms with robust bot protection and spam email prevention, while dynamically serving the action URL via a REST API for enhanced security.
 * Version: 0.1.3
 * Author: Mirza Ovinoor
 * Author URI: https://codeforsite.com/
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('BOT_PROTECTION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BOT_PROTECTION_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include feature files
require_once BOT_PROTECTION_PLUGIN_DIR . 'includes/api-routes.php';
require_once BOT_PROTECTION_PLUGIN_DIR . 'includes/admin-dashboard.php';

// Enqueue scripts and styles for the admin area
add_action('admin_enqueue_scripts', 'bot_protection_admin_assets');
function bot_protection_admin_assets() {
    wp_enqueue_script('bot-protection-admin', BOT_PROTECTION_PLUGIN_URL . 'assets/admin.js', ['jquery'], '1.2', true);
    wp_enqueue_style('bot-protection-admin', BOT_PROTECTION_PLUGIN_URL . 'assets/admin.css');
}

// Enqueue frontend scripts and styles
add_action('wp_enqueue_scripts', 'bot_protection_enqueue_frontend_script');
function bot_protection_enqueue_frontend_script() {
    // Register and enqueue the bot-protection.js file for the frontend
    wp_enqueue_script(
        'bot-protection-frontend',
        BOT_PROTECTION_PLUGIN_URL . 'assets/bot-protection.js',
        ['jquery'],
        '1.2',
        true
    );
}



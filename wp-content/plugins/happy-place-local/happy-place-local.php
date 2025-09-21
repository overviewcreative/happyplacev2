<?php
/**
 * Plugin Name: Happy Place Local
 * Description: Local places and events for the Happy Place stack. Minimal, ACF-optional.
 * Version: 0.2.1
 * Author: The Parker Group
 * Text Domain: happy-place-local
 */
if (!defined('ABSPATH')) exit;

define('HPL_VERSION', '0.2.1');
define('HPL_PATH', plugin_dir_path(__FILE__));
define('HPL_URL', plugin_dir_url(__FILE__));

// Lightweight PSR-4 style autoloader for HappyPlace\Local\*
spl_autoload_register(function($class){
    $prefix = 'HappyPlace\\Local\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $rel = substr($class, strlen($prefix));
    $file = HPL_PATH . 'includes/' . str_replace('\\','/',$rel) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once HPL_PATH . 'includes/LocalServiceProvider.php';

register_activation_hook(__FILE__, function () {
    // Register CPTs before flush so rules exist
    \HappyPlace\Local\CPT\LocalPlace::register();
    \HappyPlace\Local\CPT\Event::register();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

add_action('plugins_loaded', function () {
    (new \HappyPlace\Local\LocalServiceProvider())->register();
});

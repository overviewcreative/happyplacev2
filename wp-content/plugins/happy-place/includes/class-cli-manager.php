<?php
/**
 * CLI Manager
 * Handles WP-CLI command registration
 *
 * @package HappyPlace
 */

namespace HappyPlace;

if (!defined('ABSPATH')) {
    exit;
}

class CLI_Manager {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('init', [$this, 'register_commands']);
    }

    public function register_commands() {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }

        // Register ACF commands
        if (file_exists(HP_INCLUDES_DIR . 'cli/class-acf-cli.php')) {
            require_once HP_INCLUDES_DIR . 'cli/class-acf-cli.php';
            \WP_CLI::add_command('happy-place acf', 'HappyPlace\\CLI\\ACF_CLI');
        }

        // Register main Happy Place command group
        \WP_CLI::add_command('happy-place', [$this, 'main_command']);
    }

    /**
     * Manage Happy Place plugin
     *
     * ## SUBCOMMANDS
     *
     * * acf - Manage ACF field groups
     *
     * ## EXAMPLES
     *
     *     wp happy-place acf status
     *     wp happy-place acf sync
     *     wp happy-place acf cleanup
     *
     * @when after_wp_load
     */
    public function main_command($args, $assoc_args) {
        \WP_CLI::line('Happy Place Plugin CLI');
        \WP_CLI::line('====================');
        \WP_CLI::line('Available subcommands:');
        \WP_CLI::line('  acf      - Manage ACF field groups');
        \WP_CLI::line('');
        \WP_CLI::line('Use "wp help happy-place <subcommand>" for more information.');
    }
}
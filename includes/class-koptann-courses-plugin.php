<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Koptann_Courses_Plugin {

    private static $instance = null;
    public $version = '1.4.0'; // **MODIFICATION**: Version bump

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->initialize_hooks();
    }

    /**
     * Load all the required dependency files.
     */
    private function load_dependencies() {
        $plugin_path = plugin_dir_path(dirname(__FILE__));
        require_once $plugin_path . 'includes/class-ktc-cpts.php';
        require_once $plugin_path . 'includes/class-ktc-admin.php';
        require_once $plugin_path . 'includes/class-ktc-frontend.php';
        require_once $plugin_path . 'includes/class-ktc-helpers.php';
    }

    /**
     * Initialize all hooks for the plugin.
     */
    private function initialize_hooks() {
        $cpts = new KTC_CPTs();
        $admin = new KTC_Admin();
        $frontend = new KTC_Frontend();

        // CPTs & Taxonomies
        add_action('init', [$cpts, 'register_post_types']);
        add_action('init', [$cpts, 'register_taxonomies']);

        // Admin
        add_action('admin_menu', [$admin, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$admin, 'enqueue_admin_assets']);
        add_action('admin_init', [$admin, 'register_plugin_settings']); // **NEW**: Register settings
        
        // Admin AJAX Handlers
        add_action('wp_ajax_ktc_save_structure', [$admin, 'ajax_save_structure']);
        add_action('wp_ajax_ktc_add_item', [$admin, 'ajax_add_item']);
        add_action('wp_ajax_ktc_update_item', [$admin, 'ajax_update_item']);
        add_action('wp_ajax_ktc_delete_item', [$admin, 'ajax_delete_item']);

        // Frontend
        add_filter('post_type_link', [$frontend, 'filter_lesson_permalink'], 10, 2);
        add_filter('template_include', [$frontend, 'load_lesson_template']);
        add_action('wp_enqueue_scripts', [$frontend, 'enqueue_frontend_assets']);
        add_shortcode('koptann_courses_archive', [$frontend, 'render_courses_archive_shortcode']);
        
        // Frontend AJAX Handler
        add_action('wp_ajax_ktc_mark_lesson_complete', [$frontend, 'ajax_mark_lesson_complete']);
    }

    /**
     * Plugin activation logic.
     */
    public static function activate() {
        require_once plugin_dir_path(__FILE__) . 'class-ktc-cpts.php';
        $cpts = new KTC_CPTs();
        $cpts->register_post_types();
        $cpts->register_taxonomies();
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation logic.
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}

<?php
/**
 * Plugin Name:       Koptann Courses Refactor
 * Plugin URI:        https://#
 * Description:       A lightweight, SEO-friendly course management system for WordPress. Create courses, sections, and lessons with a drag-and-drop course builder.
 * Version:           1.2.0
 * Author:            Your Name (Refactored by Gemini)
 * Author URI:        https://#
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       koptann-courses
 * Domain Path:       /languages
 */


// /koptann-courses
// |-- koptann-courses.php              (Main Plugin File)
// |-- /includes
// |   |-- class-koptann-courses-plugin.php (Core Orchestrator Class)
// |   |-- class-ktc-cpts.php             (CPT & Taxonomy Definitions)
// |   |-- class-ktc-admin.php            (Admin Area Logic & Builder)
// |   |-- class-ktc-frontend.php         (Frontend Logic & Display)
// |-- /templates
// |   |-- single-lesson-template.php     (Lesson Page HTML Template)


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define a constant for the plugin's main file path.
define('KTC_PLUGIN_FILE', __FILE__);

/**
 * Load the main plugin class.
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-koptann-courses-plugin.php';

/**
 * The main function for that returns the singleton instance of the plugin class.
 *
 * @return Koptann_Courses_Plugin
 */
function KTC() {
    return Koptann_Courses_Plugin::get_instance();
}

// Get the plugin running.
KTC();

// Register activation and deactivation hooks.
register_activation_hook(__FILE__, ['Koptann_Courses_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Koptann_Courses_Plugin', 'deactivate']);

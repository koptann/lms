<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class KTC_CPTs {

    // **NEW**: Constructor to add the rewrite rule hook.
    public function __construct() {
        add_action('init', [$this, 'add_custom_rewrite_rules']);
    }

    public function register_post_types() {
        $course_args = [
            'labels'        => ['name' => __('Courses', 'koptann-courses'), 'singular_name' => __('Course', 'koptann-courses'), 'add_new_item' => __('Add New Course', 'koptann-courses')],
            'public'        => true,
            'hierarchical'  => false,
            // **MODIFICATION**: Added 'author' to the supports array.
            'supports'      => ['title', 'editor', 'thumbnail', 'author'],
            'taxonomies'    => ['course_category', 'course_tag'],
            'has_archive'   => 'courses',
            'rewrite'       => ['slug' => 'courses', 'with_front' => false],
            'menu_icon'     => 'dashicons-welcome-learn-more',
            'show_in_rest'  => true,
            'show_in_menu'  => false,
        ];
        register_post_type('course', $course_args);

        $section_args = [
            'labels'        => ['name' => __('Sections', 'koptann-courses'), 'singular_name' => __('Section', 'koptann-courses')],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'supports'      => ['title', 'editor', 'page-attributes'],
        ];
        register_post_type('section', $section_args);

        $lesson_args = [
            'labels'        => ['name' => __('Lessons', 'koptann-courses'), 'singular_name' => __('Lesson', 'koptann-courses')],
            'public'        => true,
            'show_in_menu'  => false,
            'supports'      => ['title', 'editor', 'excerpt', 'comments', 'page-attributes'],
            'rewrite'       => ['slug' => 'courses/%course%', 'with_front' => false],
            'show_in_rest'  => true,
        ];
        register_post_type('lesson', $lesson_args);
    }
    
    public function register_taxonomies() {
        register_taxonomy('course_category', 'course', [
            'hierarchical' => true,
            'labels' => ['name' => __('Course Categories', 'koptann-courses'), 'singular_name' => __('Course Category', 'koptann-courses')],
            'show_ui' => true, 'show_admin_column' => true, 'rewrite' => ['slug' => 'course-category'], 'show_in_rest' => true,
        ]);

        register_taxonomy('course_tag', 'course', [
            'hierarchical' => false,
            'labels' => ['name' => __('Course Tags', 'koptann-courses'), 'singular_name' => __('Course Tag', 'koptann-courses')],
            'show_ui' => true, 'show_admin_column' => true, 'rewrite' => ['slug' => 'course-tag'], 'show_in_rest' => true,
        ]);
    }

    /**
     * **NEW**: Adds the rewrite rule to interpret the custom lesson URL structure.
     */
    public function add_custom_rewrite_rules() {
        add_rewrite_rule(
            '^courses/([^/]+)/([^/]+)/?$',
            'index.php?lesson=$matches[2]',
            'top'
        );
    }
}

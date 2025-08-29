<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * KTC_Helpers Class
 *
 * A dedicated class for reusable helper functions. This improves code organization
 * by separating data retrieval and processing logic from the frontend display logic.
 */
class KTC_Helpers {

    /**
     * Get the next and previous lesson in a course.
     */
    public static function get_next_prev_lesson($current_lesson_id) {
        $course_id = get_post_meta($current_lesson_id, '_ktc_course_id', true);
        if (!$course_id) {
            return ['prev' => null, 'next' => null];
        }
        $ordered_lesson_ids = [];
        $sections_query = new WP_Query([
            'post_type'      => 'section',
            'posts_per_page' => -1,
            'meta_key'       => '_ktc_course_id',
            'meta_value'     => $course_id,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'fields'         => 'ids'
        ]);
        if ($sections_query->have_posts()) {
            foreach ($sections_query->posts as $section_id) {
                $lessons_query = new WP_Query([
                    'post_type'      => 'lesson',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'meta_key'       => '_ktc_section_id',
                    'meta_value'     => $section_id,
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                    'fields'         => 'ids'
                ]);
                if ($lessons_query->have_posts()) {
                    $ordered_lesson_ids = array_merge($ordered_lesson_ids, $lessons_query->posts);
                }
            }
        }
        if (empty($ordered_lesson_ids)) {
            return ['prev' => null, 'next' => null];
        }
        $current_key = array_search($current_lesson_id, $ordered_lesson_ids);
        if ($current_key === false) {
            return ['prev' => null, 'next' => null];
        }
        $prev_id = ($current_key > 0) ? $ordered_lesson_ids[$current_key - 1] : null;
        $next_id = ($current_key < count($ordered_lesson_ids) - 1) ? $ordered_lesson_ids[$current_key + 1] : null;
        return [
            'prev' => $prev_id ? get_post($prev_id) : null,
            'next' => $next_id ? get_post($next_id) : null,
        ];
    }
    
    /**
     * Get the first lesson of a given course.
     */
    public static function get_first_lesson_in_course($course_id) {
        $sections = get_posts(['post_type' => 'section', 'posts_per_page' => 1, 'meta_key' => '_ktc_course_id', 'meta_value' => $course_id, 'orderby' => 'menu_order', 'order' => 'ASC']);
        if (empty($sections)) return null;
        $lessons = get_posts(['post_type' => 'lesson', 'posts_per_page' => 1, 'post_status' => 'publish', 'meta_key' => '_ktc_section_id', 'meta_value' => $sections[0]->ID, 'orderby' => 'menu_order', 'order' => 'ASC']);
        return empty($lessons) ? null : $lessons[0];
    }

    /**
     * Calculate and return the total duration of a course in a human-readable format.
     */
    public static function get_total_course_duration($course_id) {
        $transient_key = 'ktc_total_duration_' . $course_id;
        $cached_duration = get_transient($transient_key);

        if (false !== $cached_duration) {
            return $cached_duration;
        }

        $total_minutes = self::get_total_lesson_minutes($course_id);

        if ($total_minutes == 0) {
            set_transient($transient_key, '', HOUR_IN_SECONDS);
            return '';
        }

        $hours = floor($total_minutes / 60);
        $minutes = $total_minutes % 60;

        $duration_string = '';
        if ($hours > 0) {
            $duration_string .= $hours . ' ' . _n('hour', 'hours', $hours, 'koptann-courses');
        }
        if ($minutes > 0) {
            $duration_string .= ' ' . $minutes . ' ' . _n('minute', 'minutes', $minutes, 'koptann-courses');
        }

        $final_duration = trim($duration_string);
        set_transient($transient_key, $final_duration, HOUR_IN_SECONDS);

        return $final_duration;
    }
    
    /**
     * Get the total duration of a course in minutes.
     */
    public static function get_total_lesson_minutes($course_id) {
        $total_minutes = 0;
        $lessons = get_posts([
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => '_ktc_course_id',
            'meta_value' => $course_id,
        ]);

        foreach ($lessons as $lesson) {
            $duration = get_post_meta($lesson->ID, '_ktc_lesson_duration_minutes', true);
            if (is_numeric($duration)) {
                $total_minutes += intval($duration);
            }
        }
        return $total_minutes;
    }

    /**
     * Get the total number of lessons in a course.
     */
    public static function get_lesson_count($course_id) {
        $query = new WP_Query([
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'meta_key' => '_ktc_course_id',
            'meta_value' => $course_id,
            'fields' => 'ids'
        ]);
        return $query->post_count;
    }

    /**
     * Check if a lesson is complete for the current user.
     */
    public static function is_lesson_complete($lesson_id, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) return false;

        $course_id = get_post_meta($lesson_id, '_ktc_course_id', true);
        if (!$course_id) return false;

        $completed_lessons = get_user_meta($user_id, '_ktc_completed_lessons_' . $course_id, true);
        return is_array($completed_lessons) && in_array($lesson_id, $completed_lessons);
    }

    /**
     * Calculate course completion percentage for the current user.
     */
    public static function get_course_progress($course_id, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) return 0;

        $total_lessons = self::get_lesson_count($course_id);

        if ($total_lessons === 0) return 0;

        $completed_lessons = get_user_meta($user_id, '_ktc_completed_lessons_' . $course_id, true);
        $completed_count = is_array($completed_lessons) ? count($completed_lessons) : 0;

        return round(($completed_count / $total_lessons) * 100);
    }
}

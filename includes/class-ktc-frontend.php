<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class KTC_Frontend {

    public function filter_lesson_permalink($post_link, $post) {
        if ('lesson' === $post->post_type && strpos($post_link, '%course%') !== false) {
            $course_id = get_post_meta($post->ID, '_ktc_course_id', true);
            if ($course_id && ($course = get_post($course_id))) {
                $post_link = str_replace('%course%', $course->post_name, $post_link);
            } else {
                $post_link = str_replace('/%course%', '', $post_link);
            }
        }
        return $post_link;
    }

    public function load_lesson_template($template) {
        if (is_singular('course')) {
            add_filter('body_class', function($classes) {
                $classes[] = 'ktc-single-course-page';
                return $classes;
            });
            $theme_template = get_stylesheet_directory() . '/single-course.php';
            if (file_exists($theme_template)) {
                return $theme_template;
            }
            return plugin_dir_path(dirname(__FILE__)) . 'templates/single-course-template.php';
        }

        if (is_singular('lesson')) {
            add_filter('body_class', function($classes) {
                $classes[] = 'ktc-lesson-view-active';
                return $classes;
            });
            $theme_template = get_stylesheet_directory() . '/single-lesson.php';
            if (file_exists($theme_template)) {
                return $theme_template;
            }
            return plugin_dir_path(dirname(__FILE__)) . 'templates/single-lesson-template.php';
        }
        return $template;
    }
    
    public function render_courses_archive_shortcode($atts) {
        $courses = get_posts(['post_type' => 'course', 'post_status' => 'publish', 'posts_per_page' => -1]);
        if (empty($courses)) return '<p>' . __('No courses are available.', 'koptann-courses') . '</p>';
        ob_start();
        ?>
        <div class="ktc-archive-container">
            <div class="ktc-view-toggle">
                <button id="ktc-grid-view-btn" class="active" title="<?php _e('Grid View', 'koptann-courses'); ?>">&#9638;</button>
                <button id="ktc-list-view-btn" title="<?php _e('List View', 'koptann-courses'); ?>">&#9776;</button>
            </div>
            <div id="ktc-courses-archive" class="ktc-courses-archive-grid">
                <?php foreach($courses as $course): ?>
                    <div class="ktc-course-archive-item">
                        <a href="<?php echo esc_url(get_permalink($course->ID)); ?>" class="ktc-course-archive-image-link">
                            <?php if (has_post_thumbnail($course->ID)): 
                                echo get_the_post_thumbnail($course->ID, 'medium_large'); 
                            else: ?>
                                <div class="ktc-placeholder-image"></div>
                            <?php endif; ?>
                        </a>
                        <div class="ktc-course-archive-content">
                            <h3 class="ktc-course-archive-title">
                                <a href="<?php echo esc_url(get_permalink($course->ID)); ?>"><?php echo esc_html($course->post_title); ?></a>
                            </h3>
                            <div class="ktc-course-archive-excerpt">
                                <?php echo has_excerpt($course->ID) ? wp_kses_post(get_the_excerpt($course->ID)) : wp_trim_words(wp_kses_post($course->post_content), 25, '...'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function enqueue_frontend_assets() {
        global $post;
        if (is_singular('lesson') || is_singular('course') || (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'koptann-courses_archive'))) {
            $frontend_css = "
            /* --- Course Archive --- */
            .ktc-archive-container { margin: 2em 0; }
            .ktc-view-toggle { text-align: right; margin-bottom: 1em; }
            .ktc-view-toggle button { background: #f0f0f0; border: 1px solid #ccc; padding: 5px 10px; cursor: pointer; font-size: 18px; }
            .ktc-view-toggle button.active { background: #e0e0e0; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); }
            .ktc-courses-archive-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
            .ktc-courses-archive-list .ktc-course-archive-item { display: flex; align-items: flex-start; gap: 20px; }
            .ktc-courses-archive-list .ktc-course-archive-image-link { width: 200px; flex-shrink: 0; }
            .ktc-course-archive-item { border: 1px solid #ddd; border-radius: 4px; overflow: hidden; transition: box-shadow .2s; background: #fff; }
            .ktc-course-archive-item:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            .ktc-course-archive-image-link img, .ktc-placeholder-image { display: block; width: 100%; height: auto; aspect-ratio: 16/9; object-fit: cover; background-color: #eee;}
            .ktc-course-archive-content { padding: 15px; }
            .ktc-course-archive-title { font-size: 1.2em; margin: 0 0 0.5em; } .ktc-course-archive-title a { text-decoration: none; color: inherit; }
            .ktc-course-archive-excerpt { font-size: 0.9em; color: #555; }

            /* --- NEW & IMPROVED: Single Course Page --- */
            .ktc-single-course-page .entry-header { display: none; }
            .ktc-course-top-bar { background: #2d2f31; color: #fff; padding: 1.5em 0; }
            .ktc-course-top-bar-inner { max-width: 1100px; margin: 0 auto; padding: 0 2em; }
            .ktc-course-top-bar .ktc-breadcrumbs { color: #ccc; font-size: 0.9em; margin-bottom: 0.5em; }
            .ktc-course-top-bar .ktc-breadcrumbs a { color: #fff; text-decoration: none; }
            .ktc-course-top-bar .entry-title { color: #fff; font-size: 2.2em; margin: 0; }
            .ktc-course-top-bar-meta { display: flex; flex-wrap: wrap; gap: 10px 20px; color: #ccc; margin-top: 10px; font-size: 0.9em; }
            .ktc-course-main-container { max-width: 1100px; margin: 0 auto; padding: 2em; display: flex; flex-wrap: wrap; align-items: flex-start; gap: 40px; }
            .ktc-course-left-col { flex: 1; min-width: 0; }
            .ktc-course-right-col { width: 340px; flex-shrink: 0; }
            .ktc-sticky-sidebar { position: sticky; top: 40px; }
            .ktc-sticky-sidebar-inner { background: #fff; border: 1px solid #ddd; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-radius: 4px; }
            .ktc-sticky-sidebar .ktc-course-image img { width: 100%; display: block; border-radius: 4px 4px 0 0; }
            .ktc-sticky-sidebar .ktc-sidebar-content { padding: 1.5em; }
            .ktc-sticky-sidebar .ktc-start-course-btn { display: block; width: 100%; text-align: center; padding: 15px; font-size: 1.1em; font-weight: bold; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
            .ktc-sticky-sidebar .ktc-start-course-btn:hover { opacity: 0.9; }
            .ktc-sticky-sidebar .ktc-course-meta { list-style: none; padding: 1em 0 0; margin-top: 1em; border-top: 1px solid #eee; }
            .ktc-sticky-sidebar .ktc-course-meta li { margin-bottom: 0.5em; display: flex; justify-content: space-between; }
            
            .ktc-course-tabs .ktc-tab-nav { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 1.5em; }
            .ktc-course-tabs .ktc-tab-nav-item { padding: 10px 0; margin-right: 30px; cursor: pointer; font-weight: bold; color: #555; border-bottom: 3px solid transparent; }
            .ktc-course-tabs .ktc-tab-nav-item.active { color: #000; border-bottom-color: #000; }
            .ktc-course-tabs .ktc-tab-panel { display: none; }
            .ktc-course-tabs .ktc-tab-panel.active { display: block; }

            .ktc-curriculum h2 { margin-bottom: 1em; font-size: 1.5em; }
            .ktc-curriculum .ktc-section-item { border: 1px solid #ddd; }
            .ktc-curriculum .ktc-section-item + .ktc-section-item { border-top: none; }
            .ktc-curriculum .ktc-section-title { font-weight: bold; font-size: 1.1em; padding: 15px; background: #f7f7f7; cursor: pointer; position: relative; display: flex; justify-content: space-between; align-items: center; }
            .ktc-curriculum .ktc-section-meta { font-size: 0.8em; font-weight: normal; color: #555; }
            .ktc-curriculum .ktc-section-title:after { content: '+'; font-weight: bold; }
            .ktc-curriculum .ktc-section-item.ktc-open > .ktc-section-title:after { content: '-'; }
            .ktc-curriculum .ktc-lesson-list { list-style: none; padding: 0; margin: 0; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; background: #fff; }
            .ktc-curriculum .ktc-section-item.ktc-open > .ktc-lesson-list { max-height: 1000px; }
            .ktc-curriculum .ktc-lesson-list li { padding: 10px 15px 10px 35px; border-top: 1px solid #eee; position: relative; display: flex; justify-content: space-between; align-items: center; }
            .ktc-curriculum .ktc-lesson-list li:before { content: '\\25BA'; font-size: 10px; position: absolute; left: 15px; top: 13px; color: #777; }
            .ktc-curriculum .ktc-lesson-duration { font-size: 0.9em; color: #555; }
            .ktc-course-description h2 { font-size: 1.5em; }

            /* --- Lesson Playground --- */
            .ktc-lesson-container { display: flex; gap: 30px; }
            .ktc-lesson-sidebar { width: 320px; flex-shrink: 0; }
            .ktc-lesson-content-wrap { flex-grow: 1; min-width: 0; }
            .ktc-lesson-content { padding-bottom: 80px; }
            .ktc-sidebar-header { padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px 4px 0 0; background: #f5f5f5; }
            .ktc-sidebar-header .ktc-course-title-sidebar { margin: 0; font-size: 1.1em; }
            .ktc-sidebar-header .ktc-course-title-sidebar a { text-decoration: none; color: inherit; }
            .ktc-course-outline { border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 4px 4px; }
            .ktc-outline-section-title { background: #fff; padding: 12px 15px; margin: 0; cursor: pointer; font-size: 1em; position: relative; border-top: 1px solid #e0e0e0; font-weight: bold; }
            .ktc-outline-section:first-child .ktc-outline-section-title { border-top: none; }
            .ktc-outline-section-title:hover { background: #f9f9f9; }
            .ktc-outline-section-title:after { content: '\\25B8'; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); transition: transform .2s ease-in-out; font-size: .8em; }
            .ktc-outline-section.ktc-open > .ktc-outline-section-title:after { transform: translateY(-50%) rotate(90deg); }
            .ktc-outline-lesson-list { list-style: none; margin: 0; padding: 0; background: #fdfdfd; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; }
            .ktc-outline-section.ktc-open > .ktc-outline-lesson-list { max-height: 1000px; }
            .ktc-outline-lesson-list li a { padding: 10px 15px 10px 30px; text-decoration: none; color: #333; display: flex; align-items: center; gap: 8px; border-top: 1px solid #f0f0f0; transition: background-color 0.2s; }
            .ktc-outline-lesson-list li a:hover { background: #f5f5f5; }
            .ktc-outline-lesson-list .ktc-current-lesson a { background: #e9f5ff; color: #005a9c; font-weight: bold; }
            .ktc-lesson-navigation { display: flex; justify-content: space-between; align-items: center; padding: 1em; border-top: 1px solid #eee; background: rgba(255,255,255,0.98); position: fixed; bottom: 0; left: 0; right: 0; z-index: 100; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
            .ktc-nav-previous, .ktc-nav-next { flex-basis: 40%; }
            .ktc-nav-complete { flex-basis: 20%; text-align: center; }
            .ktc-nav-next { text-align: right; }
            #ktc-mark-complete-btn { padding: 8px 15px; font-size: 0.9em; cursor: pointer; border-radius: 4px; border: 1px solid #2271b1; background: #2271b1; color: #fff; }
            #ktc-mark-complete-btn.completed { background: #dff0d8; color: #3c763d; border-color: #d6e9c6; cursor: default; }
            .ktc-lesson-completed-icon { color: #28a745; font-weight: bold; }

            /* --- Progress Bar --- */
            .ktc-progress-bar-container { margin: 1.5em 0 0; }
            .ktc-progress-bar-label { display: flex; justify-content: space-between; font-size: 0.9em; margin-bottom: 5px; color: #555; }
            .ktc-progress-bar-wrapper { background: #e9ecef; border-radius: 4px; overflow: hidden; height: 10px; }
            .ktc-progress-bar { background: #28a745; height: 100%; width: 0%; transition: width 0.4s ease-in-out; }

            /* --- Mobile Responsive Styles --- */
            #ktc-sidebar-toggle { display: none; }
            @media (max-width: 991px) {
                .ktc-course-right-col { width: 100%; order: -1; }
                .ktc-sticky-sidebar { position: static; }
                .ktc-lesson-sidebar { position: fixed; top: 0; left: 0; width: 300px; max-width: 90%; height: 100%; z-index: 10000; background: #fff; transition: transform 0.3s ease-in-out; transform: translateX(-100%); display: flex; flex-direction: column; }
                body.ktc-sidebar-open .ktc-lesson-sidebar { transform: translateX(0); box-shadow: 3px 0 15px rgba(0,0,0,0.1); }
                body.ktc-sidebar-open #ktc-sidebar-overlay { display: block; }
                .ktc-course-outline { flex-grow: 1; overflow-y: auto; }
                #ktc-sidebar-toggle { display: inline-block; background: #2271b1; color: #fff; border: none; border-radius: 4px; padding: 8px 12px; font-size: 1em; cursor: pointer; margin-bottom: 1em; }
                #ktc-sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.5); }
            }
            ";
            wp_register_style('ktc-frontend-styles', false);
            wp_enqueue_style('ktc-frontend-styles');
            wp_add_inline_style('ktc-frontend-styles', $frontend_css);

            wp_register_script('ktc-frontend-script', false, [], null, true);
            wp_enqueue_script('ktc-frontend-script');
            
            // **MODIFICATION**: Localize script for AJAX requests on the lesson page.
            if (is_singular('lesson')) {
                wp_localize_script('ktc-frontend-script', 'KTC_Lesson_Data', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('ktc_mark_lesson_complete_nonce'),
                    'lesson_id' => get_the_ID(),
                    'completed_text' => __('✓ Completed', 'koptann-courses'),
                ]);
            }

            ob_start();
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // --- Archive View Toggle ---
                const gridBtn = document.getElementById('ktc-grid-view-btn');
                const listBtn = document.getElementById('ktc-list-view-btn');
                const archive = document.getElementById('ktc-courses-archive');
                if (gridBtn && listBtn && archive) {
                    gridBtn.addEventListener('click', function() {
                        archive.classList.remove('ktc-courses-archive-list');
                        archive.classList.add('ktc-courses-archive-grid');
                        gridBtn.classList.add('active');
                        listBtn.classList.remove('active');
                    });
                    listBtn.addEventListener('click', function() {
                        archive.classList.remove('ktc-courses-archive-grid');
                        archive.classList.add('ktc-courses-archive-list');
                        listBtn.classList.add('active');
                        gridBtn.classList.remove('active');
                    });
                }

                // --- Accordion for Curriculum/Sidebar ---
                document.querySelectorAll('.ktc-outline-section-title, .ktc-curriculum .ktc-section-title').forEach(title => {
                    title.addEventListener('click', (e) => {
                        e.preventDefault();
                        title.parentElement.classList.toggle('ktc-open');
                    });
                });

                // --- Mobile Sidebar Toggle ---
                const toggleBtn = document.getElementById('ktc-sidebar-toggle');
                const overlay = document.getElementById('ktc-sidebar-overlay');
                const body = document.body;
                if (toggleBtn && overlay) {
                    toggleBtn.addEventListener('click', function() { body.classList.add('ktc-sidebar-open'); });
                    overlay.addEventListener('click', function() { body.classList.remove('ktc-sidebar-open'); });
                }

                // --- Course Page Tabs ---
                const tabsContainer = document.querySelector('.ktc-course-tabs');
                if (tabsContainer) {
                    const navItems = tabsContainer.querySelectorAll('.ktc-tab-nav-item');
                    const panels = tabsContainer.querySelectorAll('.ktc-tab-panel');
                    navItems.forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            const targetId = this.getAttribute('data-tab');
                            navItems.forEach(nav => nav.classList.remove('active'));
                            this.classList.add('active');
                            panels.forEach(panel => {
                                panel.id === targetId ? panel.classList.add('active') : panel.classList.remove('active');
                            });
                        });
                    });
                }

                // **NEW**: Mark Lesson Complete AJAX Handler
                const completeBtn = document.getElementById('ktc-mark-complete-btn');
                if (completeBtn) {
                    completeBtn.addEventListener('click', function() {
                        if (this.classList.contains('completed')) return;

                        fetch(KTC_Lesson_Data.ajax_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'ktc_mark_lesson_complete',
                                security: KTC_Lesson_Data.nonce,
                                lesson_id: KTC_Lesson_Data.lesson_id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                completeBtn.classList.add('completed');
                                completeBtn.textContent = KTC_Lesson_Data.completed_text;
                                
                                // Update sidebar icon
                                const sidebarLink = document.querySelector(`.ktc-outline-lesson-list li[data-lesson-id="${KTC_Lesson_Data.lesson_id}"] a`);
                                if(sidebarLink && !sidebarLink.querySelector('.ktc-lesson-completed-icon')) {
                                    sidebarLink.insertAdjacentHTML('afterbegin', '<span class="ktc-lesson-completed-icon">✓</span>');
                                }

                                // Auto-advance if next lesson exists
                                if (data.data.next_lesson_url) {
                                    window.location.href = data.data.next_lesson_url;
                                }
                            } else {
                                alert(data.data.message || 'An error occurred.');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    });
                }
            });
            </script>
            <?php
            wp_add_inline_script('ktc-frontend-script', ob_get_clean());
        }
    }
    
    public function get_next_prev_lesson($current_lesson_id) {
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
    
    public function get_first_lesson_in_course($course_id) {
        $sections = get_posts(['post_type' => 'section', 'posts_per_page' => 1, 'meta_key' => '_ktc_course_id', 'meta_value' => $course_id, 'orderby' => 'menu_order', 'order' => 'ASC']);
        if (empty($sections)) return null;
        $lessons = get_posts(['post_type' => 'lesson', 'posts_per_page' => 1, 'post_status' => 'publish', 'meta_key' => '_ktc_section_id', 'meta_value' => $sections[0]->ID, 'orderby' => 'menu_order', 'order' => 'ASC']);
        return empty($lessons) ? null : $lessons[0];
    }

    public function get_total_course_duration($course_id) {
        $transient_key = 'ktc_total_duration_' . $course_id;
        $cached_duration = get_transient($transient_key);

        if (false !== $cached_duration) {
            return $cached_duration;
        }

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
     * **NEW**: AJAX handler for marking a lesson as complete.
     */
    public function ajax_mark_lesson_complete() {
        check_ajax_referer('ktc_mark_lesson_complete_nonce', 'security');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in.']);
        }

        $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        if (!$lesson_id || 'lesson' !== get_post_type($lesson_id)) {
            wp_send_json_error(['message' => 'Invalid lesson specified.']);
        }

        $user_id = get_current_user_id();
        $course_id = get_post_meta($lesson_id, '_ktc_course_id', true);

        // Get current completed lessons for this course
        $completed_lessons = get_user_meta($user_id, '_ktc_completed_lessons_' . $course_id, true);
        if (!is_array($completed_lessons)) {
            $completed_lessons = [];
        }

        // Add the current lesson if it's not already there
        if (!in_array($lesson_id, $completed_lessons)) {
            $completed_lessons[] = $lesson_id;
            update_user_meta($user_id, '_ktc_completed_lessons_' . $course_id, $completed_lessons);
        }

        // Get next lesson for auto-advancement
        $nav = $this->get_next_prev_lesson($lesson_id);
        $next_lesson_url = $nav['next'] ? get_permalink($nav['next']->ID) : null;

        wp_send_json_success(['next_lesson_url' => $next_lesson_url]);
    }

    /**
     * **NEW**: Helper function to check if a lesson is complete for the current user.
     */
    public function is_lesson_complete($lesson_id, $user_id = null) {
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
     * **NEW**: Helper function to calculate course completion percentage.
     */
    public function get_course_progress($course_id, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) return 0;

        $all_lessons_query = new WP_Query([
            'post_type' => 'lesson', 'posts_per_page' => -1, 'post_status' => 'publish',
            'meta_key' => '_ktc_course_id', 'meta_value' => $course_id, 'fields' => 'ids'
        ]);
        $total_lessons = $all_lessons_query->post_count;

        if ($total_lessons === 0) return 0;

        $completed_lessons = get_user_meta($user_id, '_ktc_completed_lessons_' . $course_id, true);
        $completed_count = is_array($completed_lessons) ? count($completed_lessons) : 0;

        return round(($completed_count / $total_lessons) * 100);
    }
}

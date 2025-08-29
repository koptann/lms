<?php
/**
 * The template for displaying a single course landing page.
 */

global $post;
// **MODIFICATION**: No longer need to instantiate the frontend class here.
$first_lesson = KTC_Helpers::get_first_lesson_in_course($post->ID);
$total_duration = KTC_Helpers::get_total_course_duration($post->ID);
$is_free = get_post_meta($post->ID, '_ktc_is_free', true);
$lesson_count = KTC_Helpers::get_lesson_count($post->ID);
$progress = is_user_logged_in() ? KTC_Helpers::get_course_progress($post->ID) : 0;

get_header();
?>

<div class="ktc-single-course-page-container">
    <header class="ktc-course-top-bar">
        <div class="ktc-course-top-bar-inner">
            <div class="ktc-breadcrumbs">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'koptann-courses'); ?></a> &raquo;
                <a href="<?php echo esc_url(get_post_type_archive_link('course')); ?>"><?php _e('Courses', 'koptann-courses'); ?></a>
            </div>
            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
            <div class="ktc-course-top-bar-meta">
                <span>&#128100; <?php printf(__('By %s', 'koptann-courses'), get_the_author()); ?></span>
                <?php if (!empty($total_duration)) : ?>
                    <span>&#128337; <?php echo esc_html($total_duration); ?></span>
                <?php endif; ?>
                <?php if ($lesson_count > 0) : ?>
                    <span>&#128210; <?php printf(_n('%s Lesson', '%s Lessons', $lesson_count, 'koptann-courses'), $lesson_count); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="ktc-course-main-container">
        <div class="ktc-course-left-col">
            
            <div class="ktc-course-tabs">
                <nav class="ktc-tab-nav">
                    <a href="#tab-description" class="ktc-tab-nav-item active" data-tab="tab-description"><?php _e('Description', 'koptann-courses'); ?></a>
                    <a href="#tab-curriculum" class="ktc-tab-nav-item" data-tab="tab-curriculum"><?php _e('Curriculum', 'koptann-courses'); ?></a>
                </nav>

                <div id="tab-description" class="ktc-tab-panel active">
                    <div class="ktc-course-description">
                        <h2><?php _e('About this course', 'koptann-courses'); ?></h2>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>

                <div id="tab-curriculum" class="ktc-tab-panel">
                    <div class="ktc-curriculum">
                        <h2><?php _e('Course Curriculum', 'koptann-courses'); ?></h2>
                        <?php
                        $sections = get_posts(['post_type' => 'section', 'posts_per_page' => -1, 'meta_key' => '_ktc_course_id', 'meta_value' => $post->ID, 'orderby' => 'menu_order', 'order' => 'ASC']);
                        if ($sections) {
                            foreach ($sections as $section) {
                                $lessons = get_posts(['post_type' => 'lesson', 'posts_per_page' => -1, 'post_status' => 'publish', 'meta_key' => '_ktc_section_id', 'meta_value' => $section->ID]);
                                $section_lesson_count = count($lessons);
                                $section_total_minutes = 0;
                                foreach ($lessons as $lesson) {
                                    $duration = get_post_meta($lesson->ID, '_ktc_lesson_duration_minutes', true);
                                    if (is_numeric($duration)) {
                                        $section_total_minutes += intval($duration);
                                    }
                                }

                                echo '<div class="ktc-section-item">';
                                echo '<h3 class="ktc-section-title">' . esc_html($section->post_title);
                                if ($section_lesson_count > 0) {
                                    echo '<span class="ktc-section-meta">' . sprintf(_n('%s lesson', '%s lessons', $section_lesson_count, 'koptann-courses'), $section_lesson_count);
                                    if ($section_total_minutes > 0) {
                                        echo ' &bull; ' . $section_total_minutes . ' min';
                                    }
                                    echo '</span>';
                                }
                                echo '</h3>';

                                if ($lessons) {
                                    echo '<ul class="ktc-lesson-list">';
                                    foreach ($lessons as $lesson) {
                                        $lesson_duration = get_post_meta($lesson->ID, '_ktc_lesson_duration_minutes', true);
                                        echo '<li>' . esc_html($lesson->post_title);
                                        if (!empty($lesson_duration) && is_numeric($lesson_duration)) {
                                            echo '<span class="ktc-lesson-duration">' . $lesson_duration . ' min</span>';
                                        }
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }
                                echo '</div>';
                            }
                        } else {
                            echo '<p>' . __('The course curriculum has not been defined yet.', 'koptann-courses') . '</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="ktc-course-right-col">
            <div class="ktc-sticky-sidebar">
                <div class="ktc-sticky-sidebar-inner">
                    <div class="ktc-course-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else: ?>
                            <div class="ktc-placeholder-image"></div>
                        <?php endif; ?>
                    </div>
                    <div class="ktc-sidebar-content">
                        <?php if (is_user_logged_in() && $lesson_count > 0) : ?>
                        <div class="ktc-progress-bar-container">
                            <div class="ktc-progress-bar-label">
                                <span><?php _e('Your Progress', 'koptann-courses'); ?></span>
                                <span><?php echo esc_html($progress); ?>%</span>
                            </div>
                            <div class="ktc-progress-bar-wrapper">
                                <div class="ktc-progress-bar" style="width: <?php echo esc_attr($progress); ?>%;"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($first_lesson) : ?>
                            <a href="<?php echo esc_url(get_permalink($first_lesson->ID)); ?>" class="ktc-start-course-btn button"><?php echo ($progress > 0 && $progress < 100) ? __('Continue Course', 'koptann-courses') : __('Start Course', 'koptann-courses'); ?></a>
                        <?php endif; ?>

                        <ul class="ktc-course-meta">
                            <?php if ($is_free === '1') : ?>
                                <li><strong><?php _e('Price:', 'koptann-courses'); ?></strong> <span><?php _e('Free', 'koptann-courses'); ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();

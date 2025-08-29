<?php
/**
 * The template for displaying the single lesson "playground".
 */

global $post;
$frontend = new KTC_Frontend();

$course_id = get_post_meta($post->ID, '_ktc_course_id', true);
if (!$course_id || !($course = get_post($course_id))) {
    wp_die(__('This lesson is not associated with a valid course.', 'koptann-courses'));
}

add_filter('body_class', function($classes) {
    $classes[] = 'ktc-lesson-view-active';
    return $classes;
});

get_header(); 
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="ktc-lesson-container">
            <aside class="ktc-lesson-sidebar">
                <div class="ktc-sidebar-header">
                    <h3 class="ktc-course-title-sidebar">
                        <a href="<?php echo esc_url(get_permalink($course->ID)); ?>">&larr; <?php echo esc_html($course->post_title); ?></a>
                    </h3>
                </div>
                
                <nav class="ktc-course-outline">
                    <?php
                    $sections = get_posts(['post_type' => 'section', 'posts_per_page' => -1, 'meta_key' => '_ktc_course_id', 'meta_value' => $course_id, 'orderby' => 'menu_order', 'order' => 'ASC']);
                    $current_section_id = get_post_meta($post->ID, '_ktc_section_id', true);
                    foreach ($sections as $section) {
                        $is_current_section = ($section->ID == $current_section_id);
                        echo '<div class="ktc-outline-section ' . ($is_current_section ? 'ktc-open' : '') . '">';
                        echo '<h4 class="ktc-outline-section-title">' . esc_html($section->post_title) . '</h4>';
                        echo '<ul class="ktc-outline-lesson-list">';
                        $lessons = get_posts(['post_type' => 'lesson', 'posts_per_page' => -1, 'post_status' => 'publish', 'meta_key' => '_ktc_section_id', 'meta_value' => $section->ID, 'orderby' => 'menu_order', 'order' => 'ASC']);
                        foreach ($lessons as $lesson) {
                            $is_current = ($lesson->ID == $post->ID);
                            $class = $is_current ? 'ktc-current-lesson' : '';
                            echo '<li class="' . esc_attr($class) . '"><a href="' . esc_url(get_permalink($lesson->ID)) . '">' . esc_html($lesson->post_title) . '</a></li>';
                        }
                        echo '</ul></div>';
                    }
                    ?>
                </nav>
            </aside>

            <div class="ktc-lesson-content-wrap">
                <article id="post-<?php the_ID(); ?>" <?php post_class('ktc-lesson-content'); ?>>
                    <button id="ktc-sidebar-toggle">&#9776; <?php _e('Course Menu', 'koptann-courses'); ?></button>

                    <header class="entry-header"><?php the_title('<h1 class="entry-title">', '</h1>'); ?></header>
                    <div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
                    
                    <?php if (comments_open() || get_comments_number()) { comments_template(); } ?>
                </article>
            </div>
        </div>
    </main>
</div>

<!-- **MODIFIED**: Moved the navigation footer outside the main content area for correct fixed positioning. -->
<footer class="ktc-lesson-footer">
    <?php $nav = $frontend->get_next_prev_lesson($post->ID); ?>
    <nav class="ktc-lesson-navigation">
        <div class="ktc-nav-previous"><?php if ($nav['prev']): ?><a href="<?php echo get_permalink($nav['prev']->ID); ?>" rel="prev">&larr; <?php _e('Previous Lesson', 'koptann-courses'); ?></a><?php else: ?><span></span><?php endif; ?></div>
        <div class="ktc-nav-next"><?php if ($nav['next']): ?><a href="<?php echo get_permalink($nav['next']->ID); ?>" rel="next"><?php _e('Next Lesson', 'koptann-courses'); ?> &rarr;</a><?php else: ?><span></span><?php endif; ?></div>
    </nav>
</footer>

<div id="ktc-sidebar-overlay"></div>

<?php
get_footer();

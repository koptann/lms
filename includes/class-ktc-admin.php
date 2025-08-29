<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class KTC_Admin {
    
    public function __construct() {
        add_action('add_meta_boxes_lesson', [$this, 'add_lesson_details_meta_box']);
        add_action('save_post_lesson', [$this, 'save_lesson_details_meta_box']);
        add_action('add_meta_boxes_course', [$this, 'add_course_details_meta_box']);
        add_action('save_post_course', [$this, 'save_course_details_meta_box']);
    }

    public function add_admin_menu() {
        $parent_slug = 'koptann-course-builder';
        add_menu_page( __('Koptann Courses', 'koptann-courses'), __('Koptann Courses', 'koptann-courses'), 'edit_posts', $parent_slug, [$this, 'render_course_builder_page'], 'dashicons-welcome-learn-more', 20 );
        add_submenu_page( $parent_slug, __('Course Builder', 'koptann-courses'), __('Course Builder', 'koptann-courses'), 'edit_posts', $parent_slug, [$this, 'render_course_builder_page'] );
        add_submenu_page( $parent_slug, __('All Courses', 'koptann-courses'), __('All Courses', 'koptann-courses'), 'edit_posts', 'edit.php?post_type=course' );
        add_submenu_page( $parent_slug, __('Add New Course', 'koptann-courses'), __('Add New', 'koptann-courses'), 'edit_posts', 'post-new.php?post_type=course' );
        add_submenu_page( $parent_slug, __('Course Categories', 'koptann-courses'), __('Categories', 'koptann-courses'), 'manage_options', 'edit-tags.php?taxonomy=course_category&post_type=course' );
        add_submenu_page( $parent_slug, __('Course Tags', 'koptann-courses'), __('Tags', 'koptann-courses'), 'manage_options', 'edit-tags.php?taxonomy=course_tag&post_type=course' );
        // **NEW**: Add the settings page.
        add_submenu_page( $parent_slug, __('Settings', 'koptann-courses'), __('Settings', 'koptann-courses'), 'manage_options', 'ktc-settings', [$this, 'render_settings_page'] );
    }

    public function render_course_builder_page() {
        ?>
        <div class="wrap" id="ktc-course-builder">
            <h1><?php _e('Course Builder', 'koptann-courses'); ?> <a href="<?php echo admin_url('post-new.php?post_type=course'); ?>" class="page-title-action"><?php _e('Add New Course', 'koptann-courses'); ?></a></h1>
            <p><?php _e('Drag and drop sections and lessons to reorder them. The order is saved automatically.', 'koptann-courses'); ?></p>
            <ul class="ktc-course-list">
            <?php
            $courses = get_posts(['post_type' => 'course', 'posts_per_page' => -1, 'post_status' => 'any', 'orderby' => 'title', 'order' => 'ASC']);
            if ($courses) {
                foreach ($courses as $course) {
                    $this->render_course_builder_item($course->ID);
                }
            } else {
                echo '<li><p>' . __('No courses found. Please add a new course to begin.', 'koptann-courses') . '</p></li>';
            }
            ?>
            </ul>
        </div>
        <?php
    }

    private function render_course_builder_item($course_id) {
        $course = get_post($course_id);
        ?>
        <li class="ktc-course-item" data-course-id="<?php echo esc_attr($course_id); ?>">
            <div class="ktc-course-header">
                <span class="ktc-course-title"><?php echo esc_html($course->post_title); ?></span>
                <div class="ktc-course-actions">
                    <button class="button ktc-add-section"><?php _e('Add Section', 'koptann-courses'); ?></button>
                    <a href="<?php echo get_edit_post_link($course_id); ?>" class="button"><?php _e('Edit Course', 'koptann-courses'); ?></a>
                    <button class="button ktc-toggle-sections"><span class="dashicons dashicons-arrow-down-alt2"></span> <?php _e('Manage', 'koptann-courses'); ?></button>
                </div>
            </div>
            <div class="ktc-sections-container" style="display: none;">
                <ul class="ktc-section-list" data-course-id="<?php echo esc_attr($course_id); ?>">
                    <?php
                    $sections = get_posts(['post_type' => 'section', 'posts_per_page' => -1, 'meta_key' => '_ktc_course_id', 'meta_value' => $course_id, 'orderby' => 'menu_order', 'order' => 'ASC']);
                    if (!empty($sections)) {
                        foreach ($sections as $section) { $this->render_section_builder_item($section->ID); }
                    } else {
                        echo '<li class="ktc-no-items">' . __('No sections yet. Click "Add Section" to start.', 'koptann-courses') . '</li>';
                    }
                    ?>
                </ul>
            </div>
        </li>
        <?php
    }
    
    private function render_section_builder_item($section_id) {
        $section = get_post($section_id);
        ?>
        <li class="ktc-section-item" data-section-id="<?php echo esc_attr($section_id); ?>">
            <div class="ktc-section-header">
                <span class="ktc-drag-handle dashicons dashicons-menu"></span>
                <span class="ktc-section-title"><?php echo esc_html($section->post_title); ?></span>
                <div class="ktc-item-actions">
                    <a href="<?php echo get_edit_post_link($section_id); ?>" class="button button-small" target="_blank"><?php _e('Edit', 'koptann-courses'); ?></a>
                    <button class="button button-primary button-small ktc-add-lesson"><?php _e('Add Lesson', 'koptann-courses'); ?></button>
                    <button class="button-link-delete ktc-delete-item" data-type="section"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
            <ul class="ktc-lesson-list" data-section-id="<?php echo esc_attr($section_id); ?>">
                <?php
                $lessons = get_posts(['post_type' => 'lesson', 'posts_per_page' => -1, 'post_status' => 'any', 'meta_key' => '_ktc_section_id', 'meta_value' => $section_id, 'orderby' => 'menu_order', 'order' => 'ASC']);
                if (!empty($lessons)) {
                    foreach ($lessons as $lesson) { $this->render_lesson_builder_item($lesson->ID); }
                } else {
                    echo '<li class="ktc-no-items">' . __('No lessons in this section.', 'koptann-courses') . '</li>';
                }
                ?>
            </ul>
        </li>
        <?php
    }
    
    private function render_lesson_builder_item($lesson_id) {
        $lesson = get_post($lesson_id);
        $status_label = $lesson->post_status !== 'publish' ? ' <em class="ktc-post-status">(' . esc_html(get_post_status_object($lesson->post_status)->label) . ')</em>' : '';
        ?>
         <li class="ktc-lesson-item" data-lesson-id="<?php echo esc_attr($lesson_id); ?>">
            <span class="ktc-drag-handle dashicons dashicons-menu"></span>
            <span class="ktc-lesson-title"><?php echo esc_html($lesson->post_title) . $status_label; ?></span>
            <div class="ktc-item-actions">
                <a href="<?php echo get_edit_post_link($lesson_id); ?>" target="_blank"><?php _e('Edit', 'koptann-courses'); ?></a> |
                <a href="<?php echo get_permalink($lesson_id); ?>" target="_blank"><?php _e('View', 'koptann-courses'); ?></a> |
                <a href="#" class="ktc-delete-item" data-type="lesson"><?php _e('Delete', 'koptann-courses'); ?></a>
            </div>
        </li>
        <?php
    }

    public function enqueue_admin_assets($hook) {
        // Enqueue for builder page
        if ('koptann-courses_page_koptann-course-builder' === $hook || 'toplevel_page_koptann-course-builder' === $hook) {
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_localize_script('jquery-ui-sortable', 'KTC_Builder_Data', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonces'   => [
                    'save_structure' => wp_create_nonce('ktc_save_structure_nonce'),
                    'add_item'       => wp_create_nonce('ktc_add_item_nonce'),
                    'update_item'    => wp_create_nonce('ktc_update_item_nonce'),
                    'delete_item'    => wp_create_nonce('ktc_delete_item_nonce'),
                ],
                'prompts'  => [
                    'new_section'    => __('Enter the name for the new section:', 'koptann-courses'),
                    'new_lesson'     => __('Enter the name for the new lesson:', 'koptann-courses'),
                    'rename_section' => __('Enter the new name for the section:', 'koptann-courses'),
                    'confirm_delete' => __('Are you sure you want to permanently delete this item? This cannot be undone.', 'koptann-courses'),
                ]
            ]);
            
            $admin_css = "
            .ktc-course-list, .ktc-section-list, .ktc-lesson-list { list-style: none; margin: 0; padding: 0; }
            .ktc-course-item { background: #fff; border: 1px solid #ccd0d4; margin-bottom: 15px; }
            .ktc-course-header { padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; }
            .ktc-course-title { font-size: 1.2em; font-weight: bold; }
            .ktc-sections-container { padding: 0 15px 15px; } .ktc-section-list { margin-top: 15px; }
            .ktc-section-item { background: #f9f9f9; border: 1px solid #ddd; margin-bottom: 10px; }
            .ktc-section-header { padding: 8px 12px; display: flex; align-items: center; background: #f0f0f1; }
            .ktc-section-title { flex-grow: 1; font-weight: bold; } .ktc-drag-handle { cursor: move; color: #888; margin-right: 10px; }
            .ktc-lesson-list { padding-left: 20px; min-height: 20px; }
            .ktc-lesson-item { background: #fff; border: 1px solid #e5e5e5; padding: 6px 10px; margin: 5px 0; display: flex; align-items: center; }
            .ktc-lesson-title { flex-grow: 1; } .ktc-item-actions { white-space: nowrap; } .ktc-item-actions a, .ktc-item-actions button { margin-left: 8px; }
            .ktc-no-items { padding: 10px; color: #777; font-style: italic; }
            .ktc-post-status { color: #d54e21; font-weight: normal; } .ktc-sortable-placeholder { height: 40px; border: 1px dashed #ccc; background: #f7f7f7; margin: 5px 0; }
            .ktc-toggle-sections .dashicons { transition: transform 0.2s; } .ktc-toggle-sections.open .dashicons { transform: rotate(180deg); }
            .button-link-delete { color: #a00 !important; border: none; background: none; box-shadow: none; padding-top: 3px !important; cursor: pointer; }
            .button-link-delete:hover { color: #dc3232 !important; }";
            wp_add_inline_style('wp-admin', $admin_css);

            ob_start();
            ?>
            <script>
            jQuery(function($) {
                const builder = $('#ktc-course-builder');
                const ajaxAction = (action, data, successCallback) => {
                    $.post(KTC_Builder_Data.ajax_url, {
                        action: `ktc_${action}`,
                        security: KTC_Builder_Data.nonces[action],
                        ...data
                    }).done(response => {
                        if (response.success) {
                            successCallback(response.data);
                        } else {
                            alert(response.data?.message || 'An unknown error occurred.');
                        }
                    }).fail(() => alert('A server error occurred. Please try again.'));
                };

                const saveOrder = ($list) => {
                    const isSection = $list.hasClass('ktc-section-list');
                    const itemType = isSection ? 'section' : 'lesson';
                    const items = $list.children('.ktc-' + itemType + '-item').map((index, el) => ({
                        id: $(el).data(itemType + '-id'),
                        order: index,
                        parent: isSection ? $(el).closest('.ktc-section-list').data('course-id') : $(el).closest('.ktc-lesson-list').data('section-id')
                    })).get();
                    ajaxAction('save_structure', { item_type: itemType, items: items }, () => {});
                };

                $('.ktc-section-list, .ktc-lesson-list').sortable({
                    handle: '.ktc-drag-handle', axis: 'y', placeholder: 'ktc-sortable-placeholder', connectWith: '.ktc-lesson-list',
                    update: (event, ui) => {
                        saveOrder(ui.item.parent());
                        if (ui.sender) saveOrder(ui.sender);
                    }
                }).disableSelection();

                builder.on('click', '.ktc-toggle-sections', function(e) {
                    const $button = $(this);
                    const $courseItem = $button.closest('.ktc-course-item');
                    const $container = $courseItem.find('.ktc-sections-container');
                    if (!$button.hasClass('open')) {
                        $('.ktc-course-item .ktc-toggle-sections.open').not($button).removeClass('open').closest('.ktc-course-item').find('.ktc-sections-container').slideUp(200);
                    }
                    $button.toggleClass('open');
                    $container.slideToggle(200);
                });

                builder.on('click', '.ktc-add-section', e => {
                    const courseId = $(e.currentTarget).closest('.ktc-course-item').data('course-id');
                    const name = prompt(KTC_Builder_Data.prompts.new_section);
                    if (name) ajaxAction('add_item', { item_type: 'section', title: name, parent_id: courseId }, data => {
                        const $list = $(`.ktc-course-item[data-course-id="${courseId}"] .ktc-section-list`);
                        $list.find('.ktc-no-items').remove();
                        $list.append(data.html).sortable('refresh');
                    });
                });
                builder.on('click', '.ktc-add-lesson', e => {
                    const $section = $(e.currentTarget).closest('.ktc-section-item');
                    const sectionId = $section.data('section-id');
                    const courseId = $section.closest('.ktc-course-item').data('course-id');
                    const name = prompt(KTC_Builder_Data.prompts.new_lesson);
                    if (name) ajaxAction('add_item', { item_type: 'lesson', title: name, parent_id: sectionId, course_id: courseId }, data => {
                        const $list = $(`.ktc-section-item[data-section-id="${sectionId}"] .ktc-lesson-list`);
                        $list.find('.ktc-no-items').remove();
                        $list.append(data.html).sortable('refresh');
                    });
                });
                builder.on('click', '.ktc-delete-item', e => {
                    e.preventDefault();
                    if (!confirm(KTC_Builder_Data.prompts.confirm_delete)) return;
                    const $item = $(e.currentTarget).closest('[data-section-id], [data-lesson-id]');
                    const type = $item.is('.ktc-section-item') ? 'section' : 'lesson';
                    const id = $item.data(type + '-id');
                    ajaxAction('delete_item', { item_type: type, item_id: id }, () => $item.fadeOut(300, () => $item.remove()));
                });
            });
            </script>
            <?php
            wp_add_inline_script('jquery-ui-sortable', ob_get_clean());
        }

        // **NEW**: Enqueue assets for the new settings page.
        if ('koptann-courses_page_ktc-settings' === $hook) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            ob_start();
            ?>
            <script>
            jQuery(function($) {
                $('.ktc-color-picker').wpColorPicker();
            });
            </script>
            <?php
            wp_add_inline_script('wp-color-picker', ob_get_clean());
        }
    }
    
    public function ajax_save_structure() {
        check_ajax_referer('ktc_save_structure_nonce', 'security');
        if (!current_user_can('edit_posts')) wp_send_json_error();
        $item_type = sanitize_text_field($_POST['item_type']);
        $items = $_POST['items'] ?? [];
        foreach ($items as $item) {
            wp_update_post(['ID' => intval($item['id']), 'menu_order' => intval($item['order'])]);
            if ($item_type === 'lesson') {
                update_post_meta(intval($item['id']), '_ktc_section_id', intval($item['parent']));
            }
        }
        wp_send_json_success();
    }
    
    public function ajax_add_item() {
        check_ajax_referer('ktc_add_item_nonce', 'security');
        if (!current_user_can('edit_posts')) wp_send_json_error();
        $item_type = sanitize_text_field($_POST['item_type']);
        $title = sanitize_text_field($_POST['title']);
        $parent_id = intval($_POST['parent_id']);
        $new_post_id = wp_insert_post(['post_title' => $title, 'post_type' => $item_type, 'post_status' => ($item_type === 'lesson' ? 'draft' : 'publish')]);
        if ($new_post_id && !is_wp_error($new_post_id)) {
            if ($item_type === 'section') {
                update_post_meta($new_post_id, '_ktc_course_id', $parent_id);
                ob_start(); $this->render_section_builder_item($new_post_id); $html = ob_get_clean();
            } else {
                update_post_meta($new_post_id, '_ktc_section_id', $parent_id);
                update_post_meta($new_post_id, '_ktc_course_id', intval($_POST['course_id']));
                ob_start(); $this->render_lesson_builder_item($new_post_id); $html = ob_get_clean();
            }
            wp_send_json_success(['html' => $html]);
        }
        wp_send_json_error(['message' => 'Failed to create item.']);
    }
    
    public function ajax_update_item() {
        check_ajax_referer('ktc_update_item_nonce', 'security');
        if (!current_user_can('edit_posts')) wp_send_json_error();
        wp_update_post(['ID' => intval($_POST['item_id']), 'post_title' => sanitize_text_field($_POST['title'])]);
        wp_send_json_success();
    }
    
    public function ajax_delete_item() {
        check_ajax_referer('ktc_delete_item_nonce', 'security');
        if (!current_user_can('delete_posts')) wp_send_json_error();
        $item_id = intval($_POST['item_id']);
        if (sanitize_text_field($_POST['item_type']) === 'section') {
            $lessons = get_posts(['post_type' => 'lesson', 'posts_per_page' => -1, 'meta_key' => '_ktc_section_id', 'meta_value' => $item_id, 'fields' => 'ids']);
            foreach ($lessons as $lesson_id) wp_delete_post($lesson_id, true);
        }
        wp_delete_post($item_id, true) ? wp_send_json_success() : wp_send_json_error();
    }

    public function add_lesson_details_meta_box() {
        add_meta_box('ktc_lesson_details', __('Lesson Details', 'koptann-courses'), [$this, 'render_lesson_details_meta_box'], 'lesson', 'side', 'high');
    }

    public function render_lesson_details_meta_box($post) {
        wp_nonce_field('ktc_save_lesson_details', 'ktc_lesson_details_nonce');
        $duration = get_post_meta($post->ID, '_ktc_lesson_duration_minutes', true);
        ?>
        <p>
            <label for="ktc_lesson_duration_field"><strong><?php _e('Duration (in minutes)', 'koptann-courses'); ?></strong></label><br>
            <input type="number" id="ktc_lesson_duration_field" name="ktc_lesson_duration_field" value="<?php echo esc_attr($duration); ?>" style="width:100%;" />
        </p>
        <?php
    }

    public function save_lesson_details_meta_box($post_id) {
        if (!isset($_POST['ktc_lesson_details_nonce']) || !wp_verify_nonce($_POST['ktc_lesson_details_nonce'], 'ktc_save_lesson_details')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['ktc_lesson_duration_field'])) {
            $duration = intval($_POST['ktc_lesson_duration_field']);
            update_post_meta($post_id, '_ktc_lesson_duration_minutes', $duration);
        }

        $course_id = get_post_meta($post_id, '_ktc_course_id', true);
        if ($course_id) {
            delete_transient('ktc_total_duration_' . $course_id);
        }
    }

    public function add_course_details_meta_box() {
        add_meta_box('ktc_course_details', __('Course Details', 'koptann-courses'), [$this, 'render_course_details_meta_box'], 'course', 'side', 'high');
    }

    public function render_course_details_meta_box($post) {
        wp_nonce_field('ktc_save_course_details', 'ktc_course_details_nonce');
        $is_free = get_post_meta($post->ID, '_ktc_is_free', true);
        ?>
        <p>
            <input type="checkbox" id="ktc_is_free_field" name="ktc_is_free_field" value="1" <?php checked($is_free, '1'); ?> />
            <label for="ktc_is_free_field"><?php _e('This course is free', 'koptann-courses'); ?></label>
        </p>
        <?php
    }

    public function save_course_details_meta_box($post_id) {
        if (!isset($_POST['ktc_course_details_nonce']) || !wp_verify_nonce($_POST['ktc_course_details_nonce'], 'ktc_save_course_details')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $is_free = isset($_POST['ktc_is_free_field']) ? '1' : '0';
        update_post_meta($post_id, '_ktc_is_free', $is_free);
    }

    /**
     * **NEW**: Renders the main settings page wrapper.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Koptann Courses Settings', 'koptann-courses'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ktc_settings_group');
                do_settings_sections('ktc-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * **NEW**: Registers the settings, sections, and fields using the Settings API.
     */
    public function register_plugin_settings() {
        register_setting('ktc_settings_group', 'ktc_options', [$this, 'sanitize_settings']);

        add_settings_section('ktc_styling_section', __('Styling Options', 'koptann-courses'), null, 'ktc-settings');

        add_settings_field('ktc_primary_color', __('Primary Color', 'koptann-courses'), [$this, 'render_primary_color_field'], 'ktc-settings', 'ktc_styling_section');
    }

    /**
     * **NEW**: Renders the color picker field.
     */
    public function render_primary_color_field() {
        $options = get_option('ktc_options');
        $color = isset($options['primary_color']) ? $options['primary_color'] : '#2271b1';
        echo '<input type="text" name="ktc_options[primary_color]" value="' . esc_attr($color) . '" class="ktc-color-picker" />';
        echo '<p class="description">' . __('The main color for buttons and progress bars.', 'koptann-courses') . '</p>';
    }

    /**
     * **NEW**: Sanitizes settings before saving to the database.
     */
    public function sanitize_settings($input) {
        $new_input = [];
        if (isset($input['primary_color'])) {
            $new_input['primary_color'] = sanitize_hex_color($input['primary_color']);
        }
        return $new_input;
    }
}

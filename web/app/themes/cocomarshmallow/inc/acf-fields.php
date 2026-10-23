<?php
/**
 * ACF field groups, registered in code so they are version controlled.
 * Everything here is skipped silently if ACF Pro is not installed yet;
 * the theme falls back to plain post meta via cm_field().
 */
defined('ABSPATH') || exit;

add_action('acf/init', static function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => __('Site settings', 'cocomarshmallow'),
            'menu_title' => __('Site settings', 'cocomarshmallow'),
            'menu_slug'  => 'cm-site-settings',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-heart',
            'position'   => 59,
        ]);
    }

    // Book details (WooCommerce products)
    acf_add_local_field_group([
        'key'      => 'group_cm_book',
        'title'    => 'Book details',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'product']]],
        'position' => 'normal',
        'fields'   => [
            ['key' => 'field_cm_show_in_hero', 'label' => 'Feature in homepage hero', 'name' => 'show_in_hero', 'type' => 'true_false', 'ui' => 1,
             'instructions' => 'Tick for the book shown at the top of the homepage. If none is ticked, the newest book is used.'],
            ['key' => 'field_cm_blurb', 'label' => 'Blurb', 'name' => 'book_blurb', 'type' => 'textarea', 'rows' => 5, 'new_lines' => 'br',
             'instructions' => 'Shown in the hero and on book cards. Falls back to the short description.'],
            ['key' => 'field_cm_author', 'label' => 'Written by', 'name' => 'book_author', 'type' => 'text'],
            ['key' => 'field_cm_illustrator', 'label' => 'Illustrated by', 'name' => 'book_illustrator', 'type' => 'text'],
            ['key' => 'field_cm_age', 'label' => 'Age range', 'name' => 'book_age_range', 'type' => 'text', 'placeholder' => 'e.g. 3-7 years'],
            ['key' => 'field_cm_isbn', 'label' => 'ISBN', 'name' => 'book_isbn', 'type' => 'text'],
        ],
    ]);

    // Activity downloads
    acf_add_local_field_group([
        'key'      => 'group_cm_activity',
        'title'    => 'Activity download',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'cm_activity']]],
        'position' => 'acf_after_title',
        'fields'   => [
            ['key' => 'field_cm_activity_file', 'label' => 'File', 'name' => 'activity_file', 'type' => 'file', 'return_format' => 'id', 'required' => 1,
             'library' => 'all', 'mime_types' => 'pdf,zip,png,jpg,jpeg', 'instructions' => 'Upload the printable (PDF recommended).'],
            ['key' => 'field_cm_activity_age', 'label' => 'Age range', 'name' => 'activity_age_range', 'type' => 'text', 'placeholder' => 'e.g. 3-7 years'],
            ['key' => 'field_cm_activity_time', 'label' => 'Time needed', 'name' => 'activity_duration', 'type' => 'text', 'placeholder' => 'e.g. 10 minutes'],
        ],
    ]);

    // Reviews
    acf_add_local_field_group([
        'key'      => 'group_cm_review',
        'title'    => 'Review details',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'cm_review']]],
        'position' => 'normal',
        'fields'   => [
            ['key' => 'field_cm_review_rating', 'label' => 'Star rating (1-5)', 'name' => 'review_rating', 'type' => 'number', 'min' => 1, 'max' => 5, 'step' => 1, 'default_value' => 5, 'required' => 1],
            ['key' => 'field_cm_review_source', 'label' => 'Where was it posted?', 'name' => 'review_source', 'type' => 'text', 'default_value' => 'Etsy'],
            ['key' => 'field_cm_review_reply', 'label' => 'Your reply (optional)', 'name' => 'review_reply', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
            ['key' => 'field_cm_review_reply_by', 'label' => 'Reply signed by', 'name' => 'review_reply_by', 'type' => 'text', 'default_value' => 'Lysha and Amy'],
        ],
    ]);

    // Site-wide settings
    acf_add_local_field_group([
        'key'      => 'group_cm_settings',
        'title'    => 'Site settings',
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'cm-site-settings']]],
        'fields'   => [
            ['key' => 'field_cm_tab_contact', 'label' => 'Contact', 'type' => 'tab'],
            ['key' => 'field_cm_contact_email', 'label' => 'Email', 'name' => 'contact_email', 'type' => 'email'],
            ['key' => 'field_cm_contact_location', 'label' => 'Location', 'name' => 'contact_location', 'type' => 'text', 'default_value' => 'Bath, Somerset'],
            ['key' => 'field_cm_tab_social', 'label' => 'Social media', 'type' => 'tab'],
            ['key' => 'field_cm_social_instagram', 'label' => 'Instagram URL', 'name' => 'social_instagram', 'type' => 'url'],
            ['key' => 'field_cm_social_facebook', 'label' => 'Facebook URL', 'name' => 'social_facebook', 'type' => 'url'],
            ['key' => 'field_cm_social_tiktok', 'label' => 'TikTok URL', 'name' => 'social_tiktok', 'type' => 'url'],
            ['key' => 'field_cm_social_x', 'label' => 'X (Twitter) URL', 'name' => 'social_x', 'type' => 'url'],
            ['key' => 'field_cm_social_youtube', 'label' => 'YouTube URL', 'name' => 'social_youtube', 'type' => 'url'],
        ],
    ]);
});

/* Without ACF Pro, expose the same fields as simple meta boxes so content is still editable. */
add_action('add_meta_boxes', static function () {
    if (function_exists('acf_add_local_field_group')) {
        return;
    }
    add_meta_box('cm_book_fallback', 'Book details', static function ($post) {
        wp_nonce_field('cm_fallback', 'cm_fallback_nonce');
        foreach (['book_blurb' => 'Blurb', 'book_author' => 'Written by', 'book_illustrator' => 'Illustrated by', 'book_age_range' => 'Age range', 'book_isbn' => 'ISBN'] as $key => $label) {
            printf('<p><label><strong>%s</strong><br><input type="text" class="widefat" name="%s" value="%s"></label></p>',
                esc_html($label), esc_attr($key), esc_attr(get_post_meta($post->ID, $key, true)));
        }
        printf('<p><label><input type="checkbox" name="show_in_hero" value="1" %s> Feature in homepage hero</label></p>',
            checked(get_post_meta($post->ID, 'show_in_hero', true), '1', false));
    }, 'product');
    add_meta_box('cm_activity_fallback', 'Activity download', static function ($post) {
        wp_nonce_field('cm_fallback', 'cm_fallback_nonce');
        printf('<p><label><strong>File attachment ID</strong> (upload in Media, paste the ID)<br><input type="number" name="activity_file" value="%s"></label></p>',
            esc_attr(get_post_meta($post->ID, 'activity_file', true)));
        foreach (['activity_age_range' => 'Age range', 'activity_duration' => 'Time needed'] as $key => $label) {
            printf('<p><label><strong>%s</strong><br><input type="text" class="widefat" name="%s" value="%s"></label></p>',
                esc_html($label), esc_attr($key), esc_attr(get_post_meta($post->ID, $key, true)));
        }
    }, 'cm_activity');
});

add_action('add_meta_boxes', static function () {
    if (function_exists('acf_add_local_field_group')) {
        return;
    }
    add_meta_box('cm_review_fallback', 'Review details', static function ($post) {
        wp_nonce_field('cm_fallback', 'cm_fallback_nonce');
        foreach (['review_rating' => 'Star rating (1-5)', 'review_source' => 'Where was it posted?', 'review_reply' => 'Your reply (optional)', 'review_reply_by' => 'Reply signed by'] as $key => $label) {
            printf('<p><label><strong>%s</strong><br><input type="text" class="widefat" name="%s" value="%s"></label></p>',
                esc_html($label), esc_attr($key), esc_attr(get_post_meta($post->ID, $key, true)));
        }
    }, 'cm_review');
});

add_action('save_post', static function ($post_id) {
    if (function_exists('acf_add_local_field_group') || !isset($_POST['cm_fallback_nonce']) || !wp_verify_nonce($_POST['cm_fallback_nonce'], 'cm_fallback') || !current_user_can('edit_post', $post_id)) {
        return;
    }
    foreach (['book_blurb', 'book_author', 'book_illustrator', 'book_age_range', 'book_isbn', 'activity_file', 'activity_age_range', 'activity_duration', 'review_rating', 'review_source', 'review_reply', 'review_reply_by'] as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
        }
    }
    if (get_post_type($post_id) === 'product') {
        update_post_meta($post_id, 'show_in_hero', empty($_POST['show_in_hero']) ? '' : '1');
    }
});

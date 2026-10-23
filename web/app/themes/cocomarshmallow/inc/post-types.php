<?php
defined('ABSPATH') || exit;

/**
 * Children's activities: downloadable files (PDF colouring sheets, mindfulness
 * cards etc.). Not publicly routable on their own: they are listed on the
 * "Children's activities" page via the page-childrens-activities.php template.
 */
add_action('init', static function () {
    register_post_type('cm_activity', [
        'labels' => [
            'name'          => __('Activities', 'cocomarshmallow'),
            'singular_name' => __('Activity', 'cocomarshmallow'),
            'add_new_item'  => __('Add new activity', 'cocomarshmallow'),
            'edit_item'     => __('Edit activity', 'cocomarshmallow'),
            'menu_name'     => __('Activities', 'cocomarshmallow'),
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_position'      => 21,
        'menu_icon'          => 'dashicons-download',
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields'],
    ]);

    register_taxonomy('cm_activity_type', 'cm_activity', [
        'labels' => [
            'name'          => __('Activity types', 'cocomarshmallow'),
            'singular_name' => __('Activity type', 'cocomarshmallow'),
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'hierarchical'       => true,
        'show_admin_column'  => true,
    ]);
});

/**
 * Reader reviews shown on the homepage ("What our readers say").
 * Title = reviewer name, content = review text, post date = review date.
 */
add_action('init', static function () {
    register_post_type('cm_review', [
        'labels' => [
            'name'          => __('Reviews', 'cocomarshmallow'),
            'singular_name' => __('Review', 'cocomarshmallow'),
            'add_new_item'  => __('Add new review', 'cocomarshmallow'),
            'edit_item'     => __('Edit review', 'cocomarshmallow'),
            'title_field'   => __('Reviewer name', 'cocomarshmallow'),
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'menu_position'      => 22,
        'menu_icon'          => 'dashicons-format-quote',
        'supports'           => ['title', 'editor', 'page-attributes', 'custom-fields'],
    ]);
});

add_filter('enter_title_here', static fn ($t, $post) => $post->post_type === 'cm_review' ? __('Reviewer name', 'cocomarshmallow') : $t, 10, 2);

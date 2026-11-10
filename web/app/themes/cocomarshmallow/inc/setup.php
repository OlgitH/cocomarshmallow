<?php
defined('ABSPATH') || exit;

add_action('after_setup_theme', static function () {
    load_theme_textdomain('cocomarshmallow', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('custom-logo', ['height' => 120, 'width' => 120, 'flex-width' => true, 'flex-height' => true]);
    add_image_size('cm-book', 640, 640, false);
    add_image_size('cm-card', 480, 480, true);

    register_nav_menus([
        'primary' => __('Primary menu', 'cocomarshmallow'),
        'footer'  => __('Footer menu', 'cocomarshmallow'),
        'legal'   => __('Footer legal links', 'cocomarshmallow'),
    ]);
});

add_action('wp_enqueue_scripts', static function () {
    wp_enqueue_style('cm-main', get_theme_file_uri('assets/css/main.css'), [], filemtime(get_theme_file_path('assets/css/main.css')));
    wp_enqueue_script('cm-main', get_theme_file_uri('assets/js/main.js'), [], filemtime(get_theme_file_path('assets/js/main.js')), ['strategy' => 'defer', 'in_footer' => true]);
});

// Preload the body font to avoid a flash of fallback text.
add_action('wp_head', static function () {
    printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url(cm_asset('fonts/caslon-400.woff2')));
}, 1);

/** Favicon: the "C" from the logo in a crayon circle (used unless a Site Icon is set in the Customizer). */
add_action('wp_head', static function () {
    if (has_site_icon()) {
        return;
    }
    $f = static fn (string $file): string => esc_url(cm_asset('img/favicon/' . $file));
    echo '<link rel="icon" href="' . $f('favicon.ico') . '" sizes="48x48">' . "\n";
    echo '<link rel="icon" type="image/png" href="' . $f('icon-32.png') . '" sizes="32x32">' . "\n";
    echo '<link rel="icon" type="image/png" href="' . $f('icon-192.png') . '" sizes="192x192">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $f('icon-180.png') . '">' . "\n";
}, 2);

/** Add aria-current to the active menu link (in addition to WP classes). */
add_filter('nav_menu_link_attributes', static function ($atts, $item) {
    if (!empty($item->current) || !empty($item->current_item_ancestor)) {
        $atts['aria-current'] = !empty($item->current) ? 'page' : 'true';
    }
    return $atts;
}, 10, 2);

/** Keep excerpts short and tidy. */
add_filter('excerpt_length', static fn () => 28);
add_filter('excerpt_more', static fn () => '…');

/** Remove emoji scripts: fewer requests, less JS. */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/** Body class for the front page hero colour hook. */
add_filter('body_class', static function ($classes) {
    if (is_front_page()) {
        $classes[] = 'has-hero';
    }
    $style = cm_field('header_style', 'option');
    $classes[] = $style === 'fade' ? 'header-fade' : 'header-solid';
    return $classes;
});

<?php
/**
 * Idempotent starter content. Run with:  docker compose exec wordpress wp eval-file scripts/seed.php
 * All copy below is PLACEHOLDER: edit it in wp-admin.
 */

function cm_seed_page(string $slug, string $title, string $content = '', array $extra = []): int
{
    $existing = get_page_by_path($slug);
    if ($existing) {
        return $existing->ID;
    }
    return wp_insert_post(array_merge([
        'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug,
        'post_title' => $title, 'post_content' => $content,
    ], $extra));
}

function cm_seed_image(string $file, string $title, int $parent = 0): int
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $existing = get_posts(['post_type' => 'attachment', 'title' => $title, 'posts_per_page' => 1, 'post_status' => 'inherit', 'fields' => 'ids']);
    if ($existing) {
        return $existing[0];
    }
    $tmp = wp_tempnam($file);
    copy(get_theme_file_path('assets/img/' . $file), $tmp);
    $id = media_handle_sideload(['name' => $file, 'tmp_name' => $tmp], $parent, $title);
    return is_wp_error($id) ? 0 : $id;
}

// --- Site basics ---------------------------------------------------------
update_option('blogname', 'Cocomarshmallow Books');
update_option('blogdescription', 'Gentle picture books for little feelings, by children’s author Lysha Goode in Bath.');
update_option('timezone_string', 'Europe/London');
update_option('date_format', 'j F Y');
update_option('permalink_structure', '/%postname%/');

// --- Pages ---------------------------------------------------------------
$home    = cm_seed_page('home', 'Home');
$about   = cm_seed_page('about', 'About', <<<HTML
<!-- wp:paragraph --><p>Hello, I’m Lysha, a children’s author based in Bath. I write gentle, funny picture books that help little ones name and understand their big feelings.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>The Adventures of Coco Marshmallow follow a curious cat through everyday emotions, with a simple mindfulness activity in every book so families can explore each feeling together. Illustrations are by Amy Chandler.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><em>This is placeholder text. Please replace it with your own story.</em></p><!-- /wp:paragraph -->
HTML);
$act     = cm_seed_page('childrens-activities', 'Children’s activities', '<!-- wp:paragraph --><p>Printable colouring sheets, mindfulness cards and more to enjoy with Coco Marshmallow. Download, print and play!</p><!-- /wp:paragraph -->');
$news    = cm_seed_page('news-events', 'News & events');
$contact = cm_seed_page('contact', 'Contact', '<!-- wp:paragraph --><p>Questions about the books, school visits or events in and around Bath? Send a message and we’ll get back to you soon.</p><!-- /wp:paragraph -->');

update_post_meta($act, '_wp_page_template', 'page-childrens-activities.php');
update_post_meta($contact, '_wp_page_template', 'page-contact.php');
update_option('show_on_front', 'page');
update_option('page_on_front', $home);
update_option('page_for_posts', $news);

// Rank Math: focus keyword + title/description for the homepage
update_post_meta($home, 'rank_math_focus_keyword', 'Children\'s Author in Bath');
update_post_meta($home, 'rank_math_title', 'Children’s Author in Bath | Cocomarshmallow Books');
update_post_meta($home, 'rank_math_description', 'Cocomarshmallow Books: picture books by Bath children’s author Lysha Goode. Meet Coco Marshmallow, build emotional intelligence and download free kids’ activities.');
update_post_meta($about, 'rank_math_focus_keyword', 'children\'s author Bath');

// --- WooCommerce ---------------------------------------------------------
if (class_exists('WooCommerce')) {
    update_option('woocommerce_currency', 'GBP');
    update_option('woocommerce_default_country', 'GB');
    update_option('woocommerce_calc_taxes', 'no');
    update_option('woocommerce_onboarding_opt_in', 'no');

    // Turn the WooCommerce "Shop" page into "Books"
    $shop_id = (int) wc_get_page_id('shop');
    if ($shop_id > 0) {
        wp_update_post(['ID' => $shop_id, 'post_title' => 'Books', 'post_name' => 'books']);
    }

    // Rename the default product category
    wp_update_term((int) get_option('default_product_cat'), 'product_cat', ['name' => 'Books', 'slug' => 'books-category']);

    // Stripe (keys come from .env; see mu-plugins/cm-integrations.php)
    $stripe = get_option('woocommerce_stripe_settings', []);
    $stripe = is_array($stripe) ? $stripe : [];
    update_option('woocommerce_stripe_settings', array_merge($stripe, ['enabled' => 'yes', 'title' => 'Card (Stripe)']));

    // Sample book
    $existing = get_posts(['post_type' => 'product', 'title' => 'Happiness', 'posts_per_page' => 1, 'post_status' => 'any', 'fields' => 'ids']);
    if (!$existing) {
        $p = new WC_Product_Simple();
        $p->set_name('Happiness');
        $p->set_status('publish');
        $p->set_regular_price('7.99');
        $p->set_featured(true);
        $p->set_short_description('The Adventures of Coco Marshmallow: Happiness. Promoting early emotional intelligence, with a fun mindfulness activity inside.');
        $p->set_description('<p>Join Coco Marshmallow as she discovers what happiness feels like, from the tips of her ears to the tip of her tail.</p><p>Written by Lysha Goode, illustrated by Amy Chandler. Includes a fun mindfulness activity for little ones and grown-ups to share.</p>');
        $img = cm_seed_image('happiness-cover.jpg', 'Happiness book cover');
        if ($img) {
            $p->set_image_id($img);
        }
        $pid = $p->save();
        update_post_meta($pid, 'show_in_hero', '1');
        update_post_meta($pid, 'book_blurb', 'Join Coco Marshmallow on a gentle adventure to discover what happiness really feels like. A joyful picture book that promotes early emotional intelligence, with a fun mindfulness activity inside.');
        update_post_meta($pid, 'book_author', 'Lysha Goode');
        update_post_meta($pid, 'book_illustrator', 'Amy Chandler');
        update_post_meta($pid, 'book_age_range', '3-7 years');
    }
}

// --- Sample activity (placeholder file: replace with a real printable) ---------------
if (!get_posts(['post_type' => 'cm_activity', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any'])) {
    $aid = wp_insert_post([
        'post_type' => 'cm_activity', 'post_status' => 'publish', 'post_title' => 'Meadow friends poster (sample)',
        'post_excerpt' => 'A sample download. Replace this with your own colouring sheet or mindfulness activity in Activities > Add new.',
    ]);
    $file = cm_seed_image('meadow-scene.jpg', 'Meadow scene', $aid);
    update_post_meta($aid, 'activity_file', $file);
    update_post_meta($aid, 'activity_age_range', '3-7 years');
    update_post_meta($aid, 'activity_duration', '10 minutes');
    update_post_meta($aid, '_activity_file', 'field_cm_activity_file'); // ACF field reference
    update_post_meta($aid, '_activity_age_range', 'field_cm_activity_age');
    update_post_meta($aid, '_activity_duration', 'field_cm_activity_time');
}

// --- Sample news post ----------------------------------------------------
if (!get_posts(['post_type' => 'post', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any'])) {
    wp_insert_post(['post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Welcome to Cocomarshmallow Books',
        'post_content' => '<!-- wp:paragraph --><p>Our new home for Coco Marshmallow books and free activities is open. News, signings and storytime events will be posted here.</p><!-- /wp:paragraph -->']);
}
wp_delete_post(1, true); // default "Hello world!"
wp_delete_post(2, true); // default "Sample Page"

// --- Menus ---------------------------------------------------------------
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/books/');
$items = [
    ['Books', $shop_url],
    ['About', get_permalink($about)],
    ['Children’s activities', get_permalink($act)],
    ['News & events', get_permalink($news)],
    ['Contact', get_permalink($contact)],
];
foreach (['Primary' => 'primary', 'Footer' => 'footer'] as $name => $location) {
    $menu = wp_get_nav_menu_object($name);
    if (!$menu) {
        $menu_id = wp_create_nav_menu($name);
        foreach ($items as $i => [$label, $url]) {
            wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-status' => 'publish', 'menu-item-type' => 'custom', 'menu-item-position' => $i + 1]);
        }
        $locations = get_theme_mod('nav_menu_locations', []);
        $locations[$location] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}

flush_rewrite_rules();
WP_CLI::success('Starter content created.');

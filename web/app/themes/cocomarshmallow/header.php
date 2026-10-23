<?php
/** Site header: skip link, banner, primary navigation. */
defined('ABSPATH') || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e('Skip to main content', 'cocomarshmallow'); ?></a>

<header class="site-header" id="top">
    <div class="container container--wide site-header__inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name') . ' – home'); ?>">
            <?php if (has_custom_logo()) : ?>
                <?php echo wp_get_attachment_image(get_theme_mod('custom_logo'), 'thumbnail', false, ['class' => 'brand__logo', 'alt' => '']); ?>
            <?php else : ?>
                <img class="brand__logo" src="<?php echo esc_url(cm_asset('img/coco.jpg')); ?>" alt="" width="56" height="56">
            <?php endif; ?>
            <span class="brand__name"><?php bloginfo('name'); ?></span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
            <span class="nav-toggle__bars" aria-hidden="true"></span>
            <span class="nav-toggle__label"><?php esc_html_e('Menu', 'cocomarshmallow'); ?></span>
        </button>

        <nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e('Primary', 'cocomarshmallow'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'menu',
                'fallback_cb'    => 'cm_default_menu',
                'depth'          => 1,
            ]);
            ?>
            <?php if (function_exists('WC')) { get_template_part('template-parts/cart-link'); } ?>
        </nav>
    </div>
</header>
<?php
/** Used until a menu is assigned in Appearance > Menus. */
function cm_default_menu(): void
{
    $shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/books/');
    $items = [
        [__('Books', 'cocomarshmallow'), $shop],
        [__('About', 'cocomarshmallow'), home_url('/about/')],
        [__('Children’s activities', 'cocomarshmallow'), home_url('/childrens-activities/')],
        [__('News & events', 'cocomarshmallow'), home_url('/news-events/')],
        [__('Contact', 'cocomarshmallow'), home_url('/contact/')],
    ];
    echo '<ul class="menu">';
    foreach ($items as [$label, $url]) {
        printf('<li class="menu-item"><a href="%s">%s</a></li>', esc_url($url), esc_html($label));
    }
    echo '</ul>';
}

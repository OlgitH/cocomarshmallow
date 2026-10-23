<?php defined('ABSPATH') || exit; ?>
<footer class="site-footer">
    <div class="container container--wide site-footer__grid">
        <section class="footer-col footer-col--about" aria-labelledby="footer-about">
            <h2 id="footer-about" class="footer-title"><?php bloginfo('name'); ?></h2>
            <p><?php echo esc_html(get_bloginfo('description') ?: 'Gentle, joyful picture books that help little ones understand their feelings. Written and illustrated in Bath.'); ?></p>
            <?php $socials = cm_social_links(); ?>
            <?php if ($socials) : ?>
                <ul class="social" aria-label="<?php esc_attr_e('Follow us', 'cocomarshmallow'); ?>">
                    <?php foreach ($socials as $s) : ?>
                        <li>
                            <a href="<?php echo esc_url($s['url']); ?>" rel="noopener me" target="_blank">
                                <?php echo cm_icon($s['key']); ?>
                                <span class="screen-reader-text"><?php echo esc_html($s['label']); ?> <?php esc_html_e('(opens in a new tab)', 'cocomarshmallow'); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <nav class="footer-col" aria-labelledby="footer-explore">
            <h2 id="footer-explore" class="footer-title"><?php esc_html_e('Explore', 'cocomarshmallow'); ?></h2>
            <?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-menu', 'fallback_cb' => 'cm_default_menu', 'depth' => 1]); ?>
        </nav>

        <section class="footer-col" aria-labelledby="footer-contact">
            <h2 id="footer-contact" class="footer-title"><?php esc_html_e('Get in touch', 'cocomarshmallow'); ?></h2>
            <address class="footer-address">
                <?php $loc = cm_field('contact_location', 'option') ?: 'Bath, Somerset'; ?>
                <?php $mail = cm_field('contact_email', 'option') ?: (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : ''); ?>
                <span><?php echo esc_html($loc); ?></span><br>
                <?php if ($mail) : ?><a href="mailto:<?php echo esc_attr(antispambot($mail)); ?>"><?php echo esc_html(antispambot($mail)); ?></a><?php endif; ?>
            </address>
            <p><a class="button button--small" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Send a message', 'cocomarshmallow'); ?></a></p>
        </section>
    </div>

    <div class="site-footer__legal">
        <div class="container container--wide site-footer__legal-inner">
            <p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'cocomarshmallow'); ?></p>
            <nav aria-label="<?php esc_attr_e('Legal', 'cocomarshmallow'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'legal',
                    'container'      => false,
                    'menu_class'     => 'legal-menu',
                    'depth'          => 1,
                    'fallback_cb'    => static function () {
                        $privacy = get_privacy_policy_url();
                        echo '<ul class="legal-menu">';
                        if ($privacy) {
                            printf('<li><a href="%s">%s</a></li>', esc_url($privacy), esc_html__('Privacy policy', 'cocomarshmallow'));
                        }
                        echo '</ul>';
                    },
                ]);
                ?>
            </nav>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

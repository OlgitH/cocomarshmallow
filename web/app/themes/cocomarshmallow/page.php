<?php
defined('ABSPATH') || exit;
get_header();
while (have_posts()) : the_post(); ?>
<main id="main" tabindex="-1">
    <article <?php post_class('entry'); ?>>
        <header class="page-hero<?php echo cm_show_banner() ? '' : ' page-hero--pink'; ?>">
            <div class="container container--narrow">
                <?php cm_breadcrumbs(); ?>
                <?php
                $banner_title = cm_show_banner() ? trim((string) get_post_meta(get_the_ID(), 'banner_title', true)) : '';
                ?>
                <h1 class="page-hero__title"><?php echo $banner_title !== '' ? esc_html($banner_title) : get_the_title(); ?></h1>
            </div>
        </header>
        <?php
        $follow = is_page('about')
            ? array_filter(cm_social_links(), static fn ($s) => in_array($s['key'], ['instagram', 'facebook'], true))
            : [];
        ?>
        <?php $is_about = is_page('about'); ?>
        <div class="container container--wide section<?php echo $is_about ? ' has-aside' : ''; ?>">
            <?php
            // About page: pull the photo gallery out of the copy and show it in the right-hand column.
            $photos = '';
            $more = '';
            $about_intro = $is_about ? trim((string) cm_field('about_intro')) : '';
            $render_photos = static function ($rows): string {
                $out = '';
                foreach ((array) $rows as $row) {
                    $id = (int) ($row['image'] ?? 0);
                    if (!$id) {
                        continue;
                    }
                    $caption = trim((string) ($row['caption'] ?? ''));
                    $out .= '<figure class="wp-block-image">' . wp_get_attachment_image($id, 'large')
                        . ($caption !== '' ? '<figcaption class="wp-element-caption">' . esc_html($caption) . '</figcaption>' : '') . '</figure>';
                }
                return $out;
            };
            if ($is_about && $about_intro !== '') {
                // Content comes from the ACF fields (About page content).
                $photos = $render_photos(cm_field('about_photos'));
                $more = $render_photos(cm_field('about_more_photos'));
                $body = apply_filters('the_content', $about_intro);
            } elseif ($is_about) {
                // Fallback: legacy editor content with a gallery block.
                $kept = [];
                foreach (parse_blocks(get_the_content()) as $block) {
                    if (($block['blockName'] ?? '') === 'core/gallery' && str_contains($block['attrs']['className'] ?? '', 'about-photos')) {
                        // Individual portraits go in the right column; the rest in a section below.
                        foreach ($block['innerBlocks'] as $img) {
                            $file = basename((string) get_attached_file((int) ($img['attrs']['id'] ?? 0)));
                            if (str_starts_with($file, 'about-story-time') || str_starts_with($file, 'about-illustration')) {
                                $photos .= render_block($img);
                            } else {
                                $more .= render_block($img);
                            }
                        }
                    } else {
                        $kept[] = $block;
                    }
                }
                $body = apply_filters('the_content', serialize_blocks($kept));
            } else {
                $body = apply_filters('the_content', get_the_content());
            }
            ?>
            <div class="entry__content prose"><?php echo $body; ?></div>
            <?php if ($is_about) : ?>
                <div class="about-side">
                    <div class="about-photos"><?php echo $photos; ?></div>
                    <?php if ($follow) : ?>
                <aside class="follow-us" aria-labelledby="follow-us-heading">
                    <h2 id="follow-us-heading" class="follow-us__title"><?php esc_html_e('Follow us on socials', 'cocomarshmallow'); ?></h2>
                    <ul class="social" role="list">
                        <?php foreach ($follow as $s) : ?>
                            <li>
                                <a href="<?php echo esc_url($s['url']); ?>" rel="noopener me" target="_blank">
                                    <?php echo cm_icon($s['key']); ?>
                                    <span class="screen-reader-text"><?php echo esc_html($s['label']); ?> <?php esc_html_e('(opens in a new tab)', 'cocomarshmallow'); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($more)) : ?>
            <section class="container container--wide section about-more" aria-label="<?php esc_attr_e('More photos', 'cocomarshmallow'); ?>">
                <div class="about-more__grid"><?php echo $more; ?></div>
            </section>
        <?php endif; ?>
    </article>
</main>
<?php endwhile;
get_footer();

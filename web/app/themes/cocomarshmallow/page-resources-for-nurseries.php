<?php
/**
 * Template Name: Resources for nurseries & preschools
 * Editable content comes from the page editor (headings, text, lists);
 * this template adds the hero, a book call-to-action and a download call-to-action.
 */
defined('ABSPATH') || exit;
get_header();
$book = cm_hero_product();
$nf = static fn (string $k, string $d = '') => trim((string) cm_field($k)) !== '' ? trim((string) cm_field($k)) : $d;
?>
<main id="main" tabindex="-1">
    <?php while (have_posts()) : the_post(); ?>
    <header class="page-hero page-hero--pink">
        <div class="container container--wide page-hero__split">
            <div>
                <?php cm_breadcrumbs(); ?>
                <h1 class="page-hero__title"><?php the_title(); ?></h1>
                <?php $lead = $nf('nur_lead', has_excerpt() ? get_the_excerpt() : ''); if ($lead !== '') : ?><p class="page-hero__lead"><?php echo esc_html($lead); ?></p><?php endif; ?>
                <p class="hero__actions">
                    <a class="button" href="<?php echo esc_url($nf('nur_h1_url', home_url('/childrens-activities/'))); ?>"><?php echo esc_html($nf('nur_h1_label', 'Free printable activities')); ?></a>
                    <a class="button button--outline" href="<?php echo esc_url($nf('nur_h2_url', home_url('/contact/'))); ?>"><?php echo esc_html($nf('nur_h2_label', 'Ask about your setting')); ?></a>
                </p>
            </div>
            <?php if ((int) cm_field('nur_image')) : ?>
                <?php echo wp_get_attachment_image((int) cm_field('nur_image'), 'large', false, ['class' => 'page-hero__art']); ?>
            <?php else : ?>
            <img class="page-hero__art" src="<?php echo esc_url(cm_asset('img/coco.jpg')); ?>" width="480" height="480" alt="<?php esc_attr_e('Coco Marshmallow, a friendly cat in a grey cardigan, smiling among colourful leaves', 'cocomarshmallow'); ?>">
            <?php endif; ?>
        </div>
    </header>

    <?php if (!cm_render_sections()) : ?>
    <div class="container container--narrow section">
        <div class="entry__content prose prose--sections"><?php the_content(); ?></div>
    </div>
    <?php endif; ?>
    <?php endwhile; ?>

    <section class="cta-band" aria-labelledby="cta-heading">
        <div class="container container--wide cta-band__inner">
            <?php if ($book) : ?>
                <div class="cta-band__media">
                    <?php echo $book->get_image('cm-card', ['alt' => sprintf(__('Cover of %s', 'cocomarshmallow'), $book->get_name()), 'loading' => 'lazy']); ?>
                </div>
            <?php endif; ?>
            <div class="cta-band__text">
                <h2 id="cta-heading"><?php echo esc_html($nf('nur_cta_heading', 'Bring Coco to your nursery or preschool')); ?></h2>
                <p><?php echo esc_html($nf('nur_cta_text', 'Read a story together, then try the mindfulness activity. Download our free resources or take a look at the books.')); ?></p>
                <p class="hero__actions">
                    <?php if ($book) : ?><a class="button" href="<?php echo esc_url($book->get_permalink()); ?>"><?php echo esc_html(sprintf(__('See %s', 'cocomarshmallow'), $book->get_name())); ?></a><?php endif; ?>
                    <a class="button button--outline" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/books/')); ?>"><?php echo esc_html($nf('nur_cta_all', 'All books')); ?></a>
                </p>
            </div>
        </div>
    </section>
</main>
<?php get_footer();

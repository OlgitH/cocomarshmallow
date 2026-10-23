<?php
/** Template Name: Contact */
defined('ABSPATH') || exit;
get_header();
$status = isset($_GET['cm_sent']) ? 'sent' : (isset($_GET['cm_error']) ? sanitize_key($_GET['cm_error']) : '');
$mail   = cm_field('contact_email', 'option');
$loc    = cm_field('contact_location', 'option') ?: 'Bath, Somerset';
?>
<main id="main" tabindex="-1">
    <?php while (have_posts()) : the_post(); ?>
    <header class="page-hero">
        <div class="container container--narrow">
            <?php cm_breadcrumbs(); ?>
            <h1 class="page-hero__title"><?php the_title(); ?></h1>
        </div>
    </header>
    <div class="container container--wide section contact-layout">
        <div class="prose">
            <?php the_content(); ?>
            <p><strong><?php esc_html_e('Based in', 'cocomarshmallow'); ?></strong> <?php echo esc_html($loc); ?></p>
            <?php if ($mail) : ?><p><a href="mailto:<?php echo esc_attr(antispambot($mail)); ?>"><?php echo esc_html(antispambot($mail)); ?></a></p><?php endif; ?>
        </div>

        <form id="contact-form" class="form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
            <?php if ($status === 'sent') : ?>
                <p class="notice notice--success" role="status"><?php esc_html_e('Thank you! Your message has been sent.', 'cocomarshmallow'); ?></p>
            <?php elseif ($status) : ?>
                <p class="notice notice--error" role="alert">
                    <?php echo esc_html($status === 'invalid' ? __('Please fill in your name, a valid email address and a message.', 'cocomarshmallow') : __('Sorry, something went wrong sending your message. Please try again.', 'cocomarshmallow')); ?>
                </p>
            <?php endif; ?>
            <input type="hidden" name="action" value="cm_contact">
            <?php wp_nonce_field('cm_contact', 'cm_contact_nonce'); ?>
            <div class="form__row">
                <label for="cm_name"><?php esc_html_e('Your name', 'cocomarshmallow'); ?> <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="cm_name" name="cm_name" autocomplete="name" required aria-required="true">
            </div>
            <div class="form__row">
                <label for="cm_email"><?php esc_html_e('Email address', 'cocomarshmallow'); ?> <span class="req" aria-hidden="true">*</span></label>
                <input type="email" id="cm_email" name="cm_email" autocomplete="email" required aria-required="true">
            </div>
            <div class="form__row">
                <label for="cm_message"><?php esc_html_e('Message', 'cocomarshmallow'); ?> <span class="req" aria-hidden="true">*</span></label>
                <textarea id="cm_message" name="cm_message" rows="6" required aria-required="true"></textarea>
            </div>
            <div class="form__hp" aria-hidden="true">
                <label for="cm_website">Leave this field empty</label>
                <input type="text" id="cm_website" name="cm_website" tabindex="-1" autocomplete="off">
            </div>
            <button class="button" type="submit"><?php esc_html_e('Send message', 'cocomarshmallow'); ?></button>
        </form>
    </div>
    <?php endwhile; ?>
</main>
<?php get_footer();

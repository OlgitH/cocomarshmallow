<?php
/**
 * Adds the About page photo gallery. Idempotent. Run with:
 *   docker-compose exec wordpress wp eval-file scripts/seed-about-photos.php
 */
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

$about = get_page_by_path('about');
$items = [
    ['about-happiness-launch.jpg', 'Lysha and Amy with whisker face paint, holding a copy of Happiness', 'The Adventures of Coco Marshmallow: Happiness, with a pink top hat and whiskers to match.'],
    ['about-story-time.jpg', 'Smiling in fairy wings and bunny ears with a Coco Marshmallow book and soft toy', 'Lysha'],
    ['about-illustration.jpg', 'Smiling and holding a framed illustration of a badger in a hat', 'Amy'],
    ['about-kitten.jpg', 'A sleepy ragdoll kitten stretched out on a lap', 'The real-life inspiration for Coco.'],
];
$blocks = '';
$figs = '';
foreach ($items as [$file, $alt, $cap]) {
    $id = cm_seed_image($file, pathinfo($file, PATHINFO_FILENAME));
    if (!$id) { continue; }
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    $url = esc_url(wp_get_attachment_image_url($id, 'large'));
    $img = '<!-- wp:image {"id":' . $id . ',"sizeSlug":"large","linkDestination":"none"} -->'
        . '<figure class="wp-block-image size-large"><img src="' . $url . '" alt="' . esc_attr($alt) . '" class="wp-image-' . $id . '"/><figcaption class="wp-element-caption">' . esc_html($cap) . '</figcaption></figure>'
        . '<!-- /wp:image -->';
    $figs .= $img . "\n";
}
$gallery = '<!-- wp:gallery {"columns":2,"linkTo":"none","className":"about-photos"} -->'
    . '<figure class="wp-block-gallery has-nested-images columns-2 is-cropped about-photos">' . $figs . '</figure>'
    . '<!-- /wp:gallery -->';

$content = $about->post_content;
$content = preg_replace('#\s*<!-- wp:gallery.*?<!-- /wp:gallery -->#s', '', $content);
wp_update_post(['ID' => $about->ID, 'post_content' => trim($content) . "\n" . $gallery]);
echo "About page updated\n";

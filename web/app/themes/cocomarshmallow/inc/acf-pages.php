<?php
/**
 * ACF fields for page content: a flexible "Page sections" builder shared by pages,
 * plus fixed fields for the homepage and the three custom templates.
 */
defined('ABSPATH') || exit;

add_action('acf/init', static function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $btn = static fn (string $p, string $label) => [
        ['key' => "field_cm_{$p}_label", 'label' => "$label: text", 'name' => "{$p}_label", 'type' => 'text', 'wrapper' => ['width' => '40']],
        ['key' => "field_cm_{$p}_url", 'label' => "$label: link", 'name' => "{$p}_url", 'type' => 'url', 'wrapper' => ['width' => '60']],
    ];

    // Pages that get the section builder: all pages except About (own fields) and the shop pages.
    $excluded = [];
    foreach (['about'] as $slug) {
        if ($p = get_page_by_path($slug)) {
            $excluded[] = (int) $p->ID;
        }
    }
    if (function_exists('wc_get_page_id')) {
        foreach (['shop', 'cart', 'checkout', 'myaccount'] as $w) {
            if (($id = (int) wc_get_page_id($w)) > 0) {
                $excluded[] = $id;
            }
        }
    }
    $location = [];
    $rule = [['param' => 'post_type', 'operator' => '==', 'value' => 'page']];
    foreach (array_unique($excluded) as $id) {
        $rule[] = ['param' => 'page', 'operator' => '!=', 'value' => (string) $id];
    }
    $location[] = $rule;

    acf_add_local_field_group([
        'key'      => 'group_cm_sections',
        'title'    => 'Page sections',
        'location' => $location,
        'position' => 'normal',
        'fields'   => [[
            'key' => 'field_cm_sections', 'label' => 'Sections', 'name' => 'page_sections', 'type' => 'flexible_content', 'button_label' => 'Add section',
            'instructions' => 'Build the page from sections. Drag to reorder. On special pages these appear below the page’s fixed content.',
            'layouts' => [
                'layout_cm_text' => ['key' => 'layout_cm_text', 'name' => 'text', 'label' => 'Text', 'display' => 'block', 'sub_fields' => [
                    ['key' => 'field_cm_text_heading', 'label' => 'Heading (optional)', 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_cm_text_body', 'label' => 'Text', 'name' => 'body', 'type' => 'wysiwyg', 'media_upload' => 0],
                ]],
                'layout_cm_image_text' => ['key' => 'layout_cm_image_text', 'name' => 'image_text', 'label' => 'Image and text', 'display' => 'block', 'sub_fields' => array_merge([
                    ['key' => 'field_cm_it_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'wrapper' => ['width' => '50']],
                    ['key' => 'field_cm_it_side', 'label' => 'Image position', 'name' => 'side', 'type' => 'select', 'choices' => ['right' => 'Right', 'left' => 'Left'], 'default_value' => 'right', 'wrapper' => ['width' => '50']],
                    ['key' => 'field_cm_it_heading', 'label' => 'Heading (optional)', 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_cm_it_body', 'label' => 'Text', 'name' => 'body', 'type' => 'wysiwyg', 'media_upload' => 0],
                ], $btn('it_btn', 'Button'))],
                'layout_cm_gallery' => ['key' => 'layout_cm_gallery', 'name' => 'gallery', 'label' => 'Photo gallery', 'display' => 'block', 'sub_fields' => [
                    ['key' => 'field_cm_gal_heading', 'label' => 'Heading (optional)', 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_cm_gal_images', 'label' => 'Photos', 'name' => 'images', 'type' => 'gallery', 'return_format' => 'id', 'preview_size' => 'medium'],
                    ['key' => 'field_cm_gal_cols', 'label' => 'Columns', 'name' => 'columns', 'type' => 'select', 'choices' => ['2' => '2', '3' => '3', '4' => '4'], 'default_value' => '3'],
                ]],
                'layout_cm_cta' => ['key' => 'layout_cm_cta', 'name' => 'cta', 'label' => 'Call to action', 'display' => 'block', 'sub_fields' => array_merge([
                    ['key' => 'field_cm_cta_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_cm_cta_text', 'label' => 'Text', 'name' => 'text', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
                ], $btn('cta_b1', 'Button 1'), $btn('cta_b2', 'Button 2 (optional)'))],
            ],
        ]],
    ]);

    // Homepage
    acf_add_local_field_group([
        'key'      => 'group_cm_home',
        'title'    => 'Homepage text',
        'location' => [[['param' => 'page_type', 'operator' => '==', 'value' => 'front_page']]],
        'position' => 'acf_after_title',
        'fields'   => [
            ['key' => 'field_cm_home_kicker', 'label' => 'Hero tag line', 'name' => 'home_kicker', 'type' => 'text', 'default_value' => 'Children’s author in Bath'],
            ['key' => 'field_cm_home_latest', 'label' => 'Hero label above the book title', 'name' => 'home_latest_label', 'type' => 'text', 'default_value' => 'Latest book'],
            ['key' => 'field_cm_home_feat_h', 'label' => 'Featured books heading', 'name' => 'home_featured_heading', 'type' => 'text', 'default_value' => 'Featured books'],
            ['key' => 'field_cm_home_feat_l', 'label' => 'Featured books link text', 'name' => 'home_featured_link', 'type' => 'text', 'default_value' => 'See all books'],
            ['key' => 'field_cm_home_rev_h', 'label' => 'Reviews heading', 'name' => 'home_reviews_heading', 'type' => 'text', 'default_value' => 'What our readers say'],
        ],
    ]);

    // Children's activities
    acf_add_local_field_group([
        'key'      => 'group_cm_activities_page',
        'title'    => 'Children’s activities page',
        'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-childrens-activities.php']]],
        'position' => 'acf_after_title',
        'hide_on_screen' => ['the_content'],
        'fields'   => [
            ['key' => 'field_cm_act_intro', 'label' => 'Intro text', 'name' => 'act_intro', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0],
            ['key' => 'field_cm_act_image', 'label' => 'Header illustration', 'name' => 'act_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Optional. Leave empty for the default meadow scene.'],
            ['key' => 'field_cm_act_heading', 'label' => 'Downloads heading', 'name' => 'act_heading', 'type' => 'text', 'default_value' => 'Free downloads'],
            ['key' => 'field_cm_act_empty', 'label' => 'Message when there are no activities', 'name' => 'act_empty', 'type' => 'text', 'default_value' => 'Activities are coming soon. Check back shortly!'],
        ],
    ]);

    // Contact
    acf_add_local_field_group([
        'key'      => 'group_cm_contact_page',
        'title'    => 'Contact page',
        'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-contact.php']]],
        'position' => 'acf_after_title',
        'hide_on_screen' => ['the_content'],
        'fields'   => [
            ['key' => 'field_cm_contact_intro', 'label' => 'Intro text', 'name' => 'contact_intro', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0,
             'instructions' => 'Email address and location come from Site settings.'],
            ['key' => 'field_cm_contact_thanks', 'label' => 'Message after sending', 'name' => 'contact_thanks', 'type' => 'text', 'default_value' => 'Thank you! Your message has been sent.'],
        ],
    ]);

    // Nurseries
    acf_add_local_field_group([
        'key'      => 'group_cm_nurseries_page',
        'title'    => 'Nurseries page header and call to action',
        'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-resources-for-nurseries.php']]],
        'position' => 'acf_after_title',
        'hide_on_screen' => ['the_content'],
        'fields'   => array_merge([
            ['key' => 'field_cm_nur_lead', 'label' => 'Header intro', 'name' => 'nur_lead', 'type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
            ['key' => 'field_cm_nur_image', 'label' => 'Header illustration', 'name' => 'nur_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Optional. Leave empty for the default Coco picture.'],
        ], $btn('nur_h1', 'Header button 1'), $btn('nur_h2', 'Header button 2'), [
            ['key' => 'field_cm_nur_cta_h', 'label' => 'Bottom band heading', 'name' => 'nur_cta_heading', 'type' => 'text', 'default_value' => 'Bring Coco to your nursery or preschool'],
            ['key' => 'field_cm_nur_cta_t', 'label' => 'Bottom band text', 'name' => 'nur_cta_text', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '', 'default_value' => 'Read a story together, then try the mindfulness activity. Download our free resources or take a look at the books.'],
            ['key' => 'field_cm_nur_cta_all', 'label' => '“All books” button text', 'name' => 'nur_cta_all', 'type' => 'text', 'default_value' => 'All books'],
        ]),
    ]);
});

/** Render the flexible "Page sections" for a page. Returns true if anything was output. */
function cm_render_sections($post_id = null): bool
{
    $rows = cm_field('page_sections', $post_id);
    if (!$rows || !is_array($rows)) {
        return false;
    }
    $button = static function (array $r, string $p, string $class = 'button'): string {
        $label = trim((string) ($r["{$p}_label"] ?? ''));
        $url   = trim((string) ($r["{$p}_url"] ?? ''));
        return ($label !== '' && $url !== '') ? '<a class="' . esc_attr($class) . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a> ' : '';
    };
    foreach ($rows as $r) {
        $layout  = $r['acf_fc_layout'] ?? '';
        $heading = trim((string) ($r['heading'] ?? ''));
        $h       = $heading !== '' ? '<h2 class="flex-section__title">' . esc_html($heading) . '</h2>' : '';
        switch ($layout) {
            case 'text':
                echo '<section class="flex-section container container--narrow section"><div class="prose prose--sections">' . $h . wp_kses_post(apply_filters('the_content', $r['body'] ?? '')) . '</div></section>';
                break;
            case 'image_text':
                $img = (int) ($r['image'] ?? 0);
                echo '<section class="flex-section container container--wide section"><div class="flex-section__split flex-section__split--' . esc_attr($r['side'] ?? 'right') . '">';
                echo '<div class="prose">' . $h . wp_kses_post(apply_filters('the_content', $r['body'] ?? ''));
                $b = $button($r, 'it_btn');
                echo $b ? '<p>' . $b . '</p>' : '';
                echo '</div>';
                echo $img ? '<div class="flex-section__media">' . wp_get_attachment_image($img, 'large') . '</div>' : '';
                echo '</div></section>';
                break;
            case 'gallery':
                $ids = array_filter((array) ($r['images'] ?? []));
                if (!$ids) {
                    break;
                }
                echo '<section class="flex-section container container--wide section">' . $h . '<ul class="flex-gallery flex-gallery--' . (int) ($r['columns'] ?? 3) . '" role="list">';
                foreach ($ids as $id) {
                    $cap = wp_get_attachment_caption((int) $id);
                    echo '<li><figure>' . wp_get_attachment_image((int) $id, 'large') . ($cap ? '<figcaption>' . esc_html($cap) . '</figcaption>' : '') . '</figure></li>';
                }
                echo '</ul></section>';
                break;
            case 'cta':
                echo '<section class="cta-band cta-band--simple"><div class="container container--narrow text-center">' . ($heading !== '' ? '<h2>' . esc_html($heading) . '</h2>' : '');
                echo !empty($r['text']) ? '<p>' . esc_html($r['text']) . '</p>' : '';
                echo '<p class="hero__actions">' . $button($r, 'cta_b1') . $button($r, 'cta_b2', 'button button--outline') . '</p></div></section>';
                break;
        }
    }
    return true;
}

<?php
/**
 * Creates the "Resources for nurseries" SEO page (idempotent).
 * Run: wp eval-file scripts/seed-resources.php
 */
$slug = 'resources-for-nurseries';
$content = <<<'HTML'
<!-- wp:heading --><h2>Why Coco Marshmallow books are great for nursery and preschool children</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The Adventures of Coco Marshmallow are gentle, funny picture books made for the youngest readers. Each story follows Coco the cat through one big feeling at a time, with short passages, warm illustrations and plenty to look at and talk about, which makes them ideal for nursery and preschool story time.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul>
<li><strong>One feeling per book</strong>, so children can focus on a single idea.</li>
<li><strong>Read-aloud rhythm</strong> that works for circle time, quiet corners and one-to-one reading.</li>
<li><strong>Illustrations full of detail</strong> that invite children to point, spot and describe what they see.</li>
<li><strong>A fun mindfulness activity inside</strong>, ready to try straight after the story.</li>
</ul><!-- /wp:list -->

<!-- wp:heading --><h2>Promotes early emotional intelligence</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Young children feel emotions long before they have the words for them. Stories give them a safe way to meet those feelings from a distance: watching Coco feel happy, nervous or cross helps children recognise the same feelings in themselves and in their friends.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Used in a nursery or preschool, the books can help practitioners to:</p><!-- /wp:paragraph -->
<!-- wp:list --><ul>
<li>build a vocabulary for feelings ("Coco looks excited. How can you tell?")</li>
<li>start gentle conversations about how everyone feels</li>
<li>encourage empathy and kindness between children</li>
<li>support personal, social and emotional development in the early years</li>
</ul><!-- /wp:list -->

<!-- wp:heading --><h2>Why mindfulness is important for kids</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Mindfulness, simply noticing what is happening right now, gives small children simple tools for big moments. Paying attention to their breathing, their bodies and the world around them can help children settle, focus and feel calmer when feelings run high.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Every Coco Marshmallow book includes a short, playful mindfulness activity designed for little ones, with no special equipment needed. They fit easily into a busy session: before a story, after outdoor play or at the end of the day.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Positive parenting, at home and in your setting</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Positive parenting is about connection: listening, naming feelings and showing children that all of their emotions are welcome. The Coco Marshmallow books give parents, carers and practitioners a shared starting point for exactly those conversations.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul>
<li>Share a book at home after reading it at nursery, so the same words and ideas carry across.</li>
<li>Send the free printable activities home as a way to keep families involved.</li>
<li>Use Coco as a friendly reference ("Shall we do Coco's breathing?") when a child needs a calming moment.</li>
</ul><!-- /wp:list -->

<!-- wp:heading --><h2>Free resources for nurseries and preschools</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We have free printable activities, including colouring sheets and mindfulness cards, that you can download and use in your setting. <a href="/childrens-activities/">Browse the children's activities</a>.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2>Frequently asked questions</h2><!-- /wp:heading -->
<!-- wp:html --><details><summary>What age are the books for?</summary><p>They are written for the early years and early primary years, and work especially well as read-aloud stories for nursery and preschool children.</p></details>
<details><summary>Are the activities free to use in my setting?</summary><p>Yes. The printable activities on our children's activities page are free to download and print for use with your children.</p></details>
<details><summary>Can I order books for my nursery or preschool?</summary><p>Yes, you can buy any book from our <a href="/books/">Books page</a>. If you would like to ask about ordering for a whole setting, please <a href="/contact/">get in touch</a>.</p></details><!-- /wp:html -->
HTML;

$page = get_page_by_path($slug);
$args = [
    'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug,
    'post_title' => 'Resources for nurseries and preschools',
    'post_excerpt' => 'Picture books, mindfulness activities and free printables that help nursery and preschool children understand their feelings, with ideas to share with parents and carers.',
    'post_content' => $content,
];
$id = $page ? wp_update_post($args + ['ID' => $page->ID]) : wp_insert_post($args);
update_post_meta($id, '_wp_page_template', 'page-resources-for-nurseries.php');

// Rank Math: primary keyword first, then secondary keywords
update_post_meta($id, 'rank_math_focus_keyword', 'resources for nurseries,resources for preschools,early emotional intelligence,mindfulness for kids,positive parenting');
update_post_meta($id, 'rank_math_title', 'Resources for Nurseries & Preschools | Cocomarshmallow Books');
update_post_meta($id, 'rank_math_description', 'Resources for nurseries and preschools: picture books that promote early emotional intelligence, free mindfulness activities for kids and positive parenting ideas.');

// Footer menu link (once)
$footer = wp_get_nav_menu_object('Footer');
if ($footer) {
    $has = false;
    foreach ((array) wp_get_nav_menu_items($footer->term_id) as $item) {
        if ((int) $item->object_id === $id || $item->url === get_permalink($id)) { $has = true; }
    }
    if (!$has) {
        wp_update_nav_menu_item($footer->term_id, 0, ['menu-item-title' => 'Resources for nurseries', 'menu-item-url' => get_permalink($id), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom', 'menu-item-position' => 10]);
    }
}
WP_CLI::success('Resources page ready: ' . get_permalink($id));

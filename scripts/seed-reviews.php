<?php
/** Seeds reader reviews (idempotent: skips any reviewer+date already present). Run: wp eval-file scripts/seed-reviews.php */
$reviews = [
    ['Jacqui',   '2026-01-11', 'Just wonderful!! My grandchildren love it 🩵🩷', ''],
    ['Chris',    '2026-01-03', 'A beautiful little book with a lovely message! My grandson loved it!', ''],
    ['Claudia',  '2025-12-13', 'Gorgeous children’s book with stunning illustrations - thoughtful narrative with cute characters. Excellent source for teaching emotional intelligence. Perfect as a gift too!', 'Thank you for your review. We are so glad you are enjoying our book! :)'],
    ['Jo',       '2025-12-11', 'Beautiful book! A great Christmas present! Thank you', 'Thank you for taking the time to review, so pleased you like our book :)'],
    ['Michelle', '2025-12-10', 'A beautiful book with a sweet poetic story line. The illustrations really capture the characters. Having a mindful practise included was a lovely thing to do with my son. I’ve purchased 2 others for gifts this Christmas! Coco marshmallow is a hit in our house!', 'Thank you for your kind review :) Glad Coco is a hit in your house!'],
    ['Kevin',    '2025-12-08', 'Wonderfully illustrated and well written children’s book', 'Thanks so much for taking the time to post a review, it means a lot to us'],
    ['Michael',  '2025-12-07', 'Lovely, touching story with charming illustrations.', 'Thank you so much for the lovely feedback x'],
];
$added = 0;
foreach ($reviews as [$name, $date, $text, $reply]) {
    $exists = get_posts(['post_type' => 'cm_review', 'title' => $name, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'date_query' => [['year' => (int) substr($date, 0, 4), 'month' => (int) substr($date, 5, 2), 'day' => (int) substr($date, 8, 2)]]]);
    if ($exists) {
        if (!get_post_meta($exists[0], 'review_rating', true)) {
            update_post_meta($exists[0], 'review_rating', 5);
            update_post_meta($exists[0], '_review_rating', 'field_cm_review_rating');
        }
        continue;
    }
    $id = wp_insert_post([
        'post_type' => 'cm_review', 'post_status' => 'publish', 'post_title' => $name, 'post_content' => $text,
        'post_date' => $date . ' 12:00:00', 'post_date_gmt' => get_gmt_from_date($date . ' 12:00:00'),
    ]);
    update_post_meta($id, 'review_rating', 5);
    update_post_meta($id, '_review_rating', 'field_cm_review_rating');
    update_post_meta($id, 'review_source', 'Etsy');
    update_post_meta($id, '_review_source', 'field_cm_review_source');
    if ($reply) {
        update_post_meta($id, 'review_reply', $reply);
        update_post_meta($id, '_review_reply', 'field_cm_review_reply');
        update_post_meta($id, 'review_reply_by', 'Lysha and Amy');
        update_post_meta($id, '_review_reply_by', 'field_cm_review_reply_by');
    }
    $added++;
}
WP_CLI::success("Reviews added: $added");

<?php $uid = wp_unique_id('search-'); ?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label for="<?php echo esc_attr($uid); ?>"><?php esc_html_e('Search', 'cocomarshmallow'); ?></label>
    <input type="search" id="<?php echo esc_attr($uid); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>" autocomplete="off">
    <button type="submit" class="button button--small"><?php esc_html_e('Search', 'cocomarshmallow'); ?></button>
</form>

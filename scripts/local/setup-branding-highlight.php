<?php
/** Switch only the existing homepage headline emphasis to its dedicated preset. */
if (wp_get_environment_type() !== 'local') throw new RuntimeException('Run this migration on Local only.');
$pages = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'numberposts'=>-1,
    'meta_query'=>[['key'=>'_bvs_seed_key', 'value'=>['lv-home','en-home'], 'compare'=>'IN']]]);
$changed = 0;
foreach ($pages as $page) {
    $content = str_replace('color:var(--wp--preset--color--accent-light)" class="has-inline-color">',
        'color:var(--wp--preset--color--branding-highlight)" class="has-inline-color">', $page->post_content);
    if ($content === $page->post_content) continue;
    $result = wp_update_post(['ID'=>$page->ID, 'post_content'=>wp_slash($content)], true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    $changed++;
}
WP_CLI::success("Updated $changed homepage headline highlights.");

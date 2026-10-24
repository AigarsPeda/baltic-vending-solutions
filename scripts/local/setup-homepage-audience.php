<?php
/** One-time audience layout update; later WordPress edits are preserved. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Run on the BVS Local site only.');
require_once __DIR__.'/homepage-content.php';
foreach (['lv', 'en'] as $lang) {
    $pages = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'meta_key'=>'_bvs_seed_key', 'meta_value'=>$lang.'-home', 'numberposts'=>1]);
    if (!$pages) throw new RuntimeException('Missing homepage: '.$lang);
    $page = $pages[0];
    if (get_post_meta($page->ID, '_bvs_audience_refinement', true)) continue;
    $blocks = parse_blocks($page->post_content);
    $found = false;
    foreach ($blocks as &$block) {
        if (($block['attrs']['anchor'] ?? '') !== 'who-its-for') continue;
        $block = parse_blocks(bvs_home_audience($lang))[0];
        $found = true;
    }
    unset($block);
    if (!$found) throw new RuntimeException('Missing audience section: '.$lang);
    $content = serialize_blocks($blocks);
    if ($content !== $page->post_content) {
        $result = wp_update_post(wp_slash(['ID'=>$page->ID, 'post_content'=>$content]), true);
        if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
        WP_CLI::log('Updated native audience blocks: '.$lang);
    }
    update_post_meta($page->ID, '_bvs_audience_refinement', 1);
}
WP_CLI::success('Audience refinement complete. Further edits belong in WordPress.');

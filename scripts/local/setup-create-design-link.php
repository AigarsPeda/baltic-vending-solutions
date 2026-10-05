<?php
/** Add an editable editor shortcut beside homepage and product form upload fields. */
if (!defined('WP_CLI') || !WP_CLI || get_stylesheet() !== 'baltic-vending-solutions') throw new RuntimeException('Run with WP-CLI on the BVS site.');
function bvs_create_design_link(&$blocks, $lang, $url) {
    $found = 0;
    foreach ($blocks as &$block) {
        if ($block['blockName'] === 'bvs/quote-form' && ($block['attrs']['formId'] ?? '') === 'quote-' . $lang) {
            $block['attrs']['createDesignUrl'] ??= $url;
            $block['attrs']['createDesignLabel'] ??= $lang === 'lv' ? 'Izveidot dizainu' : 'Create design';
            $found++;
        }
        $found += bvs_create_design_link($block['innerBlocks'], $lang, $url);
    }
    return $found;
}
$updates = [];
foreach (['lv', 'en'] as $lang) foreach (['home', 'compact', 'fridge'] as $key) {
    $pages = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-' . $key, 'numberposts' => 1]);
    $url = bvs_design_page_url($lang);
    if (!$pages || !$url) throw new RuntimeException('Missing page or editor for ' . $lang . '-' . $key);
    $page = $pages[0];
    $blocks = parse_blocks($page->post_content);
    if (bvs_create_design_link($blocks, $lang, $url) !== 1) throw new RuntimeException('Expected one quote form for ' . $lang . '-' . $key);
    $content = serialize_blocks($blocks);
    if ($content !== $page->post_content) $updates[$page->ID] = $content;
}
foreach ($updates as $id => $content) {
    $result = wp_update_post(wp_slash(['ID' => $id, 'post_content' => $content]), true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
}
WP_CLI::success(count($updates) . ' pages updated with Create design shortcuts beside form upload fields.');

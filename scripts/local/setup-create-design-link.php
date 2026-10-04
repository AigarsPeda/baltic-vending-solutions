<?php
/** Add an editable editor shortcut beside the homepage form's upload field. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
function bvs_home_create_design_link(&$blocks, $lang, $url) {
    $found = 0;
    foreach ($blocks as &$block) {
        if ($block['blockName'] === 'bvs/quote-form' && ($block['attrs']['formId'] ?? '') === 'quote-' . $lang) {
            $block['attrs']['createDesignUrl'] ??= $url;
            $block['attrs']['createDesignLabel'] ??= $lang === 'lv' ? 'Izveidot dizainu' : 'Create design';
            $found++;
        }
        $found += bvs_home_create_design_link($block['innerBlocks'], $lang, $url);
    }
    return $found;
}
foreach (['lv', 'en'] as $lang) {
    $pages = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-home', 'numberposts' => 1]);
    $url = bvs_design_page_url($lang);
    if (!$pages || !$url) throw new RuntimeException('Missing homepage or editor for ' . $lang);
    $page = $pages[0];
    $blocks = parse_blocks($page->post_content);
    if (bvs_home_create_design_link($blocks, $lang, $url) !== 1) throw new RuntimeException('Expected one homepage quote form for ' . $lang);
    $content = serialize_blocks($blocks);
    if ($content !== $page->post_content) {
        $result = wp_update_post(wp_slash(['ID' => $page->ID, 'post_content' => $content]), true);
        if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    }
}
WP_CLI::success('Create design shortcuts added beside both homepage form upload fields.');

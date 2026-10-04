<?php
/** Replace only the homepage hero image with the existing Smart Fridge photo. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
$images = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'meta_query' => [
    ['key' => '_bvs_source', 'value' => 'fridge'],
    ['key' => '_bvs_variant', 'value' => 'unbranded'],
]]);
if (!$images) throw new RuntimeException('Missing Smart Fridge Media Library image.');
$image_id = $images[0]->ID;
$image_url = wp_get_attachment_image_url($image_id, 'large');
function bvs_home_hero_image(&$blocks, $id, $url) {
    $changed = 0;
    foreach ($blocks as &$block) {
        if ($block['blockName'] === 'core/image' && in_array('bvs-hero-image', explode(' ', $block['attrs']['className'] ?? ''), true)) {
            $block['attrs']['id'] = $id;
            $image = new WP_HTML_Tag_Processor($block['innerHTML']);
            if (!$image->next_tag('img')) throw new RuntimeException('Missing hero image markup.');
            $image->set_attribute('src', $url);
            $image->set_attribute('alt', 'Smart Fridge');
            $image->set_attribute('class', preg_replace('/\bwp-image-\d+\b/', 'wp-image-' . $id, $image->get_attribute('class')));
            $block['innerHTML'] = $image->get_updated_html();
            $block['innerContent'] = [$block['innerHTML']];
            $changed++;
        }
        $changed += bvs_home_hero_image($block['innerBlocks'], $id, $url);
    }
    return $changed;
}
foreach (['lv', 'en'] as $lang) {
    $pages = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-home', 'numberposts' => 1]);
    if (!$pages) throw new RuntimeException('Missing homepage for ' . $lang);
    $page = $pages[0];
    $blocks = parse_blocks($page->post_content);
    $changed = 0;
    foreach ($blocks as &$block) {
        if (in_array('bvs-hero', explode(' ', $block['attrs']['className'] ?? ''), true)) {
            $changed += bvs_home_hero_image($block['innerBlocks'], $image_id, $image_url);
        }
    }
    unset($block);
    if ($changed !== 1) throw new RuntimeException('Expected one homepage hero image for ' . $lang);
    $content = serialize_blocks($blocks);
    if ($content !== $page->post_content) {
        $result = wp_update_post(wp_slash(['ID' => $page->ID, 'post_content' => $content]), true);
        if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    }
}
WP_CLI::success('Both homepage hero images now use the existing Smart Fridge photo.');

<?php
/** Publish only the four reviewed native product-page bodies, preserving revisions. */
if (!defined('WP_CLI') || !WP_CLI) throw new RuntimeException('WP-CLI only.');
if (count($args) !== 2 || !in_array($args[1], ['check', 'apply'], true)) {
    throw new RuntimeException('Usage: wp eval-file update-products.php payload.json check|apply');
}
$payload = json_decode(file_get_contents($args[0]), true, 512, JSON_THROW_ON_ERROR);
$keys = ['lv-compact', 'lv-fridge', 'en-compact', 'en-fridge'];
if (home_url() !== $payload['before']['home'] || home_url() !== $payload['after']['home']
    || array_keys($payload['before']['pages']) !== $keys || array_keys($payload['after']['pages']) !== $keys) {
    throw new RuntimeException('Unexpected site or product-page set.');
}
$checkBlocks = function ($blocks) use (&$checkBlocks) {
    foreach ($blocks as $block) {
        if ($block['blockName'] === 'core/html' || (!$block['blockName'] && trim($block['innerHTML']))) {
            throw new RuntimeException('Only native editor blocks are allowed.');
        }
        $checkBlocks($block['innerBlocks']);
    }
};
$changes = [];
foreach ($keys as $key) {
    $before = $payload['before']['pages'][$key]; $after = $payload['after']['pages'][$key];
    $page = get_post($before['id']);
    if (!$page || $page->post_type !== 'page' || $page->post_status !== 'publish'
        || get_post_meta($page->ID, '_bvs_seed_key', true) !== $key || $after['id'] !== $page->ID
        || $page->post_content !== $before['content'] || $page->post_title !== $before['title']
        || $after['title'] !== $before['title']) {
        throw new RuntimeException('Product page changed since review: '.$key);
    }
    $checkBlocks(parse_blocks($after['content']));
    $changes[] = ['ID'=>$page->ID, 'post_content'=>$after['content']];
}
WP_CLI::log('Preflight passed: four product pages match the reviewed baseline.');
if ($args[1] === 'check') return;
foreach ($changes as $change) {
    $result = wp_update_post(wp_slash($change), true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    if (get_post_field('post_content', $change['ID']) !== $change['post_content']) throw new RuntimeException('Content verification failed.');
}
WP_CLI::success('Updated four native product pages. Other content and settings preserved.');

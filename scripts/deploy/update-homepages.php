<?php
/** WP-CLI: selectively publish two reviewed homepages and existing menu order/classes. */
if (!defined('WP_CLI') || !WP_CLI) throw new RuntimeException('WP-CLI only.');
if (count($args) !== 2 || !in_array($args[1], ['check', 'apply'], true)) {
    throw new RuntimeException('Usage: wp eval-file update-homepages.php payload.json check|apply');
}
$payload = json_decode(file_get_contents($args[0]), true, 512, JSON_THROW_ON_ERROR);
if (home_url() !== $payload['before']['home'] || home_url() !== $payload['after']['home']) {
    throw new RuntimeException('Payload belongs to another site.');
}
$pageChanges = []; $menuChanges = [];
foreach (['lv', 'en'] as $lang) {
    $before = $payload['before']['pages'][$lang];
    $after = $payload['after']['pages'][$lang];
    $page = get_post($before['id']);
    if (!$page || $page->post_type !== 'page' || $page->post_status !== 'publish'
        || get_post_meta($page->ID, '_bvs_seed_key', true) !== $lang.'-home'
        || $after['id'] !== $page->ID || $page->post_content !== $before['content']) {
        throw new RuntimeException('Homepage changed since review: '.$lang);
    }
    $pageChanges[] = ['ID'=>$page->ID, 'post_content'=>$after['content']];
    $menu = wp_get_nav_menu_object('Primary '.strtoupper($lang));
    $current = [];
    foreach (wp_get_nav_menu_items($menu->term_id) as $item) {
        $current[] = ['id'=>$item->ID, 'title'=>$item->title, 'url'=>$item->url, 'order'=>$item->menu_order, 'classes'=>$item->classes];
    }
    if ($current !== $payload['before']['menus'][$lang]) throw new RuntimeException('Menu changed since review: '.$lang);
    $byId = array_column($current, null, 'id');
    $next = $payload['after']['menus'][$lang];
    $ids = array_column($next, 'id'); $oldIds = array_keys($byId);
    sort($ids); sort($oldIds);
    if ($ids !== $oldIds) throw new RuntimeException('Menu item membership must stay unchanged.');
    foreach ($next as $item) {
        if ($item['title'] !== $byId[$item['id']]['title'] || $item['url'] !== $byId[$item['id']]['url']) {
            throw new RuntimeException('Only menu order and classes may change.');
        }
        if ($item['order'] !== $byId[$item['id']]['order'] || $item['classes'] !== $byId[$item['id']]['classes']) {
            $menuChanges[] = $item;
        }
    }
}
WP_CLI::log('Preflight passed: two homepages and existing menu records match the reviewed snapshot.');
if ($args[1] === 'check') return;
foreach ($pageChanges as $change) {
    $result = wp_update_post(wp_slash($change), true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
}
foreach ($menuChanges as $item) {
    $result = wp_update_post(['ID'=>$item['id'], 'menu_order'=>$item['order']], true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    update_post_meta($item['id'], '_menu_item_classes', $item['classes']);
}
foreach ($pageChanges as $change) {
    if (get_post_field('post_content', $change['ID']) !== $change['post_content']) throw new RuntimeException('Content verification failed.');
}
WP_CLI::success('Published two native homepages and menu order/classes. Other content and settings were preserved.');

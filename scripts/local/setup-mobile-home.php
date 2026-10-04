<?php
/** Add a native, mobile-only homepage destination to each language menu. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
foreach (['lv', 'en'] as $lang) {
    $homes = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-home', 'numberposts' => 1]);
    $menu = wp_get_nav_menu_object('Primary ' . strtoupper($lang));
    if (!$homes || !$menu) throw new RuntimeException('Missing homepage or menu for ' . $lang);
    $items = wp_get_nav_menu_items($menu->term_id);
    $home_item = null;
    foreach ($items as $item) {
        if ($item->object === 'page' && (int) $item->object_id === $homes[0]->ID) {
            $home_item = $item;
            break;
        }
    }
    $position = 2;
    foreach ($items as $item) {
        if ($home_item && $item->ID === $home_item->ID) continue;
        wp_update_post(['ID' => $item->ID, 'menu_order' => $position++]);
    }
    $classes = array_filter($home_item ? $home_item->classes : []);
    $classes[] = 'bvs-mobile-home';
    $result = wp_update_nav_menu_item($menu->term_id, $home_item ? $home_item->ID : 0, [
        'menu-item-object-id' => $homes[0]->ID,
        'menu-item-object' => 'page',
        'menu-item-type' => 'post_type',
        'menu-item-title' => $lang === 'lv' ? 'Sākums' : 'Home',
        'menu-item-position' => 1,
        'menu-item-classes' => implode(' ', array_unique($classes)),
        'menu-item-status' => 'publish',
    ]);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
}
WP_CLI::success('Sākums / Home is first in both native menus, visible on mobile only.');

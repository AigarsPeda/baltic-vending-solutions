<?php
/** Add a native Contact item without replacing existing menus or page content. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
foreach (['lv', 'en'] as $lang) {
    $menu = wp_get_nav_menu_object('Primary ' . strtoupper($lang));
    $homes = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-home', 'numberposts' => 1]);
    if (!$menu || !$homes) throw new RuntimeException('Missing menu or homepage for ' . $lang);
    $items = wp_get_nav_menu_items($menu->term_id);
    $contact = null;
    foreach ($items as $item) {
        if (in_array('bvs-contact-menu', $item->classes, true)) $contact = $item;
    }
    $ordered = [];
    $position = null;
    foreach ($items as $item) {
        if ($contact && $item->ID === $contact->ID) continue;
        // Polylang adds lang-item classes only when rendering its native placeholder.
        if ($position === null && get_post_meta($item->ID, '_pll_menu_item', true)) $position = count($ordered) + 1;
        $ordered[] = $item->ID;
    }
    $position = $position ?? count($ordered) + 1;
    $result = wp_update_nav_menu_item($menu->term_id, $contact ? $contact->ID : 0, [
        'menu-item-type' => 'custom',
        'menu-item-title' => $lang === 'lv' ? 'Kontakti' : 'Contact',
        'menu-item-url' => get_permalink($homes[0]->ID) . '#quote',
        'menu-item-position' => $position,
        'menu-item-classes' => 'bvs-contact-menu',
        'menu-item-status' => 'publish',
    ]);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    array_splice($ordered, $position - 1, 0, [$result]);
    foreach ($ordered as $index => $id) wp_update_post(['ID' => $id, 'menu_order' => $index + 1]);
}
WP_CLI::success('Kontakti / Contact added before the language switch in both native menus.');

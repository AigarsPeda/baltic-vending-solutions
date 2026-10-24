<?php
/** One-time editorial update. Revisions preserve the previous pages; reruns preserve later edits. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Run on the BVS Local site only.');
require_once __DIR__.'/homepage-content.php';
foreach (['lv', 'en'] as $lang) {
    $pages = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'meta_key'=>'_bvs_seed_key', 'meta_value'=>$lang.'-home', 'numberposts'=>1]);
    if (!$pages) throw new RuntimeException('Missing homepage: '.$lang);
    $page = $pages[0];
    if (get_post_meta($page->ID, '_bvs_homepage_refinement', true)) continue;
    $result = wp_update_post(wp_slash(['ID'=>$page->ID, 'post_content'=>bvs_refine_home_content($page->post_content, $lang)]), true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    update_post_meta($page->ID, '_bvs_homepage_refinement', 1);
    WP_CLI::log('Updated native homepage blocks: '.$lang);
}
if (!get_option('bvs_homepage_menu_refinement')) {
    foreach (['lv', 'en'] as $lang) {
        $menu = wp_get_nav_menu_object('Primary '.strtoupper($lang));
        if (!$menu) throw new RuntimeException('Missing navigation: '.$lang);
        $items = wp_get_nav_menu_items($menu->term_id);
        $editor = null; $ordered = [];
        foreach ($items as $item) {
            if (in_array('bvs-design-menu', $item->classes, true)) {
                $editor = $item;
                update_post_meta($item->ID, '_menu_item_classes', array_values(array_diff($item->classes, ['bvs-design-menu'])));
            } else {
                if ($editor && in_array('bvs-contact-menu', $item->classes, true)) { $ordered[] = $editor->ID; $editor = null; }
                $ordered[] = $item->ID;
            }
        }
        if ($editor) $ordered[] = $editor->ID;
        foreach ($ordered as $index=>$id) wp_update_post(['ID'=>$id, 'menu_order'=>$index + 1]);
    }
    update_option('bvs_homepage_menu_refinement', 1);
}
WP_CLI::success('Homepage refinement complete. Further content edits belong in WordPress.');

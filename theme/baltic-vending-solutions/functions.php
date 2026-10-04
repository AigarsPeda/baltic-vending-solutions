<?php
/** Presentation only. Authored content belongs in WordPress. */
defined('ABSPATH') || exit;
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 80, 'width' => 280, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/site.css');
    register_nav_menus(['primary' => __('Primary navigation', 'bvs')]);
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bvs-site', get_theme_file_uri('assets/site.css'), [], filemtime(get_theme_file_path('assets/site.css')));
    wp_enqueue_script('bvs-navigation', get_theme_file_uri('assets/navigation.js'), [], filemtime(get_theme_file_path('assets/navigation.js')), true);
    wp_enqueue_script('bvs-details', get_theme_file_uri('assets/details.js'), [], filemtime(get_theme_file_path('assets/details.js')), true);
    wp_enqueue_script('bvs-counters', get_theme_file_uri('assets/counters.js'), [], filemtime(get_theme_file_path('assets/counters.js')), true);
});
add_action('widgets_init', function () {
    $languages = function_exists('pll_languages_list') ? pll_languages_list(['fields' => 'slug']) : [];
    foreach (array_unique(array_merge(['lv', 'en'], $languages)) as $language) {
        foreach (['header' => 'Header actions', 'footer' => 'Footer content', 'cookies' => 'Cookie banner'] as $area => $label) {
            register_sidebar(['name' => "$label — " . strtoupper($language), 'id' => "bvs-$area-$language", 'before_widget' => '<div class="bvs-widget">', 'after_widget' => '</div>', 'before_title' => '<h2>', 'after_title' => '</h2>']);
        }
    }
});
function bvs_language() {
    return function_exists('pll_current_language') ? (pll_current_language('slug') ?: 'lv') : 'en';
}
// Interface controls are also editable under Settings > BVS enquiries.
function bvs_ui($key) {
    $options = get_option('bvs_interface', []);
    return $options[bvs_language()][$key] ?? ($key === 'skip' ? __('Skip to content', 'bvs') : __('Menu', 'bvs'));
}
// Keep the native Contact menu item pointed at the form on the current page.
function bvs_contact_anchor($blocks) {
    foreach ($blocks as $block) {
        $anchor = $block['attrs']['anchor'] ?? '';
        if (in_array($anchor, ['quote', 'design-quote'], true)) return $anchor;
        $anchor = bvs_contact_anchor($block['innerBlocks']);
        if ($anchor) return $anchor;
    }
    return '';
}
add_filter('wp_nav_menu_objects', function ($items, $args) {
    if (($args->theme_location ?? '') !== 'primary') return $items;
    $page = get_queried_object();
    $anchor = $page instanceof WP_Post ? bvs_contact_anchor(parse_blocks($page->post_content)) : '';
    $home = function_exists('pll_home_url') ? pll_home_url(bvs_language()) : home_url('/');
    foreach ($items as $item) {
        if (in_array('bvs-contact-menu', $item->classes, true)) {
            $item->url = $anchor ? '#' . $anchor : $home . '#quote';
        }
    }
    return $items;
}, 10, 2);

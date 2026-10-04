<?php
/** One-time expansion of the existing Local privacy drafts. Preserve later authored edits. */
if (!defined('WP_CLI') || !WP_CLI || !function_exists('bvs_is_local') || !bvs_is_local()) throw new RuntimeException('Privacy setup requires the Local BVS site.');
require_once __DIR__.'/privacy-content.php';
$privacy_pages = [];
foreach (['lv','en'] as $lang) {
    $pages = get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_bvs_seed_key','meta_value'=>$lang.'-privacy','numberposts'=>1]);
    if (!$pages) throw new RuntimeException('Missing privacy page for '.$lang.'.');
    $privacy_pages[$lang] = $pages[0]->ID;
    if (get_post_meta($pages[0]->ID, '_bvs_privacy_version', true)) continue;
    $result = wp_update_post(wp_slash(['ID'=>$pages[0]->ID, 'post_title'=>bvs_privacy_copy($lang)['title'], 'post_content'=>bvs_privacy_content($lang)]), true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    update_post_meta($pages[0]->ID, '_bvs_privacy_version', '1');
}
$selected_policy = get_post((int) get_option('wp_page_for_privacy_policy'));
require_once ABSPATH.'wp-admin/includes/class-wp-privacy-policy-content.php';
$is_starter_policy = $selected_policy && $selected_policy->post_status === 'draft'
    && $selected_policy->post_name === 'privacy-policy'
    && trim($selected_policy->post_content) === trim(WP_Privacy_Policy_Content::get_default_content());
if (!$selected_policy || $is_starter_policy) update_option('wp_page_for_privacy_policy', $privacy_pages['lv']);
WP_CLI::success('Editable LV/EN privacy drafts ready. Existing routes, translations and subsequent edits are preserved.');

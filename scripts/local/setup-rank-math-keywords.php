<?php
/** Add starter analysis keywords without replacing later editorial choices. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
if (!defined('RANK_MATH_VERSION')) throw new RuntimeException('Rank Math must be active.');
$keywords = [
    'lv-home' => 'viedās tirdzniecības iekārtas',
    'en-home' => 'smart vending machines',
    'lv-compact' => 'Compact Cooler',
    'en-compact' => 'Compact Cooler',
    'lv-fridge' => 'Smart Fridge',
    'en-fridge' => 'Smart Fridge',
    'lv-design' => 'dizaina redaktors',
    'en-design' => 'design editor',
    'lv-privacy' => 'privātums un sīkdatnes',
    'en-privacy' => 'privacy and cookies',
];
foreach ($keywords as $key => $keyword) {
    $pages = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'meta_key' => '_bvs_seed_key', 'meta_value' => $key, 'numberposts' => 1]);
    if (!$pages) continue;
    $id = $pages[0]->ID;
    if (!metadata_exists('post', $id, 'rank_math_focus_keyword')) {
        update_post_meta($id, 'rank_math_focus_keyword', $keyword);
        WP_CLI::log($key . ': ' . $keyword);
    }
}
WP_CLI::success('Starter focus keywords added. Run Rank Math > Status & Tools > Database Tools > Recalculate Scores to save native scores.');

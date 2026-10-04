<?php
/** Fill missing native Polylang flags without replacing later language choices. */
if (!defined('WP_CLI') || !WP_CLI || !function_exists('bvs_is_local') || !bvs_is_local()) throw new RuntimeException('Use the Local BVS installation.');
if (!function_exists('PLL')) throw new RuntimeException('Activate Polylang first.');
foreach (['lv'=>'lv', 'en'=>'us'] as $slug=>$flag) {
    $language = PLL()->model->get_language($slug);
    if (!$language) throw new RuntimeException('Missing language '.$slug.'.');
    if ($language->flag_code) continue;
    $result = PLL()->model->update_language(['lang_id'=>$language->term_id, 'flag'=>$flag]);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
}
WP_CLI::success('Missing LV/EN flags assigned in native Polylang settings.');

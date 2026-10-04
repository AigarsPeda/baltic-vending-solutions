<?php
/** Consent and native widget checks against this project's Local installation. No saved changes. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use the running Local BVS installation.');
function bvs_consent_assert($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
$original_cookie = $_COOKIE['bvs_consent'] ?? null;
$now = (int) floor(microtime(true) * 1000);
try {
    foreach ([
        null, 'malformed', [],
        ['v'=>0,'analytics'=>true,'at'=>$now],
        ['v'=>1,'analytics'=>false,'at'=>$now],
        ['v'=>1,'analytics'=>'true','at'=>$now],
        ['v'=>1,'analytics'=>true,'at'=>(string)$now],
        ['v'=>1,'analytics'=>true,'at'=>$now+DAY_IN_SECONDS*1000],
        ['v'=>1,'analytics'=>true,'at'=>$now-181*DAY_IN_SECONDS*1000],
    ] as $choice) {
        $_COOKIE['bvs_consent'] = is_array($choice) ? rawurlencode(wp_json_encode($choice)) : $choice;
        bvs_consent_assert(!bvs_analytics_consent(), 'Missing, rejected or invalid choices must not grant Analytics consent.');
        bvs_consent_assert(apply_filters('googlesitekit_analytics-4_tag_blocked', false), 'Local Analytics tag must stay blocked.');
    }
    $_COOKIE['bvs_consent'] = rawurlencode(wp_json_encode(['v'=>1,'analytics'=>true,'at'=>$now]));
    bvs_consent_assert(bvs_analytics_consent(), 'An explicit, current acceptance is recognised.');
    bvs_consent_assert(apply_filters('googlesitekit_analytics-4_tag_blocked', false), 'Local must block Analytics even after acceptance.');
    bvs_consent_assert(apply_filters('googlesitekit_analytics-4_tag_blocked', true), 'An existing Site Kit block must survive.');
    foreach (['ads','adsense','tagmanager'] as $module) bvs_consent_assert(apply_filters('googlesitekit_'.$module.'_tag_blocked', false), 'Unplanned Google tags must stay blocked.');
    bvs_consent_assert(apply_filters('wp_get_consent_type', false)==='optin', 'WP Consent API must default to opt-in.');
    bvs_consent_assert(defined('WP_CONSENT_API_VERSION'), 'WP Consent API is active.');
    $block = WP_Block_Type_Registry::get_instance()->get_registered('bvs/cookie-banner');
    bvs_consent_assert($block && $block->is_dynamic() && $block->editor_script_handles, 'Cookie block has an editor and dynamic frontend.');
    $widgets = get_option('widget_block', []);
    $sidebars = get_option('sidebars_widgets', []);
    foreach (['lv','en'] as $lang) {
        $found = false;
        foreach ($sidebars['bvs-cookies-'.$lang] ?? [] as $id) {
            $number = (int) str_replace('block-', '', $id);
            foreach (parse_blocks($widgets[$number]['content'] ?? '') as $saved) {
                if ($saved['blockName'] !== 'bvs/cookie-banner') continue;
                $found = true;
                foreach (['title','description','rejectLabel','acceptLabel','closeLabel','settingsLabel','privacyLabel','privacyUrl','storageError'] as $key) {
                    // Native WordPress omits attributes equal to their registered defaults on save.
                    bvs_consent_assert(!empty($saved['attrs'][$key] ?? $block->attributes[$key]['default'] ?? null), 'Banner copy is available through native editable block attributes.');
                }
                bvs_consent_assert(pll_get_post_language(url_to_postid($saved['attrs']['privacyUrl'])) === $lang && get_post_meta(url_to_postid($saved['attrs']['privacyUrl']), '_bvs_seed_key', true) === $lang.'-privacy', 'Each banner links to its translated privacy page.');
            }
        }
        bvs_consent_assert($found, 'Each language has an editable cookie widget.');
    }
} finally {
    if ($original_cookie === null) unset($_COOKIE['bvs_consent']);
    else $_COOKIE['bvs_consent'] = $original_cookie;
}
WP_CLI::success('Consent validation, Local tag blocking, WP Consent API and translated native widget storage passed.');

<?php
defined('ABSPATH') || exit;
const BVS_CONSENT_VERSION = 1;
const BVS_CONSENT_LIFETIME = 180 * DAY_IN_SECONDS;

function bvs_saved_cookie_choice() {
    $raw = $_COOKIE['bvs_consent'] ?? '';
    if (!is_string($raw)) return null;
    $choice = json_decode(rawurldecode(wp_unslash($raw)), true);
    $now = microtime(true) * 1000;
    return is_array($choice) && ($choice['v'] ?? null) === BVS_CONSENT_VERSION
        && is_bool($choice['analytics'] ?? null) && (is_int($choice['at'] ?? null) || is_float($choice['at'] ?? null))
        && $choice['at'] <= $now && $now - $choice['at'] < BVS_CONSENT_LIFETIME * 1000 ? $choice : null;
}
function bvs_analytics_consent() { return (bvs_saved_cookie_choice()['analytics'] ?? false) === true; }
// Public language routes work without persistence. Defer the language cookie until a choice is saved.
add_action('plugins_loaded', function () {
    if (is_admin()) return;
    if (!bvs_saved_cookie_choice() && !defined('PLL_COOKIE')) define('PLL_COOKIE', false);
    // WordPress's optional emoji detector writes a sessionStorage cache on a fresh visit.
    // Native browser emoji rendering avoids that pre-choice persistence.
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('embed_head', 'print_emoji_detection_script');
}, 0);
add_filter('wp_get_consent_type', fn() => 'optin');
add_filter('wp_consent_api_registered_bvs-site/bvs-site.php', '__return_true');
// Basic opt-in: Site Kit remains the only tag owner and cannot render GA before a choice.
add_filter('googlesitekit_analytics-4_tag_blocked', fn($blocked) => $blocked || wp_get_environment_type() !== 'production' || is_user_logged_in() || !bvs_analytics_consent());
foreach (['ads','adsense','tagmanager'] as $module) add_filter('googlesitekit_'.$module.'_tag_blocked', '__return_true');

add_action('init', function () {
    wp_register_script('bvs-cookie-editor', plugins_url('cookie-banner/editor.js', __FILE__), ['wp-blocks','wp-element','wp-block-editor','wp-components'], filemtime(__DIR__.'/cookie-banner/editor.js'), true);
    $metadata = json_decode(file_get_contents(__DIR__.'/cookie-banner/block.json'), true);
    wp_add_inline_script('bvs-cookie-editor', 'window.bvsCookieMetadata = '.wp_json_encode($metadata).';', 'before');
    wp_register_script('bvs-cookie-view', plugins_url('cookie-banner/view.js', __FILE__), defined('WP_CONSENT_API_VERSION') ? ['wp-consent-api'] : [], filemtime(__DIR__.'/cookie-banner/view.js'), true);
    wp_register_style('bvs-cookie-style', plugins_url('cookie-banner/style.css', __FILE__), [], filemtime(__DIR__.'/cookie-banner/style.css'));
    register_block_type(__DIR__.'/cookie-banner', ['render_callback'=>'bvs_render_cookie_banner']);
});
function bvs_render_cookie_banner($attributes) {
    static $rendered = false;
    if ($rendered) return '';
    $rendered = true;
    $meta = json_decode(file_get_contents(__DIR__.'/cookie-banner/block.json'), true);
    $a = array_merge(array_map(fn($attribute) => $attribute['default'], $meta['attributes']), $attributes);
    $analytics = get_option('googlesitekit_analytics-4_settings', []);
    $reload = wp_get_environment_type() === 'production' && !is_user_logged_in()
        && !empty($analytics['useSnippet']) && preg_match('/^G-[A-Z0-9]+$/', $analytics['measurementID'] ?? '');
    ob_start(); ?>
    <button type="button" class="bvs-cookie-settings" aria-controls="bvs-cookie-banner" aria-expanded="false" hidden><?php echo esc_html($a['settingsLabel']); ?></button>
    <section id="bvs-cookie-banner" class="bvs-cookie-banner" role="region" aria-labelledby="bvs-cookie-title" data-version="<?php echo BVS_CONSENT_VERSION; ?>" data-lifetime="<?php echo BVS_CONSENT_LIFETIME; ?>" data-reload="<?php echo $reload ? 'true' : 'false'; ?>" hidden>
        <div class="bvs-cookie-inner">
            <div class="bvs-cookie-copy"><h2 id="bvs-cookie-title"><?php echo esc_html($a['title']); ?></h2><p><?php echo esc_html($a['description']); ?></p>
                <?php if ($a['privacyUrl']): ?><a href="<?php echo esc_url($a['privacyUrl']); ?>"><?php echo esc_html($a['privacyLabel']); ?></a><?php endif; ?>
                <p class="bvs-cookie-error" role="status" hidden><?php echo esc_html($a['storageError']); ?></p>
            </div>
            <div class="bvs-cookie-actions">
                <button type="button" data-cookie-choice="false"><?php echo esc_html($a['rejectLabel']); ?></button>
                <button type="button" data-cookie-choice="true"><?php echo esc_html($a['acceptLabel']); ?></button>
                <button type="button" class="bvs-cookie-close" data-cookie-close hidden><?php echo esc_html($a['closeLabel']); ?></button>
            </div>
        </div>
    </section>
    <?php return ob_get_clean();
}

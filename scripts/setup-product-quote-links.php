<?php
/** Update only the native hero buttons on the four translated product pages. */
if (!defined('WP_CLI') || !WP_CLI || get_stylesheet() !== 'baltic-vending-solutions') {
    throw new RuntimeException('Run with WP-CLI on the BVS site.');
}
$updates = [];
foreach (['lv', 'en'] as $lang) foreach (['compact', 'fridge'] as $model) {
    $pages = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-' . $model, 'numberposts' => 1]);
    if (!$pages) throw new RuntimeException('Missing product page: ' . $lang . '-' . $model);
    $page = $pages[0];
    if (!str_contains($page->post_content, 'id="quote"')) throw new RuntimeException('Missing on-page quote destination.');
    $content = preg_replace_callback('~<!-- wp:buttons(?: \{.*?\})? -->.*?<!-- /wp:buttons -->~s', function ($match) {
        $buttons = parse_blocks($match[0])[0];
        if (count($buttons['innerBlocks']) < 1 || count($buttons['innerBlocks']) > 2) {
            throw new RuntimeException('Unexpected product hero buttons.');
        }
        $primary = $buttons['innerBlocks'][0];
        $html = new WP_HTML_Tag_Processor($primary['innerHTML']);
        if (!$html->next_tag('a') || !str_ends_with($html->get_attribute('href') ?? '', '#quote')) {
            throw new RuntimeException('Expected a quote button.');
        }
        $html->set_attribute('href', '#quote');
        $primary['innerHTML'] = $html->get_updated_html();
        $primary['innerContent'] = [$primary['innerHTML']];
        if (isset($buttons['innerBlocks'][1])) {
            $secondary = new WP_HTML_Tag_Processor($buttons['innerBlocks'][1]['innerHTML']);
            if (!$secondary->next_tag('a') || !str_ends_with($secondary->get_attribute('href') ?? '', '#equipment')) {
                throw new RuntimeException('Expected the secondary equipment button.');
            }
        }
        $buttons['innerBlocks'] = [$primary];
        $buttons['innerContent'] = [$buttons['innerContent'][0], null, end($buttons['innerContent'])];
        return serialize_block($buttons);
    }, $page->post_content, -1, $count);
    if ($count !== 1) throw new RuntimeException('Expected one product hero button group.');
    if ($content !== $page->post_content) $updates[$page->ID] = $content;
}
foreach ($updates as $id => $content) {
    $result = wp_update_post(wp_slash(['ID' => $id, 'post_content' => $content]), true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
}
WP_CLI::success(count($updates) . ' product pages updated. Quote buttons target the on-page form; secondary equipment buttons removed.');

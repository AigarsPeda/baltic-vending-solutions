<?php
/** Add editable placeholder contact details to the homepage introductions. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
foreach (['lv', 'en'] as $lang) {
    $pages = get_posts(['post_type' => 'page', 'meta_key' => '_bvs_seed_key', 'meta_value' => $lang . '-home', 'numberposts' => 1]);
    if (!$pages) throw new RuntimeException('Missing homepage for ' . $lang);
    $page = $pages[0];
    $blocks = parse_blocks($page->post_content);
    $found = false;
    foreach ($blocks as &$block) {
        if (($block['attrs']['anchor'] ?? '') !== 'quote') continue;
        foreach ($block['innerBlocks'] as &$columns) {
            if ($columns['blockName'] !== 'core/columns') continue;
            $column = &$columns['innerBlocks'][0];
            $found = true;
            $exists = false;
            foreach ($column['innerBlocks'] as $child) {
                if (in_array('bvs-contact-details', explode(' ', $child['attrs']['className'] ?? ''), true)) { $exists = true; break; }
            }
            if ($exists) { unset($column); continue; }
            $phone = $lang === 'lv' ? 'Tālrunis' : 'Phone';
            $email = $lang === 'lv' ? 'E-pasts' : 'Email';
            $html = '<!-- wp:group {"className":"bvs-contact-details","layout":{"type":"constrained"}} --><div class="wp-block-group bvs-contact-details">';
            $html .= '<!-- wp:paragraph --><p>' . esc_html($phone) . '<br><strong>+371 XX XXX XXX</strong></p><!-- /wp:paragraph -->';
            $html .= '<!-- wp:paragraph --><p>' . esc_html($email) . '<br><strong>info@example.com</strong></p><!-- /wp:paragraph -->';
            $html .= '</div><!-- /wp:group -->';
            $column['innerBlocks'][] = parse_blocks($html)[0];
            array_splice($column['innerContent'], count($column['innerContent']) - 1, 0, [null]);
            unset($column);
        }
        unset($columns);
    }
    unset($block);
    if (!$found) throw new RuntimeException('Missing contact introduction for ' . $lang);
    $content = serialize_blocks($blocks);
    if ($content !== $page->post_content) {
        $result = wp_update_post(wp_slash(['ID' => $page->ID, 'post_content' => $content]), true);
        if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    }
}
WP_CLI::success('Editable phone and email placeholders added below both homepage contact introductions.');

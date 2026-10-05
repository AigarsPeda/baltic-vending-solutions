<?php
/** Migrate fixed legacy brand colours to the theme's named presets. Reruns are safe. */
if (wp_get_environment_type() !== 'local') throw new RuntimeException('Run this migration on Local only.');
$roles = ['#00675f'=>'accent', '#00534d'=>'accent-hover', '#e4f1ef'=>'accent-soft', '#a5d1cb'=>'accent-light'];
$attributes = function ($value) use (&$attributes, $roles) {
    if (is_array($value)) return array_map($attributes, $value);
    return is_string($value) && isset($roles[strtolower($value)]) ? 'var:preset|color|'.$roles[strtolower($value)] : $value;
};
$html = function ($value) use ($roles) {
    return preg_replace_callback('/((?:background-)?color\s*:\s*)(#(?:00675f|00534d|e4f1ef|a5d1cb))\b/i',
        fn($match)=>$match[1].'var(--wp--preset--color--'.$roles[strtolower($match[2])].')', $value);
};
$blocks = function ($items) use (&$blocks, $attributes, $html) {
    foreach ($items as &$block) {
        $block['attrs'] = $attributes($block['attrs']);
        $block['innerBlocks'] = $blocks($block['innerBlocks']);
        $block['innerHTML'] = $html($block['innerHTML']);
        $block['innerContent'] = array_map(fn($part)=>is_string($part) ? $html($part) : $part, $block['innerContent']);
    }
    return $items;
};
$migrate = fn($content)=>serialize_blocks($blocks(parse_blocks($content)));
$changed = 0;
foreach (get_posts(['post_type'=>['page','wp_block'], 'post_status'=>'publish', 'numberposts'=>-1]) as $post) {
    $content = $migrate($post->post_content);
    if ($content === $post->post_content) continue;
    $result = wp_update_post(['ID'=>$post->ID, 'post_content'=>wp_slash($content)], true);
    if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
    $changed++;
}
$widgets = get_option('widget_block', []);
$changed_widgets = 0;
foreach ($widgets as &$widget) {
    if (!is_array($widget) || !isset($widget['content'])) continue;
    $content = $migrate($widget['content']);
    if ($content === $widget['content']) continue;
    $widget['content'] = $content;
    $changed_widgets++;
}
unset($widget);
if ($changed_widgets) update_option('widget_block', $widgets);
WP_CLI::success("Updated $changed page/block records and $changed_widgets widgets to named brand presets.");

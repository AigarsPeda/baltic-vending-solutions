<?php
/** Native starter blocks for the homepage branding section. */
function bvs_branding_block($name, $attributes, $html) {
    return '<!-- wp:'.$name.($attributes ? ' '.wp_json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '').' -->'.$html.'<!-- /wp:'.$name.' -->';
}
function bvs_branding_media() {
    $existing = get_posts(['post_type'=>'attachment', 'post_status'=>'inherit', 'meta_key'=>'_bvs_source', 'meta_value'=>'client-janogago', 'numberposts'=>1]);
    if ($existing) { return $existing[0]->ID; }
    require_once ABSPATH.'wp-admin/includes/media.php';
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/image.php';
    $source = dirname(__DIR__, 2).'/media/clients/janoga-mini-cafe.png';
    if (!is_file($source)) { throw new RuntimeException('Missing supplied JāņogaGO client photo.'); }
    $temp = wp_tempnam('janoga-mini-cafe.png');
    if (!copy($source, $temp)) { throw new RuntimeException('Could not copy the client photo.'); }
    $id = media_handle_sideload(['name'=>'janoga-mini-cafe.png', 'tmp_name'=>$temp], 0, 'JāņogaGO branded vending machines');
    if (is_wp_error($id)) { @unlink($temp); throw new RuntimeException($id->get_error_message()); }
    update_post_meta($id, '_bvs_source', 'client-janogago');
    update_post_meta($id, '_wp_attachment_image_alt', 'JāņogaGO smart fridges with a red branded wrap');
    return $id;
}
function bvs_branding_image($lang, $id) {
    $alt = $lang === 'lv' ? 'JāņogaGO viedie ledusskapji ar sarkanu zīmola aplīmējumu' : 'JāņogaGO smart fridges with a red branded wrap';
    return bvs_branding_block('image', ['id'=>$id, 'sizeSlug'=>'large', 'linkDestination'=>'none', 'className'=>'bvs-branding-image'],
        '<figure class="wp-block-image size-large bvs-branding-image"><img src="'.esc_url(wp_get_attachment_image_url($id, 'large')).'" alt="'.esc_attr($alt).'" class="wp-image-'.$id.'"/></figure>');
}
function bvs_branding_content($lang) {
    $copy = $lang === 'lv' ? [
        'title'=>'Iekārta, kas izceļ',
        'titleAccent'=>'jūsu zīmolu',
        'intro'=>'Piedāvājam individuālu iekārtu aplīmēšanu jūsu zīmola krāsās. Dizainu pielāgojam jūsu materiāliem un izvēlētajam modelim.',
        'details'=>[
            ['Jūsu logotips, krāsas un dizains', 'Atsūtiet zīmola materiālus un produktu klāstu. Pielāgojam to izvietojumu izvēlētajai iekārtai.'],
            ['Dizainu saskaņojam ar jums', 'Pirms darba sākuma vienojamies par dizainu un to, kuras iekārtas daļas aplīmēt.'],
        ],
    ] : [
        'title'=>'A machine that makes',
        'titleAccent'=>'your brand stand out',
        'intro'=>'We offer custom machine wrapping in your brand colours. We adapt the design to your artwork and chosen model.',
        'details'=>[
            ['Your logo, colours and artwork', 'Share your brand artwork and product range. We adapt the layout to your chosen machine.'],
            ['You approve the design', 'Before we start, we agree the design and which parts of the machine to wrap.'],
        ],
    ];
    $heading = bvs_branding_block('heading', [], '<h2 class="wp-block-heading">'.esc_html($copy['title']).' <mark style="background-color:rgba(0, 0, 0, 0);color:#a5d1cb" class="has-inline-color">'.esc_html($copy['titleAccent']).'</mark></h2>');
    $intro = bvs_branding_block('paragraph', ['className'=>'bvs-lead'], '<p class="bvs-lead">'.esc_html($copy['intro']).'</p>');
    $details = '';
    foreach ($copy['details'] as [$title, $description]) {
        $content = bvs_branding_block('heading', ['level'=>3], '<h3 class="wp-block-heading">'.esc_html($title).'</h3>')
            .bvs_branding_block('paragraph', [], '<p>'.esc_html($description).'</p>');
        $details .= bvs_branding_block('group', ['className'=>'bvs-branding-detail'], '<div class="wp-block-group bvs-branding-detail">'.$content.'</div>');
    }
    require_once __DIR__.'/design-content.php';
    $editor_url = bvs_design_page_url($lang);
    if ($editor_url) $details .= bvs_design_button($lang, $editor_url);
    $columns = bvs_branding_block('column', [], '<div class="wp-block-column">'.$heading.$intro.$details.'</div>')
        .bvs_branding_block('column', ['className'=>'bvs-branding-example'], '<div class="wp-block-column bvs-branding-example">'.bvs_branding_image($lang, bvs_branding_media()).'</div>');
    $layout = bvs_branding_block('columns', ['className'=>'bvs-branding-layout'], '<div class="wp-block-columns bvs-branding-layout">'.$columns.'</div>');
    return bvs_branding_block('group', ['className'=>'bvs-section bvs-branding', 'anchor'=>'branding', 'layout'=>['type'=>'constrained']], '<div id="branding" class="wp-block-group bvs-section bvs-branding">'.$layout.'</div>');
}

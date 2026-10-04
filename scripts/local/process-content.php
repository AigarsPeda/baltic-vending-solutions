<?php
/** Native starter blocks and local photos for the project-start sequence. */
function bvs_process_block($name, $attributes, $html) {
    return '<!-- wp:'.$name.($attributes ? ' '.wp_json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '').' -->'.$html.'<!-- /wp:'.$name.' -->';
}
function bvs_process_media() {
    require_once ABSPATH.'wp-admin/includes/media.php';
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/image.php';
    $ids = [];
    foreach (['planning'=>'Planning a vending project', 'discussion'=>'Discussing a vending proposal'] as $key => $title) {
        $existing = get_posts(['post_type'=>'attachment', 'post_status'=>'inherit', 'meta_key'=>'_bvs_source', 'meta_value'=>'process-'.$key, 'numberposts'=>1]);
        if ($existing) { $ids[$key] = $existing[0]->ID; continue; }
        $source = dirname(__DIR__, 2).'/media/process/'.$key.'.jpg';
        if (!is_file($source)) { throw new RuntimeException('Missing project-start photo: '.$source); }
        $temp = wp_tempnam($key.'.jpg');
        if (!copy($source, $temp)) { throw new RuntimeException('Could not copy project-start photo.'); }
        $id = media_handle_sideload(['name'=>'project-'.$key.'.jpg', 'tmp_name'=>$temp], 0, $title);
        if (is_wp_error($id)) { @unlink($temp); throw new RuntimeException($id->get_error_message()); }
        update_post_meta($id, '_bvs_source', 'process-'.$key);
        update_post_meta($id, '_wp_attachment_image_alt', $title);
        $credits = [
            'planning'=>['MJ Duford', 'https://unsplash.com/photos/person-is-working-on-a-laptop-and-writing-in-a-notebook-45u1mboQtQE'],
            'discussion'=>['Campaign Creators', 'https://unsplash.com/photos/people-sitting-near-table-with-laptop-computer-qCi_MzVODoU'],
        ];
        update_post_meta($id, '_bvs_photo_credit', $credits[$key][0]);
        update_post_meta($id, '_bvs_photo_source_url', $credits[$key][1]);
        update_post_meta($id, '_bvs_photo_license_url', 'https://unsplash.com/license');
        $ids[$key] = $id;
    }
    $machine = get_posts(['post_type'=>'attachment', 'post_status'=>'inherit', 'meta_key'=>'_bvs_source', 'meta_value'=>'compact', 'numberposts'=>1]);
    if (!$machine) { throw new RuntimeException('Missing Compact Cooler photo.'); }
    return [$ids['planning'], $machine[0]->ID, $ids['discussion']];
}
function bvs_process_content($title, $steps, $images, $lang) {
    $alts = $lang === 'lv'
        ? ['Plāna pierakstīšana pie datora', 'Compact Cooler viedais ledusskapis', 'Piedāvājuma pārrunāšana pie galda']
        : ['Writing a plan beside a laptop', 'Compact Cooler smart fridge', 'Discussing a proposal around a table'];
    $heading = bvs_process_block('heading', [], '<h2 class="wp-block-heading">'.esc_html($title).'</h2>');
    $cards = '';
    foreach ($steps as $index => [$name, $description]) {
        $id = $images[$index];
        $image_class = 'bvs-process-image'.($index === 1 ? ' bvs-process-machine' : '');
        $image = bvs_process_block('image', ['id'=>$id, 'sizeSlug'=>'large', 'linkDestination'=>'none', 'className'=>$image_class],
            '<figure class="wp-block-image size-large '.$image_class.'"><img src="'.esc_url(wp_get_attachment_image_url($id, 'large')).'" alt="'.esc_attr($alts[$index]).'" class="wp-image-'.$id.'"/></figure>');
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $copy = bvs_process_block('paragraph', ['className'=>'bvs-step-number'], '<p class="bvs-step-number">'.$number.'</p>')
            .bvs_process_block('heading', ['level'=>3], '<h3 class="wp-block-heading">'.esc_html($name).'</h3>')
            .bvs_process_block('paragraph', [], '<p>'.esc_html($description).'</p>');
        $copy = bvs_process_block('group', ['className'=>'bvs-process-copy'], '<div class="wp-block-group bvs-process-copy">'.$copy.'</div>');
        $cards .= bvs_process_block('column', ['className'=>'bvs-process-step'], '<div class="wp-block-column bvs-process-step">'.$image.$copy.'</div>');
    }
    $cards = bvs_process_block('columns', ['className'=>'bvs-process-steps'], '<div class="wp-block-columns bvs-process-steps">'.$cards.'</div>');
    return bvs_process_block('group', ['className'=>'bvs-section bvs-process', 'layout'=>['type'=>'constrained']], '<div class="wp-block-group bvs-section bvs-process">'.$heading.$cards.'</div>');
}

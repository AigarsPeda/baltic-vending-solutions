<?php
/**
 * Plugin Name: BVS site functionality
 * Description: Editable quote form, private enquiries and local notification capture.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;
require_once __DIR__.'/cookie-banner.php';
require_once __DIR__.'/design-editor.php';
require_once __DIR__.'/design-attachments.php';
function bvs_is_local() {
    return wp_get_environment_type() === 'local' && str_ends_with((string) wp_parse_url(home_url(), PHP_URL_HOST), '.local');
}
add_action('init', function () {
    register_post_type('bvs_enquiry', [
        'labels' => ['name' => 'Quote enquiries', 'singular_name' => 'Quote enquiry'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => false, 'exclude_from_search' => true,
        'menu_icon' => 'dashicons-email', 'supports' => ['title'],
        'capabilities' => ['edit_post'=>'manage_options', 'read_post'=>'manage_options', 'delete_post'=>'manage_options', 'edit_posts'=>'manage_options', 'edit_others_posts'=>'manage_options', 'publish_posts'=>'manage_options', 'read_private_posts'=>'manage_options', 'delete_posts'=>'manage_options', 'create_posts'=>'do_not_allow'],
        'map_meta_cap' => false,
    ]);
    wp_register_script('bvs-quote-editor', plugins_url('quote-form/editor.js', __FILE__), ['wp-blocks','wp-element','wp-block-editor','wp-components'], filemtime(__DIR__.'/quote-form/editor.js'), true);
    wp_add_inline_script('bvs-quote-editor', 'window.bvsQuoteMetadata = '.wp_json_encode(json_decode(file_get_contents(__DIR__.'/quote-form/block.json'), true)).';', 'before');
    wp_register_script('bvs-quote-view', plugins_url('quote-form/view.js', __FILE__), [], filemtime(__DIR__.'/quote-form/view.js'), true);
    wp_register_style('bvs-quote-style', plugins_url('quote-form/form.css', __FILE__), [], filemtime(__DIR__.'/quote-form/form.css'));
    register_block_type(__DIR__.'/quote-form', ['render_callback'=>'bvs_render_quote']);
});
function bvs_quote_attributes($attributes) {
    $metadata = json_decode(file_get_contents(__DIR__.'/quote-form/block.json'), true);
    return array_merge(array_map(fn($a) => $a['default'], $metadata['attributes']), $attributes);
}
function bvs_render_quote($attributes) {
    $a = bvs_quote_attributes($attributes);
    $id = wp_unique_id('bvs-form-');
    ob_start(); ?>
    <form class="bvs-quote-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-endpoint="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <p class="bvs-form-note"><?php echo esc_html($a['intro']); ?></p>
        <input type="hidden" name="action" value="bvs_quote"><input type="hidden" name="page_id" value="<?php echo get_the_ID(); ?>"><input type="hidden" name="form_id" value="<?php echo esc_attr($a['formId']); ?>">
        <?php wp_nonce_field('bvs_quote', 'bvs_nonce', false); ?>
        <div class="bvs-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="bvs-form-grid">
        <?php foreach (['name'=>'text','company'=>'text','email'=>'email','phone'=>'tel'] as $key=>$type): ?>
            <label class="bvs-field" for="<?php echo esc_attr($id.$key); ?>"><?php echo esc_html($a[$key.'Label']); ?><input id="<?php echo esc_attr($id.$key); ?>" name="<?php echo esc_attr($key); ?>" type="<?php echo esc_attr($type); ?>" autocomplete="<?php echo esc_attr(['name'=>'name','company'=>'organization','email'=>'email','phone'=>'tel'][$key]); ?>" maxlength="200" placeholder="<?php echo esc_attr($a[$key.'Placeholder']); ?>" <?php echo in_array($key,['name','email'],true) ? 'required' : ''; ?>></label>
        <?php endforeach; ?>
        <?php foreach (['model'=>['any'=>'modelAny','compact'=>'compactLabel','fridge'=>'fridgeLabel'],'mode'=>['advice'=>'modeAdvice','buy'=>'modeBuy','rent'=>'modeRent']] as $key=>$choices): ?>
            <label class="bvs-field" for="<?php echo esc_attr($id.$key); ?>"><?php echo esc_html($a[$key.'Label']); ?><select id="<?php echo esc_attr($id.$key); ?>" name="<?php echo esc_attr($key); ?>"><?php foreach ($choices as $value=>$label): ?><option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($a[$label]); ?></option><?php endforeach; ?></select></label>
        <?php endforeach; ?>
        <?php foreach (['products','location','message'] as $key): ?>
            <label class="bvs-field bvs-field-wide" for="<?php echo esc_attr($id.$key); ?>"><?php echo esc_html($a[$key.'Label']); ?><textarea id="<?php echo esc_attr($id.$key); ?>" name="<?php echo esc_attr($key); ?>" rows="<?php echo $key === 'message' ? 3 : 2; ?>" maxlength="2000" placeholder="<?php echo esc_attr($a[$key.'Placeholder']); ?>" <?php echo $key === 'products' ? 'required' : ''; ?>></textarea></label>
        <?php endforeach; ?>
        <div class="bvs-field bvs-field-wide bvs-design-attachment">
            <label for="<?php echo esc_attr($id.'design'); ?>"><?php echo esc_html($a['designLabel']); ?></label>
            <div class="bvs-editor-design-option" hidden>
                <label class="bvs-editor-design-choice"><input type="checkbox" class="bvs-use-editor-design" checked aria-describedby="<?php echo esc_attr($id.'editor-design-hint'); ?>"><?php echo esc_html($a['designEditorLabel']); ?></label>
                <p id="<?php echo esc_attr($id.'editor-design-hint'); ?>" class="bvs-form-note"><?php echo esc_html($a['designEditorHint']); ?></p>
            </div>
            <?php if ($a['createDesignUrl']): ?><div class="bvs-design-upload-row"><?php endif; ?>
            <input id="<?php echo esc_attr($id.'design'); ?>" type="file" name="design" accept="image/png,image/jpeg,image/webp" aria-describedby="<?php echo esc_attr($id.'design-hint'); ?>" data-error="<?php echo esc_attr($a['designError']); ?>">
            <?php if ($a['createDesignUrl']): ?><a class="bvs-create-design" href="<?php echo esc_url($a['createDesignUrl']); ?>"><?php echo esc_html($a['createDesignLabel']); ?></a></div><?php endif; ?>
            <p id="<?php echo esc_attr($id.'design-hint'); ?>" class="bvs-form-note"><?php echo esc_html($a['designHint']); ?></p>
            <button type="button" class="bvs-remove-design" hidden><?php echo esc_html($a['designRemove']); ?></button>
        </div>
        </div>
        <p class="bvs-privacy"><?php echo esc_html($a['privacyText']); ?> <?php if ($a['privacyUrl']): ?><a href="<?php echo esc_url($a['privacyUrl']); ?>"><?php echo esc_html($a['privacyLabel']); ?></a><?php endif; ?></p>
        <button class="bvs-submit" type="submit" data-sending="<?php echo esc_attr($a['sendingLabel']); ?>"><?php echo esc_html($a['submitLabel']); ?></button>
        <p class="bvs-form-message" role="status" aria-live="polite" data-fallback="<?php echo esc_attr($a['unavailableMessage']); ?>" tabindex="-1" hidden></p>
    </form>
    <?php return ob_get_clean();
}
function bvs_find_form($blocks, $id) {
    foreach ($blocks as $block) {
        if ($block['blockName'] === 'bvs/quote-form' && ($block['attrs']['formId'] ?? 'quote') === $id) return bvs_quote_attributes($block['attrs']);
        $found = bvs_find_form($block['innerBlocks'] ?? [], $id);
        if ($found) return $found;
    }
    return null;
}
function bvs_save_quote($data, $upload = null) {
    foreach ($data as $value) if (!is_string($value)) return new WP_Error('invalid_request', 'Invalid request.');
    $page = get_post(absint($data['page_id'] ?? 0));
    $a = $page && $page->post_status === 'publish' ? bvs_find_form(parse_blocks($page->post_content), sanitize_key($data['form_id'] ?? '')) : null;
    if (!$a) return new WP_Error('missing_form', 'The form is unavailable.');
    if (!wp_verify_nonce($data['bvs_nonce'] ?? '', 'bvs_quote') || !empty($data['website'])) return new WP_Error('invalid_request', $a['unavailableMessage']);
    $fields = [];
    foreach (['name','company','email','phone','model','mode','products','location','message'] as $key) {
        if (!isset($data[$key]) || !is_string($data[$key]) || strlen($data[$key]) > (in_array($key,['products','location','message'],true) ? 8000 : 800)) return new WP_Error('invalid_field', $a['errorMessage']);
        $fields[$key] = sanitize_textarea_field($data[$key]);
    }
    $fields['email'] = sanitize_email($fields['email']);
    if (!$fields['name'] || !is_email($fields['email']) || !$fields['products'] || !in_array($fields['model'],['any','compact','fridge'],true) || !in_array($fields['mode'],['advice','buy','rent'],true)) return new WP_Error('validation', $a['errorMessage']);
    $limit_key = 'bvs_rate_'.hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', wp_salt());
    $count = (int) get_transient($limit_key);
    if ($count >= 5) return new WP_Error('rate_limit', $a['unavailableMessage']);
    $design = bvs_validate_design_attachment($upload, $a['designError']);
    if (is_wp_error($design)) return $design;
    $id = wp_insert_post(['post_type'=>'bvs_enquiry','post_status'=>'private','post_title'=>$fields['name'].' — '.$fields['model'],'meta_input'=>['_bvs_fields'=>$fields,'_bvs_source_page'=>$page->ID,'_bvs_language'=>$a['language'],'_bvs_notification'=>'pending']], true);
    if (is_wp_error($id) || !$id) return new WP_Error('save_failed', $a['unavailableMessage']);
    if ($design) {
        $design = bvs_store_design($design, $a['unavailableMessage']);
        if (is_wp_error($design)) { wp_delete_post($id, true);return $design; }
        update_post_meta($id, '_bvs_design', $design);
    }
    set_transient($limit_key, $count + 1, 5 * MINUTE_IN_SECONDS);
    $recipient = sanitize_email(get_option('bvs_recipient', ''));
    $status = 'not_configured';
    if (is_email($recipient)) {
        $body = implode("\n", array_map(fn($key,$value) => ucfirst($key).': '.$value, array_keys($fields), $fields));
        $attachment = $design ? bvs_design_file($design) : null;
        if ($design) $body .= "\nDesign attachment: bvs-design.png";
        try {
            $sent = wp_mail($recipient, 'New equipment enquiry #'.$id, $body, ['Reply-To: '.$fields['email']], $attachment ? [$attachment] : []);
            $status = $sent ? (bvs_is_local() ? 'local_captured' : 'accepted_by_transport') : 'failed';
        } catch (Throwable $error) { $status = 'failed'; }
    }
    update_post_meta($id, '_bvs_notification', $status);
    return ['id'=>$id, 'message'=>$a['successMessage']];
}
// Local must never contact a real provider, even if transport settings are copied.
add_filter('pre_wp_mail', function ($return, $attributes) {
    return bvs_is_local() ? true : $return;
}, PHP_INT_MAX - 10, 2);
function bvs_handle_quote() {
    $result = bvs_save_quote(wp_unslash($_POST), $_FILES['design'] ?? null);
    $ajax = wp_doing_ajax();
    if (is_wp_error($result)) {
        if ($ajax) wp_send_json_error(['message'=>$result->get_error_message()], 400);
        wp_die(esc_html($result->get_error_message()), '', ['response'=>400, 'back_link'=>true]);
    }
    if ($ajax) wp_send_json_success(['message'=>$result['message']]);
    wp_die(esc_html($result['message']), '', ['response'=>200, 'back_link'=>true]);
}
foreach (['wp_ajax_bvs_quote','wp_ajax_nopriv_bvs_quote','admin_post_bvs_quote','admin_post_nopriv_bvs_quote'] as $hook) add_action($hook, 'bvs_handle_quote');
add_action('add_meta_boxes_bvs_enquiry', function () {
    add_meta_box('bvs-details','Enquiry details', function ($post) {
        echo '<dl>';
        foreach ((array) get_post_meta($post->ID, '_bvs_fields', true) as $key=>$value) echo '<dt><strong>'.esc_html(ucfirst($key)).'</strong></dt><dd><p>'.nl2br(esc_html($value)).'</p></dd>';
        echo '</dl><p><strong>Notification:</strong> '.esc_html(get_post_meta($post->ID,'_bvs_notification',true)).'</p>';
        if (get_post_meta($post->ID, '_bvs_design', true)) {
            $url = wp_nonce_url(admin_url('admin-post.php?action=bvs_download_design&enquiry='.$post->ID), 'bvs_design_'.$post->ID);
            echo '<p><a class="button" href="'.esc_url($url).'">Download attached design</a></p>';
        }
        if (bvs_is_local()) echo '<p>Local capture is active. No email leaves this site. The values above are the captured notification content.</p>';
    }, 'bvs_enquiry', 'normal', 'high');
});
add_filter('manage_bvs_enquiry_posts_columns', function ($columns) { $columns['bvs_notification']='Notification'; return $columns; });
add_action('manage_bvs_enquiry_posts_custom_column', function ($column,$id) { if ($column==='bvs_notification') echo esc_html(get_post_meta($id,'_bvs_notification',true)); }, 10, 2);
add_action('admin_menu', function () { add_options_page('BVS enquiries','BVS enquiries','manage_options','bvs-enquiries','bvs_settings'); });
add_action('admin_init', function () {
    register_setting('bvs_enquiries','bvs_recipient',['sanitize_callback'=>'sanitize_email']);
    register_setting('bvs_enquiries','bvs_interface',['sanitize_callback'=>function($value) {
        $clean=[]; foreach (['en','lv'] as $lang) foreach (['menu','skip'] as $key) $clean[$lang][$key]=sanitize_text_field($value[$lang][$key] ?? ''); return $clean;
    }]);
});
function bvs_settings() {
    if (!current_user_can('manage_options')) return;
    $ui=get_option('bvs_interface', []); ?>
    <div class="wrap"><h1>BVS enquiries</h1><p>Quote entries are private and available to administrators. Local capture prevents outbound mail on a Local development site.</p><form action="options.php" method="post"><?php settings_fields('bvs_enquiries'); ?>
    <p><label>Notification recipient <input type="email" class="regular-text" name="bvs_recipient" value="<?php echo esc_attr(get_option('bvs_recipient','')); ?>"></label></p>
    <?php foreach(['en','lv'] as $lang): foreach(['menu','skip'] as $key): ?><p><label><?php echo esc_html(strtoupper($lang).' '.$key.' control label'); ?> <input class="regular-text" name="bvs_interface[<?php echo esc_attr($lang); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($ui[$lang][$key] ?? ''); ?>"></label></p><?php endforeach; endforeach; ?>
    <?php submit_button(); ?></form></div><?php
}

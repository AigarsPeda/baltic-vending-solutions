<?php
/** Add missing cookie widgets without replacing edited content. Local only. */
if (!defined('WP_CLI') || !WP_CLI || !function_exists('bvs_is_local') || !bvs_is_local()) throw new RuntimeException('Cookie setup requires the Local BVS site.');
if (!WP_Block_Type_Registry::get_instance()->is_registered('bvs/cookie-banner')) throw new RuntimeException('Sync the BVS plugin first.');
$cookie_meta = json_decode(file_get_contents(dirname(__DIR__,2).'/plugins/bvs-site/cookie-banner/block.json'),true);
$cookie_defaults = array_map(fn($attribute) => $attribute['default'],$cookie_meta['attributes']);
$cookie_widgets = get_option('widget_block',[]);
$cookie_sidebars = get_option('sidebars_widgets',[]);
$cookie_added = 0;
foreach(['lv','en'] as $cookie_lang) {
    $cookie_privacy = get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_bvs_seed_key','meta_value'=>$cookie_lang.'-privacy','numberposts'=>1]);
    if (!$cookie_privacy) throw new RuntimeException('Missing translated privacy page.');
    $cookie_attrs = $cookie_defaults;
    $cookie_attrs['privacyUrl'] = get_permalink($cookie_privacy[0]->ID);
    if ($cookie_lang==='lv') $cookie_attrs = array_merge($cookie_attrs,[
        'title'=>'Jūsu sīkdatņu izvēle',
        'description'=>'Nepieciešamās sīkdatnes saglabā valodas un sīkdatņu izvēli. Analītika nav obligāta un tiek ieslēgta tikai ar jūsu atļauju. Savu izvēli varat mainīt jebkurā laikā.',
        'rejectLabel'=>'Noraidīt analītiku','acceptLabel'=>'Atļaut analītiku','closeLabel'=>'Aizvērt, nemainot izvēli',
        'settingsLabel'=>'Sīkdatņu iestatījumi','privacyLabel'=>'Privātums un sīkdatnes',
        'storageError'=>'Izvēli neizdevās saglabāt. Pārbaudiet, vai pārlūkā ir atļautas sīkdatnes, un mēģiniet vēlreiz.',
    ]);
    $cookie_sidebar = 'bvs-cookies-'.$cookie_lang;
    $cookie_exists = false;
    foreach($cookie_sidebars[$cookie_sidebar] ?? [] as $cookie_widget_id) {
        $cookie_number = (int) str_replace('block-','',$cookie_widget_id);
        if (str_contains($cookie_widgets[$cookie_number]['content'] ?? '', 'wp:bvs/cookie-banner')) $cookie_exists = true;
    }
    if (!$cookie_exists) {
        $cookie_numbers = array_filter(array_keys($cookie_widgets),'is_int');
        $cookie_number = $cookie_numbers ? max($cookie_numbers)+1 : 2;
        $cookie_widgets[$cookie_number] = ['content'=>'<!-- wp:bvs/cookie-banner '.wp_json_encode($cookie_attrs,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).' /-->'];
        $cookie_sidebars[$cookie_sidebar][] = 'block-'.$cookie_number;
        $cookie_added++;
    }
    // The policy remains a development draft; document the actual choice cookie now.
    if (!str_contains($cookie_privacy[0]->post_content,'bvs-cookie-policy')) {
        $cookie_title = $cookie_lang==='lv' ? 'Sīkdatņu izvēle' : 'Cookie choices';
        $cookie_text = $cookie_lang==='lv'
          ? 'Sīkdatņu izvēli glabājam līdz 180 dienām. Analītika nav obligāta. Izvēli varat mainīt vietnes kājenes sadaļā “Sīkdatņu iestatījumi”. Nepieciešamās sīkdatnes saglabā arī valodas izvēli. Analītika šajā izstrādes versijā nav pievienota.'
          : 'We store your cookie choice for up to 180 days. Analytics is optional. You can change your choice through Cookie settings in the footer. Necessary cookies also remember your language. Analytics is not connected in this development version.';
        $cookie_content = '<!-- wp:group {"className":"bvs-section bvs-cookie-policy","layout":{"type":"constrained"}} --><div class="wp-block-group bvs-section bvs-cookie-policy"><!-- wp:heading --><h2 class="wp-block-heading">'.esc_html($cookie_title).'</h2><!-- /wp:heading --><!-- wp:paragraph --><p>'.esc_html($cookie_text).'</p><!-- /wp:paragraph --></div><!-- /wp:group -->';
        $cookie_result = wp_update_post(wp_slash(['ID'=>$cookie_privacy[0]->ID,'post_content'=>$cookie_privacy[0]->post_content.$cookie_content]),true);
        if (is_wp_error($cookie_result)) throw new RuntimeException($cookie_result->get_error_message());
    }
}
update_option('widget_block',$cookie_widgets);
update_option('sidebars_widgets',$cookie_sidebars);
WP_CLI::success('Added '.$cookie_added.' missing cookie-banner widgets. Existing copy is preserved.');

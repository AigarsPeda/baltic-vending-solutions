<?php
/** Add the editor without reseeding or replacing existing content. Safe to rerun. */
if (!defined('WP_CLI') || !WP_CLI || !bvs_is_local()) throw new RuntimeException('Use this on the BVS Local site only.');
require_once __DIR__.'/design-content.php';
function bvs_editor_append(&$parent, $child) {
    $parent['innerBlocks'][]=$child;
    array_splice($parent['innerContent'], max(0,count($parent['innerContent'])-1), 0, [null]);
}
$ids=[];
foreach(['lv','en'] as $lang) {
    $homes=get_posts(['post_type'=>'page','meta_key'=>'_bvs_seed_key','meta_value'=>$lang.'-home','numberposts'=>1]);
    if(!$homes)throw new RuntimeException('Missing homepage '.$lang);
    $home=$homes[0];$quote=bvs_find_form(parse_blocks($home->post_content),'quote-'.$lang);
    if(!$quote)throw new RuntimeException('Missing native quote form');
    $quote=array_merge($quote,bvs_design_quote_labels($lang),['formId'=>'design-'.$lang]);
    $existing=get_posts(['post_type'=>'page','post_status'=>'any','meta_key'=>'_bvs_seed_key','meta_value'=>$lang.'-design','numberposts'=>1]);
    if($existing) $id=$existing[0]->ID;
    else $id=wp_insert_post(wp_slash(['post_type'=>'page','post_status'=>'publish','post_author'=>$home->post_author,'post_title'=>$lang==='lv'?'Dizaina redaktors':'Design editor','post_name'=>$lang==='lv'?'dizaina-redaktors':'design-editor','post_content'=>bvs_design_page_content($lang,$quote),'meta_input'=>['_bvs_seed_key'=>$lang.'-design']]),true);
    if(is_wp_error($id))throw new RuntimeException($id->get_error_message());
    pll_set_post_language($id,$lang);$ids[$lang]=$id;
}
pll_save_post_translations($ids);
foreach($ids as $lang=>$id){
    $url=get_permalink($id);
    $home=get_posts(['post_type'=>'page','meta_key'=>'_bvs_seed_key','meta_value'=>$lang.'-home','numberposts'=>1])[0];
    $blocks=parse_blocks($home->post_content);
    foreach($blocks as &$block) {
        if(!str_contains($block['attrs']['className']??'','bvs-branding'))continue;
        if(str_contains(serialize_block($block),'bvs-editor-link'))continue;
        $button=parse_blocks(bvs_design_button($lang,$url))[0];
        foreach($block['innerBlocks'] as &$columns)if($columns['blockName']==='core/columns')bvs_editor_append($columns['innerBlocks'][0],$button);
        unset($columns);
    }unset($block);
    wp_update_post(wp_slash(['ID'=>$home->ID,'post_content'=>serialize_blocks($blocks)]));
    $menu=wp_get_nav_menu_object('Primary '.strtoupper($lang));
    if(!$menu)throw new RuntimeException('Missing language menu');
    $items=wp_get_nav_menu_items($menu->term_id);$found=false;
    foreach($items as $item)if((int)$item->object_id===$id){$found=true;$classes=array_filter($item->classes);if(!in_array('bvs-design-menu',$classes,true))update_post_meta($item->ID,'_menu_item_classes',array_merge($classes,['bvs-design-menu']));}
    if(!$found){
        foreach($items as $position=>$item)wp_update_post(['ID'=>$item->ID,'menu_order'=>$position+2]);
        wp_update_nav_menu_item($menu->term_id,0,['menu-item-object-id'=>$id,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-title'=>$lang==='lv'?'Dizaina redaktors':'Design editor','menu-item-position'=>1,'menu-item-classes'=>'bvs-design-menu','menu-item-status'=>'publish']);
    }
}
// Add translated attachment labels to saved forms without changing any existing labels.
function bvs_editor_form_labels(&$blocks,$lang){foreach($blocks as &$b){if($b['blockName']==='bvs/quote-form')foreach(bvs_design_quote_labels($lang) as $key=>$value)if(!isset($b['attrs'][$key]))$b['attrs'][$key]=$value;bvs_editor_form_labels($b['innerBlocks'],$lang);}unset($b);}
foreach(get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>-1]) as $page){$lang=pll_get_post_language($page->ID);$blocks=parse_blocks($page->post_content);bvs_editor_form_labels($blocks,$lang);$new=serialize_blocks($blocks);if($new!==$page->post_content)wp_update_post(wp_slash(['ID'=>$page->ID,'post_content'=>$new]));}
flush_rewrite_rules();
WP_CLI::success('Editor pages '.implode(',',$ids).' linked from both menus and wrapping sections.');

<?php
/** Integration checks. Requires this project's running Local site. Removes its own test records. */
if (!bvs_is_local() || !get_option('bvs_seed_version')) throw new RuntimeException('Seed the Local site first.');
function bvs_assert($condition,$message) { if(!$condition) throw new RuntimeException($message); }
$pages=get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_bvs_seed_key','numberposts'=>-1]);
bvs_assert(count($pages)===10,'Expected ten translated starter pages including the editor.');
foreach($pages as $p) {
 $lang=pll_get_post_language($p->ID); bvs_assert(in_array($lang,['lv','en'],true),'Every starter page has a language.');
 bvs_assert(pll_get_post($p->ID,$lang==='lv' ? 'en' : 'lv'),'Every starter page has a translation.');
 if(!str_contains(get_post_meta($p->ID,'_bvs_seed_key',true),'privacy')) bvs_assert(bvs_find_form(parse_blocks($p->post_content),(str_ends_with(get_post_meta($p->ID,'_bvs_seed_key',true),'-design')?'design-':'quote-').$lang),'The quote block attributes survived storage.');
}
$attachments=get_posts(['post_type'=>'attachment','post_status'=>'inherit','meta_query'=>[['key'=>'_bvs_source','value'=>['compact','fridge'],'compare'=>'IN']],'numberposts'=>-1]);
bvs_assert(count($attachments)===2,'Two proposal attachments.');
foreach($attachments as $p) { bvs_assert(is_file(get_attached_file($p->ID)),'The uploaded media file exists.'); bvs_assert(!empty(wp_get_attachment_metadata($p->ID)['sizes']),'Native generated media sizes exist.'); }
$home=pll_get_post(get_option('page_on_front'),'en');
$data=['page_id'=>(string)$home,'form_id'=>'quote-en','bvs_nonce'=>wp_create_nonce('bvs_quote'),'website'=>'','name'=>'BVS integration test','company'=>'Example','email'=>'test@example.invalid','phone'=>'','model'=>'compact','mode'=>'rent','products'=>'Packaged meals','location'=>'Test location','message'=>'Synthetic data, delete after test'];
$ids=[];
try {
 $result=bvs_save_quote($data); bvs_assert(!is_wp_error($result),'Valid enquiry saved.'); $ids[]=$result['id'];
 bvs_assert(get_post($result['id'])->post_status==='private','Enquiry is private.');
 bvs_assert(get_post_meta($result['id'],'_bvs_notification',true)==='local_captured','Local captured the notification without sending.');
 $fail=fn()=>false; add_filter('pre_wp_mail',$fail,PHP_INT_MAX);
 $result=bvs_save_quote($data); remove_filter('pre_wp_mail',$fail,PHP_INT_MAX);
 bvs_assert(!is_wp_error($result),'Failed notification still reports successful save.'); $ids[]=$result['id'];
 bvs_assert(get_post_meta($result['id'],'_bvs_notification',true)==='failed','Notification failure is recorded separately.');
 bvs_assert($result['message']==='Your enquiry has been saved.','Correct saved feedback on mail failure.');
 $bad=$data; $bad['email']='not-an-email'; bvs_assert(is_wp_error(bvs_save_quote($bad)),'Invalid email rejected.');
 $bad=$data; $bad['website']='spam'; bvs_assert(is_wp_error(bvs_save_quote($bad)),'Honeypot rejected.');
 $bad=$data; $bad['bvs_nonce']='invalid'; bvs_assert(is_wp_error(bvs_save_quote($bad)),'Invalid nonce rejected.');
 $bad=$data; $bad['model']=['malformed']; bvs_assert(is_wp_error(bvs_save_quote($bad)),'Array payload rejected.');
 // The type is neither publicly queryable nor REST-exposed.
 $type=get_post_type_object('bvs_enquiry'); bvs_assert(!$type->public && !$type->show_in_rest,'No public enquiry endpoint.');
} finally {
 foreach($ids as $id) wp_delete_post($id,true);
 $key='bvs_rate_'.hash_hmac('sha256',$_SERVER['REMOTE_ADDR'] ?? 'unknown',wp_salt()); delete_transient($key);
}
WP_CLI::success('Translations, native attachments, private enquiries, local capture, failed mail, validation and spam checks passed.');

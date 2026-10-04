<?php
if(!bvs_is_local())throw new RuntimeException('Local only');
$entries=get_posts(['post_type'=>'bvs_enquiry','post_status'=>'private','numberposts'=>-1]);$found=[];
foreach($entries as $entry){$fields=get_post_meta($entry->ID,'_bvs_fields',true);if(($fields['name']??'')!=='BVS design verification')continue;$design=get_post_meta($entry->ID,'_bvs_design',true);if(!$design||$design['width']!==1880||$design['height']!==1100)throw new RuntimeException('Missing exported attachment');if(!str_starts_with(file_get_contents(bvs_design_file($design)),"\x89PNG\r\n\x1a\n"))throw new RuntimeException('Not PNG');if(get_post_meta($entry->ID,'_bvs_notification',true)!=='local_captured')throw new RuntimeException('Local mail capture not active');$found[]=$entry->ID;}
if(count($found)!==1)throw new RuntimeException('Expected exactly one synthetic saved enquiry');
file_put_contents('/private/tmp/bvs-design-test-id.json',wp_json_encode(['id'=>$found[0]]));
WP_CLI::success('Private PNG outside the web root, local notification captured, no public attachment created.');

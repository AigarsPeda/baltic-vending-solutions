<?php
/** Normalised raster files live outside the web root, reached only by an admin download. */
defined('ABSPATH') || exit;
function bvs_design_directory() {
    return defined('BVS_PRIVATE_DESIGN_DIR') ? BVS_PRIVATE_DESIGN_DIR : dirname(untrailingslashit(ABSPATH)).'/bvs-private-designs';
}
function bvs_design_file($design) {
    if (!is_array($design) || !preg_match('/^[a-f0-9-]{36}\.png$/', $design['file'] ?? '')) return '';
    return bvs_design_directory().'/'.$design['file'];
}
function bvs_store_design($design, $message) {
    $directory = bvs_design_directory();
    if (!is_dir($directory) && !wp_mkdir_p($directory)) return new WP_Error('design_save', $message);
    $real = wp_normalize_path(realpath($directory));
    foreach (array_filter([realpath(ABSPATH), realpath($_SERVER['DOCUMENT_ROOT'] ?? ABSPATH)]) as $root) {
        $root = wp_normalize_path($root);
        if ($real === $root || str_starts_with($real, trailingslashit($root))) return new WP_Error('design_directory', $message);
    }
    if (!chmod($directory, 0700)) return new WP_Error('design_save', $message);
    $design['file'] = wp_generate_uuid4().'.png';
    $path = bvs_design_file($design);
    $handle = @fopen($path, 'xb');
    if (!$handle) return new WP_Error('design_save', $message);
    $bytes = base64_decode($design['data'], true);
    $written = fwrite($handle, $bytes);fclose($handle);
    if ($written !== strlen($bytes) || !chmod($path, 0600)) { wp_delete_file($path);return new WP_Error('design_save', $message); }
    unset($design['data']);
    return $design;
}
function bvs_validate_design_attachment($upload, $message) {
    if ($upload === null) return null;
    if (!is_array($upload) || !isset($upload['error']) || !is_int($upload['error'])) return new WP_Error('design_invalid', $message);
    if ($upload['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($upload['error'] !== UPLOAD_ERR_OK || !isset($upload['tmp_name'], $upload['size']) || !is_string($upload['tmp_name']) || !is_numeric($upload['size']) || $upload['size'] < 1 || $upload['size'] > 5*1024*1024 || !is_uploaded_file($upload['tmp_name'])) return new WP_Error('design_invalid', $message);
    // Verify bytes and decode, regardless of the supplied filename or browser MIME.
    $info = @getimagesize($upload['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_PNG,IMAGETYPE_JPEG,IMAGETYPE_WEBP], true) || $info[0]>4096 || $info[1]>4096 || $info[0]*$info[1]>16777216 || !function_exists('imagecreatefromstring')) return new WP_Error('design_invalid', $message);
    $bytes = file_get_contents($upload['tmp_name']);
    if (!$bytes || strlen($bytes)>5*1024*1024) return new WP_Error('design_invalid', $message);
    wp_raise_memory_limit('image');
    $memory = ini_get('memory_limit');
    if ($memory !== '-1' && memory_get_usage(true)+$info[0]*$info[1]*8+32*1024*1024 > wp_convert_hr_to_bytes($memory)) return new WP_Error('design_invalid', $message);
    $image = @imagecreatefromstring($bytes);
    if (!$image) return new WP_Error('design_invalid', $message);
    imagesavealpha($image, true);
    ob_start();$saved = imagepng($image, null, 6);$png=ob_get_clean();imagedestroy($image);
    if (!$saved || !$png || strlen($png)>5*1024*1024) return new WP_Error('design_invalid', $message);
    // Re-encoding strips original metadata and appended non-image content.
    return ['name'=>'bvs-design.png','mime'=>'image/png','width'=>$info[0],'height'=>$info[1],'data'=>base64_encode($png)];
}
function bvs_download_design() {
    $id = absint($_GET['enquiry'] ?? 0);
    if (!current_user_can('manage_options') || get_post_type($id)!=='bvs_enquiry') wp_die('Access denied.', '', ['response'=>403]);
    check_admin_referer('bvs_design_'.$id);
    $design = get_post_meta($id, '_bvs_design', true);
    $path = bvs_design_file($design);
    $bytes = $path && is_file($path) ? file_get_contents($path) : false;
    if (!$bytes) wp_die('No design attachment.', '', ['response'=>404]);
    nocache_headers();header('Content-Type: image/png');header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="bvs-design-'.$id.'.png"');
    header('Content-Length: '.strlen($bytes));echo $bytes;exit;
}
add_action('admin_post_bvs_download_design', 'bvs_download_design');
add_action('admin_post_nopriv_bvs_download_design', 'bvs_download_design');
add_action('before_delete_post', function ($id) {
    if (get_post_type($id) !== 'bvs_enquiry') return;
    $path = bvs_design_file(get_post_meta($id, '_bvs_design', true));
    if ($path && is_file($path)) wp_delete_file($path);
});

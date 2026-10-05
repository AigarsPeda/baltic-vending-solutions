<?php
/** Three editable visual wrap panels. Draft artwork never leaves the browser. */
defined('ABSPATH') || exit;
function bvs_design_page_url($lang) {
    $pages = get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_bvs_seed_key','meta_value'=>$lang.'-design','numberposts'=>1]);
    return $pages ? get_permalink($pages[0]->ID) : '';
}
add_action('init', function () {
    $dir = __DIR__.'/design-editor';
    wp_register_script('bvs-design-admin', plugins_url('design-editor/editor.js', __FILE__), ['wp-blocks','wp-element','wp-block-editor','wp-components'], filemtime($dir.'/editor.js'), true);
    wp_add_inline_script('bvs-design-admin', 'window.bvsDesignMetadata = '.wp_json_encode(json_decode(file_get_contents($dir.'/block.json'), true)).';', 'before');
    wp_register_script('bvs-model-viewer', plugins_url('design-editor/vendor/model-viewer-4.2.0.min.js', __FILE__), [], '4.2.0', true);
    wp_register_script('bvs-design-view', plugins_url('design-editor/view.js', __FILE__), ['bvs-model-viewer'], filemtime($dir.'/view.js'), true);
    wp_register_style('bvs-design-style', plugins_url('design-editor/editor.css', __FILE__), [], filemtime($dir.'/editor.css'));
    register_block_type($dir, ['render_callback'=>'bvs_render_design_editor']);
});
add_filter('script_loader_tag', function ($tag, $handle) {
    return $handle === 'bvs-model-viewer' ? str_replace('<script ', '<script type="module" ', $tag) : $tag;
}, 10, 2);
function bvs_render_design_editor($attributes) {
    $meta = json_decode(file_get_contents(__DIR__.'/design-editor/block.json'), true);
    $a = array_merge(array_map(fn($value)=>$value['default'], $meta['attributes']), $attributes);
    $id = wp_unique_id('bvs-design-');
    $palette = array_column(wp_get_global_settings(['color', 'palette', 'theme']), 'color', 'slug');
    $default_colour = $palette['accent'] ?? '#ffffff';
    $model_url = add_query_arg('ver', filemtime(__DIR__.'/design-editor/assets/smart-fridge-design.glb'), plugins_url('design-editor/assets/smart-fridge-design.glb', __FILE__));
    $label = fn($key)=>esc_html($a[$key]);
    $range = function ($key,$min,$max,$value) use ($label,$id) {
        echo '<label class="bvs-design-range" for="'.esc_attr($id.$key).'">'.$label($key.'Label').'<input id="'.esc_attr($id.$key).'" type="range" data-control="'.$key.'" min="'.$min.'" max="'.$max.'" value="'.$value.'"><output>'.$value.'</output></label>';
    };
    ob_start(); ?>
    <!-- THESIS: See your branding on the actual machine while editing flat panels.
    OWN-WORLD: Existing BVS Plex, shared brand controls, white tools and cool neutral preview.
    STORY: Select panel, upload or draw, inspect in 3D, download or send with an enquiry.
    FIRST VIEWPORT: Compact tools at left, large machine and flat panel at right, export below.
    FORM: Operate workbench, extension of the existing BVS world, seed bvs-wrap-editor.
    FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, and DESIGN.md -->
    <section id="<?php echo esc_attr($id); ?>" class="bvs-design-editor" data-labels="<?php echo esc_attr(wp_json_encode($a)); ?>" aria-label="<?php echo esc_attr($a['previewLabel']); ?>">
      <noscript><p><?php echo $label('noScriptMessage'); ?></p></noscript>
      <div class="bvs-design-workbench" hidden>
        <div class="bvs-design-tools">
          <fieldset><legend><?php echo $label('panelLabel'); ?></legend><div class="bvs-design-panels">
            <?php foreach (['front','left','right'] as $panel): ?><button type="button" data-panel="<?php echo $panel; ?>" aria-pressed="<?php echo $panel==='front'?'true':'false'; ?>" aria-label="<?php echo esc_attr($a[$panel.'Label']); ?>" title="<?php echo esc_attr($a[$panel.'Label']); ?>"><?php echo $label($panel.'ShortLabel'); ?></button><?php endforeach; ?>
          </div></fieldset>
          <fieldset><legend><?php echo $label('colourLabel'); ?></legend><div class="bvs-design-colour">
            <input type="color" data-control="colour" value="<?php echo esc_attr($default_colour); ?>" aria-label="<?php echo esc_attr($a['colourLabel']); ?>">
            <input type="text" data-control="hex" value="<?php echo esc_attr($default_colour); ?>" maxlength="7" pattern="#[a-fA-F0-9]{6}" aria-label="<?php echo esc_attr($a['colourLabel'].' HEX'); ?>" spellcheck="false">
          </div><button type="button" class="bvs-design-text" data-action="colour-all"><?php echo $label('colourAllLabel'); ?></button></fieldset>
          <?php foreach (['artwork','logo'] as $kind): ?><fieldset><legend><?php echo $label($kind.'Label'); ?></legend>
            <input type="file" data-upload="<?php echo $kind; ?>" accept="image/png,image/jpeg,image/webp" aria-label="<?php echo esc_attr($a[$kind.'Label']); ?>" aria-describedby="<?php echo esc_attr($id.$kind.'-hint'); ?>">
            <p class="bvs-design-file-name" data-file-label="<?php echo $kind; ?>" hidden></p><p class="bvs-design-hint" id="<?php echo esc_attr($id.$kind.'-hint'); ?>"><?php echo $label($kind.'Hint'); ?></p>
            <button type="button" data-action="remove-<?php echo $kind; ?>" class="bvs-design-text" hidden><?php echo $label('remove'.ucfirst($kind).'Label'); ?></button>
            <?php if ($kind==='artwork'): ?><label class="bvs-design-fit" hidden><?php echo $label('fitLabel'); ?><select data-control="fit"><option value="cover"><?php echo $label('coverLabel'); ?></option><option value="contain"><?php echo $label('containLabel'); ?></option></select></label><?php endif; ?>
            <?php if ($kind==='logo'): ?><div class="bvs-design-logo-controls" hidden><?php $range('size',5,100,22); $range('x',0,100,17); $range('y',0,100,6); $range('rotation',-180,180,0); ?></div><?php endif; ?>
          </fieldset><?php endforeach; ?>
          <details class="bvs-design-drawing"><summary><?php echo $label('drawingLabel'); ?></summary>
            <label class="bvs-design-field"><?php echo $label('drawingToolLabel'); ?><select data-control="drawingTool">
              <?php foreach (['raw','smooth','line','rectangle','ellipse','text','erase'] as $kind): ?><option value="<?php echo $kind; ?>"><?php echo $label($kind==='text'?'textToolLabel':$kind.'Label'); ?></option><?php endforeach; ?>
            </select></label>
            <div class="bvs-design-modes"><button type="button" data-tool="draw" aria-pressed="false"><?php echo $label('drawLabel'); ?></button></div>
            <div class="bvs-design-text-controls" hidden><label class="bvs-design-field"><?php echo $label('textLabel'); ?><input type="text" data-control="text" maxlength="100" autocomplete="off"></label><?php $range('textSize',12,120,48); ?><p class="bvs-design-hint"><?php echo $label('textHint'); ?></p></div>
            <label class="bvs-design-fill" hidden><input type="checkbox" data-control="shapeFill"> <?php echo $label('shapeFillLabel'); ?></label>
            <label class="bvs-design-brush"><?php echo $label('brushColourLabel'); ?><input type="color" data-control="brushColour" value="#ffffff"></label>
            <?php $range('brushSize',2,60,12); ?>
            <p class="bvs-design-hint" data-drawing-hint><?php echo $label('drawingHint'); ?></p>
            <p class="bvs-design-hint" data-eraser-hint hidden><?php echo $label('eraseHint'); ?></p>
            <button type="button" data-action="clear" class="bvs-design-text"><?php echo $label('clearLabel'); ?></button>
          </details>
        </div>
        <div class="bvs-design-stage">
          <div class="bvs-design-stage-heading"><h2><?php echo $label('previewLabel'); ?></h2><button type="button" data-action="preview-switch" hidden><?php echo $label('showFlatViewLabel'); ?></button></div>
          <div class="bvs-design-previews">
            <div class="bvs-design-machine">
              <div class="bvs-design-machine-view">
              <div class="bvs-design-preview-tools">
                <button type="button" data-action="view" class="bvs-design-reset" aria-label="<?php echo esc_attr($a['resetViewLabel']); ?>" title="<?php echo esc_attr($a['resetViewLabel']); ?>"><span class="bvs-design-reset-label"><?php echo $label('resetViewLabel'); ?></span><svg class="bvs-design-reset-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 11a9 9 0 1 1 2.64 6.36M3 4v7h7"/></svg></button>
                <div class="bvs-design-view-modes"><button type="button" data-preview-mode="rotate" aria-pressed="true"><?php echo $label('rotateModelLabel'); ?></button><button type="button" data-preview-mode="draw" aria-pressed="false" disabled><?php echo $label('drawModelLabel'); ?></button></div>
                <div class="bvs-design-history">
                  <button type="button" data-action="eraser" aria-pressed="false" aria-label="<?php echo esc_attr($a['eraseLabel']); ?>" title="<?php echo esc_attr($a['eraseLabel']); ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m16 3 5 5a2 2 0 0 1 0 3L11 21H6l-3-3a2 2 0 0 1 0-3L13 3a2 2 0 0 1 3 0ZM8 10l7 7M11 21h10"/></svg></button>
                  <?php foreach (['undo','redo'] as $action): ?><button type="button" data-action="<?php echo $action; ?>" aria-label="<?php echo esc_attr($a[$action.'Label']); ?>" title="<?php echo esc_attr($a[$action.'Label']); ?>" disabled><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?php if ($action==='undo'): ?><path d="M9 5 4 10l5 5M4 10h10a6 6 0 0 1 0 12" transform="translate(0 -2)"/><?php else: ?><path d="m15 5 5 5-5 5M20 10H10a6 6 0 0 0 0 12" transform="translate(0 -2)"/><?php endif; ?></svg></button><?php endforeach; ?>
                </div>
              </div>
              <div class="bvs-design-model"><model-viewer src="<?php echo esc_url($model_url); ?>" alt="<?php echo esc_attr($a['previewLabel']); ?>" poster="<?php echo esc_url(plugins_url('design-editor/assets/smart-fridge-neutral.png', __FILE__)); ?>" camera-controls interaction-prompt="none" camera-orbit="25deg 90deg 4.74m" min-camera-orbit="auto auto 1.58m" max-camera-orbit="auto auto 6.32m" camera-target="0m .965m 0m" field-of-view="30deg" shadow-intensity="0.7" environment-image="neutral" exposure="1" loading="eager" touch-action="pan-y"><span slot="progress-bar" hidden></span></model-viewer><img class="bvs-design-poster" src="<?php echo esc_url(plugins_url('design-editor/assets/smart-fridge-neutral.png', __FILE__)); ?>" alt="<?php echo esc_attr($a['previewLabel']); ?>"></div>
              </div>
              <div class="bvs-design-zoom" aria-label="<?php echo esc_attr($a['modelZoomLabel']); ?>"><button type="button" data-zoom="model-out" aria-label="<?php echo esc_attr($a['zoomOutLabel']); ?>">−</button><output data-model-zoom>100%</output><button type="button" data-zoom="model-in" aria-label="<?php echo esc_attr($a['zoomInLabel']); ?>">+</button></div><p class="bvs-design-hint"><?php echo $label('previewHint'); ?></p></div>
            <div class="bvs-design-flat"><div class="bvs-design-flat-view"><h3><?php echo $label('canvasLabel'); ?></h3><div class="bvs-design-canvas-wrap">
              <canvas width="592" height="1024" tabindex="0" aria-label="<?php echo esc_attr($a['canvasLabel']); ?>"></canvas>
              <svg class="bvs-design-mask" viewBox="0 0 592 1024" aria-hidden="true" hidden><path d="M198 42H592V988H198Z M11 120H193V447H11Z M23 462H103V594H23Z M0 641L86 720V1024H0Z"/></svg>
            </div></div><div class="bvs-design-zoom" aria-label="<?php echo esc_attr($a['flatZoomLabel']); ?>"><button type="button" data-zoom="flat-out" aria-label="<?php echo esc_attr($a['zoomOutLabel']); ?>">−</button><output data-flat-zoom>100%</output><button type="button" data-action="pan-panel" aria-pressed="false" hidden><?php echo $label('panPanelLabel'); ?></button><button type="button" data-zoom="flat-in" aria-label="<?php echo esc_attr($a['zoomInLabel']); ?>">+</button></div><p class="bvs-design-hint"><?php echo $label('canvasHint'); ?></p></div>
          </div>
          <p class="bvs-design-status" role="status" aria-live="polite"><?php echo $label('loadingMessage'); ?></p>
          <div class="bvs-design-actions"><button type="button" data-action="attach" class="bvs-design-primary"><?php echo $label('attachLabel'); ?></button><button type="button" data-action="download"><?php echo $label('downloadLabel'); ?></button><button type="button" data-action="reset" class="bvs-design-danger"><?php echo $label('resetLabel'); ?></button></div>
          <p class="bvs-design-hint bvs-design-production"><?php echo $label('productionNote'); ?></p>
        </div>
      </div>
      <p class="bvs-design-save-status bvs-design-hint" role="status" aria-live="polite" data-state="loading"><?php echo $label('draftLoadingMessage'); ?></p>
      <p class="bvs-design-draft"><?php echo $label('draftNote'); ?></p>
    </section>
    <?php return ob_get_clean();
}

<?php
/** Native starter content for the wrapping editor. Existing saved pages stay authoritative. */
function bvs_design_labels($lang) {
    if ($lang !== 'lv') return ['language'=>'en'];
    return [
        'language'=>'lv','panelLabel'=>'Izvēlieties paneli','frontLabel'=>'Priekšpuse','leftLabel'=>'Kreisais sāns','rightLabel'=>'Labais sāns','frontShortLabel'=>'Priekša','leftShortLabel'=>'Kreisais','rightShortLabel'=>'Labais',
        'colourLabel'=>'Paneļa krāsa','colourAllLabel'=>'Lietot krāsu visiem paneļiem','artworkLabel'=>'Fona attēls','artworkHint'=>'PNG, JPG vai WebP līdz 5 MB. Attēlu uzklāj izvēlētajam panelim.',
        'logoLabel'=>'Jūsu logotips','logoHint'=>'Logotipam bez fona izmantojiet caurspīdīgu PNG.','removeArtworkLabel'=>'Noņemt fona attēlu','removeLogoLabel'=>'Noņemt logotipu',
        'fitLabel'=>'Attēla izvietojums','coverLabel'=>'Aizpildīt paneli','containLabel'=>'Ietilpināt visu attēlu','sizeLabel'=>'Logotipa platums (%)','xLabel'=>'Horizontālā pozīcija (%)','yLabel'=>'Vertikālā pozīcija (%)','rotationLabel'=>'Logotipa pagrieziens (°)',
        'modelViewLabel'=>'3D iekārta','flatViewLabel'=>'2D panelis','showFlatViewLabel'=>'2D skats','showModelViewLabel'=>'3D skats','panPanelLabel'=>'Pārvietot tuvināto paneli','stopPanPanelLabel'=>'Turpināt rediģēt','eraseLabel'=>'Dzēšgumija','eraseActionLabel'=>'Dzēst objektus','stopEraseLabel'=>'Beigt dzēst','eraseModelLabel'=>'Dzēst uz iekārtas','eraseHint'=>'3D skatā izvēlieties “Dzēst uz iekārtas”. Noklikšķiniet uz zīmējuma, figūras, teksta vai logotipa, lai noņemtu virsējo objektu. Ar Atcelt to var atjaunot.','drawingToolLabel'=>'Rīks','rawLabel'=>'Brīvā roka: precīzi','smoothLabel'=>'Brīvā roka: izlīdzināti','lineLabel'=>'Taisna līnija','rectangleLabel'=>'Taisnstūris','ellipseLabel'=>'Elipse','textToolLabel'=>'Teksts','shapeFillLabel'=>'Aizpildīt figūras','textLabel'=>'Jūsu teksts','textSizeLabel'=>'Teksta izmērs','textHint'=>'Ievadiet tekstu un noklikšķiniet uz paneļa vai iekārtas, lai to novietotu.','drawingHint'=>'Velciet, lai zīmētu. Izlīdzinātais režīms iztaisno gandrīz taisnas līnijas un nogludina līknes. Turiet Shift, lai zīmētu kvadrātu vai apli.','rotateModelLabel'=>'Pagriezt iekārtu','drawModelLabel'=>'Zīmēt uz iekārtas','zoomInLabel'=>'Tuvināt','zoomOutLabel'=>'Tālināt','flatZoomLabel'=>'Paneļa tuvinājums','modelZoomLabel'=>'Iekārtas tuvinājums','drawingLimitMessage'=>'Panelis ir pilns. Atceliet vai notīriet daļu zīmējuma, lai turpinātu.',
        'drawingLabel'=>'Zīmēšanas rīki','drawLabel'=>'Zīmēt','stopDrawLabel'=>'Beigt zīmēt','brushColourLabel'=>'Otas krāsa','brushSizeLabel'=>'Otas izmērs','undoLabel'=>'Atcelt','redoLabel'=>'Atkārtot','clearLabel'=>'Notīrīt zīmējumu',
        'canvasLabel'=>'Rediģējiet izvēlēto paneli','canvasHint'=>'Zīmējiet šeit vai velciet logotipu, kad zīmēšana ir izslēgta. Caurspīdīgās zonas nevar aplīmēt.',
        'previewLabel'=>'Smart Fridge priekšskatījums','previewHint'=>'Pagrieziet iekārtu un izvēlieties “Zīmēt uz iekārtas”. Rediģēt var tikai aplīmējamos paneļus.','resetViewLabel'=>'Atjaunot skatu',
        'downloadLabel'=>'Lejupielādēt dizainu','attachLabel'=>'Pieprasīt piedāvājumu ar šo dizainu','resetLabel'=>'Sākt no jauna','resetConfirm'=>'Notīrīt visus trīs paneļus un dzēst šajā pārlūkā saglabāto dizainu?',
        'loadingMessage'=>'Ielādējam 3D iekārtu…','readyMessage'=>'3D priekšskatījums ir gatavs.','fallbackMessage'=>'Šajā pārlūkā 3D nav pieejams. Jūs joprojām varat rediģēt, lejupielādēt un nosūtīt plakano dizainu.',
        'fileError'=>'Izvēlieties PNG, JPG vai WebP līdz 5 MB un 4096 pikseļiem katrā malā.','exportError'=>'Failu neizdevās sagatavot. Mēģiniet vēlreiz.',
        'attachedMessage'=>'Dizains ir pievienots formai zemāk. Aizpildiet savu informāciju, lai to nosūtītu.','downloadMessage'=>'Dizaina fails ir gatavs.',
        'draftNote'=>'Jūsu dizains tiek automātiski saglabāts šajā pārlūkā. Atgriezieties, lai turpinātu. Poga “Sākt no jauna” dzēš saglabāto dizainu. To dzēš arī pārlūka datu notīrīšana.',
        'draftLoadingMessage'=>'Meklējam saglabātu dizainu…','draftEmptyMessage'=>'Jūsu izmaiņas tiks saglabātas šajā pārlūkā.','draftSavingMessage'=>'Saglabājam dizainu…','draftSavedMessage'=>'Dizains ir saglabāts šajā pārlūkā.','draftRestoredMessage'=>'Saglabātais dizains ir atjaunots. Turpiniet iesākto.','draftErrorMessage'=>'Saglabāšana pārlūkā nav pieejama. Pirms aiziešanas atstājiet šo lapu atvērtu vai lejupielādējiet dizainu.','draftClearedMessage'=>'Saglabātais dizains ir dzēsts.',
        'productionNote'=>'Šī ir dizaina vizualizācija. Pirms izgatavošanas ar jums saskaņojam izmērus, drukas failus un aplīmēšanas apjomu.',
        'downloadTitle'=>'Smart Fridge aplīmēšanas dizains','exportNote'=>'Dizaina vizualizācija • nav drukas šablons','noScriptMessage'=>'Lai lietotu dizaina redaktoru, ieslēdziet JavaScript. Savu attēlu varat pievienot arī formai zemāk.',
    ];
}
function bvs_design_quote_labels($lang) {
    return $lang==='lv' ? ['designLabel'=>'Pievienot dizainu (nav obligāts)','designHint'=>'PNG, JPG vai WebP līdz 5 MB.','designError'=>'Pievienojiet derīgu PNG, JPG vai WebP līdz 5 MB un 4096 pikseļiem katrā malā.','designAttached'=>'Dizains pievienots','designRemove'=>'Noņemt dizainu','designEditorLabel'=>'Pievienot manu pašreizējo redaktora dizainu','designEditorHint'=>'Nosūtot formu, jūsu jaunākais dizains tiks pievienots automātiski. Tas nav jālejupielādē vai jāaugšupielādē.'] : [];
}
function bvs_design_block($name,$attrs,$html) {
    return '<!-- wp:'.$name.($attrs?' '.wp_json_encode($attrs,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):'').' -->'.$html.'<!-- /wp:'.$name.' -->';
}
function bvs_design_button($lang,$url) {
    $label = $lang==='lv'?'Izmēģināt savu dizainu':'Try your design';
    $button = bvs_design_block('button',['className'=>'bvs-editor-link'],'<div class="wp-block-button bvs-editor-link"><a class="wp-block-button__link wp-element-button" href="'.esc_url($url).'">'.esc_html($label).'</a></div>');
    return bvs_design_block('buttons',[],'<div class="wp-block-buttons">'.$button.'</div>');
}
function bvs_design_page_content($lang, $quote) {
    $title = $lang==='lv'?'Izmēģiniet savu dizainu uz iekārtas':'Try your design on the machine';
    $intro = $lang==='lv'?'Izvēlieties krāsas, pievienojiet attēlu un logotipu. Apskatiet rezultātu 3D, lejupielādējiet vai nosūtiet mums kopā ar pieprasījumu.':'Choose colours, add artwork and a logo. See the result in 3D, download it or send it to us with your enquiry.';
    $content = bvs_design_block('heading',['level'=>1],'<h1 class="wp-block-heading">'.esc_html($title).'</h1>')
        .bvs_design_block('paragraph',['className'=>'bvs-lead'],'<p class="bvs-lead">'.esc_html($intro).'</p>')
        .'<!-- wp:bvs/design-editor '.wp_json_encode(bvs_design_labels($lang),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).' /-->';
    $page = bvs_design_block('group',['className'=>'bvs-design-page','layout'=>['type'=>'default']],'<div class="wp-block-group bvs-design-page">'.$content.'</div>');
    $heading = $lang==='lv'?'Saņemiet piedāvājumu savam dizainam':'Get a quote for your design';
    $text = $lang==='lv'?'Redaktorā izveidotais dizains tiks pievienots automātiski, nosūtot formu. Varat arī izvēlēties savu failu. Tas palīdzēs mums sagatavot jūsu iekārtas un aplīmēšanas piedāvājumu.':'Your editor design will be attached automatically when you submit the form. You can also choose your own file. We will use it to prepare your equipment and wrapping quote.';
    $contact = bvs_design_block('heading',[],'<h2 class="wp-block-heading">'.esc_html($heading).'</h2>').bvs_design_block('paragraph',['className'=>'bvs-lead'],'<p class="bvs-lead">'.esc_html($text).'</p>');
    $left=bvs_design_block('column',[],'<div class="wp-block-column">'.$contact.'</div>');
    $right=bvs_design_block('column',[],'<div class="wp-block-column"><!-- wp:bvs/quote-form '.wp_json_encode($quote,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).' /--></div>');
    $columns=bvs_design_block('columns',[],'<div class="wp-block-columns">'.$left.$right.'</div>');
    return $page.bvs_design_block('group',['className'=>'bvs-section bvs-contact bvs-surface','anchor'=>'design-quote','layout'=>['type'=>'constrained']],'<div id="design-quote" class="wp-block-group bvs-section bvs-contact bvs-surface">'.$columns.'</div>');
}

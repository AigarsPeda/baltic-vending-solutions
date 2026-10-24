<?php
/** Native homepage blocks. Used once during setup; WordPress owns all later edits. */
function bvs_home_block($name, $attrs, $html) {
    return '<!-- wp:'.$name.($attrs ? ' '.wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '').' -->'.$html.'<!-- /wp:'.$name.' -->';
}
function bvs_home_heading($text, $level = 2) {
    return bvs_home_block('heading', $level === 2 ? [] : ['level'=>$level], '<h'.$level.' class="wp-block-heading">'.esc_html($text).'</h'.$level.'>');
}
function bvs_home_paragraph($text, $class = '') {
    return bvs_home_block('paragraph', $class ? ['className'=>$class] : [], '<p'.($class ? ' class="'.esc_attr($class).'"' : '').'>'.esc_html($text).'</p>');
}
function bvs_home_group($content, $class, $anchor = '') {
    return bvs_home_block('group', array_filter(['className'=>$class, 'anchor'=>$anchor, 'layout'=>['type'=>str_contains($class, 'bvs-section') ? 'constrained' : 'default']]), '<div'.($anchor ? ' id="'.esc_attr($anchor).'"' : '').' class="wp-block-group '.esc_attr($class).'">'.$content.'</div>');
}
function bvs_home_columns($columns, $class = '') {
    $html = '';
    foreach ($columns as $column) $html .= bvs_home_block('column', [], '<div class="wp-block-column">'.$column.'</div>');
    return bvs_home_block('columns', $class ? ['className'=>$class] : [], '<div class="wp-block-columns'.($class ? ' '.esc_attr($class) : '').'">'.$html.'</div>');
}
function bvs_home_audience($lang) {
    $copy = $lang === 'lv' ? [
        ['Paplašiniet produktu tirdzniecību', 'Pārdodiet savas iepakotās maltītes, uzkodas vai dzērienus arī pašapkalpošanās iekārtā.', 'Salīdzināt iekārtas', '#equipment'],
        ['Plānojiet pirmo tirdzniecības vietu', 'Pastāstiet par iecerētajiem produktiem un atrašanās vietu. Palīdzēsim izvēlēties piemērotu iekārtu.', 'Pārrunāt ieceri', '#quote'],
    ] : [
        ['Take your products to a new location', 'Sell your packaged meals, snacks or drinks through a self-service machine.', 'Compare machines', '#equipment'],
        ['Plan your first sales point', 'Tell us about your planned products and location. We will help you choose a suitable machine.', 'Discuss your idea', '#quote'],
    ];
    $layout = '';
    foreach ($copy as [$title, $description, $label, $href]) {
        $link = bvs_home_block('paragraph', ['className'=>'bvs-audience-link'], '<p class="bvs-audience-link"><a href="'.esc_attr($href).'">'.esc_html($label).'</a></p>');
        $layout .= bvs_home_group(bvs_home_heading($title).bvs_home_paragraph($description).$link, 'bvs-audience-item');
    }
    return bvs_home_group(bvs_home_group($layout, 'bvs-audience-layout'), 'bvs-section bvs-audience', 'who-its-for');
}
function bvs_home_comparison_note($lang) {
    $copy = $lang === 'lv' ? [
        'Produktu skaits iekārtā ir atkarīgs no iepakojuma izmēriem un plauktu izkārtojuma.',
        'Pirms pasūtījuma palīdzēsim izvēlēties jūsu produktiem un vietai piemērotu modeli.',
    ] : [
        'How many products fit depends on package sizes and shelf layout.',
        'Before you order, we will help you choose a model for your products and location.',
    ];
    return bvs_home_group(bvs_home_paragraph($copy[0]).bvs_home_paragraph($copy[1]), 'bvs-comparison-note');
}
function bvs_home_commercial($lang) {
    $copy = $lang === 'lv' ? [
        'title'=>'Iegādāties vai nomāt?',
        'options'=>[
            ['Iegāde', 'Jūs nopērkat iekārtu, un tā kļūst par jūsu uzņēmuma īpašumu.'],
            ['Noma', 'Jūs maksājat par iekārtas lietošanu, to neiegādājoties. Nomas pieejamību un termiņu saskaņojam individuāli.'],
        ],
        'planning'=>'Pirms iegādes vai nomas',
        'tasks'=>[
            ['Vieta un pieslēgumi', 'Pārbaudām iekārtas izmērus, piekļuvi piegādei, elektrību un internetu.'],
            ['Ikdienas apkalpošana', 'Vienojamies, kurš papildinās produktus, pārbaudīs derīguma termiņus un tīrīs iekārtu.'],
            ['Izmaksas un pakalpojumi', 'Pirms pasūtījuma precizējam, kas iekļauts piedāvājumā un par ko jāmaksā atsevišķi.'],
        ],
        'quote'=>'Pieprasīt piedāvājumu',
    ] : [
        'title'=>'Buy or rent?',
        'options'=>[
            ['Purchase', 'You buy the machine, and your business owns it.'],
            ['Rental', 'You pay to use the machine without buying it. Rental availability and duration are agreed individually.'],
        ],
        'planning'=>'Before you buy or rent',
        'tasks'=>[
            ['Location and connections', 'We check the machine’s dimensions, delivery access, power and internet connection.'],
            ['Daily operation', 'We agree who will restock products, check expiry dates and clean the machine.'],
            ['Costs and services', 'Before you order, we clarify what the quote includes and what costs extra.'],
        ],
        'quote'=>'Request a quote',
    ];
    $options = [];
    foreach ($copy['options'] as [$title, $description]) $options[] = bvs_home_heading($title, 3).bvs_home_paragraph($description);
    $items = '';
    foreach ($copy['tasks'] as [$title, $description]) {
        $items .= bvs_home_group(bvs_home_heading($title, 4).bvs_home_paragraph($description), 'bvs-planning-item');
    }
    $tasks = bvs_home_heading($copy['planning'], 3).bvs_home_group($items, 'bvs-planning-items');
    $button = bvs_home_block('buttons', [], '<div class="wp-block-buttons">'.bvs_home_block('button', [], '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#quote">'.esc_html($copy['quote']).'</a></div>').'</div>');
    return bvs_home_group(bvs_home_heading($copy['title']).bvs_home_columns($options, 'bvs-purchase-options').bvs_home_group($tasks, 'bvs-operating-plan').$button, 'bvs-section bvs-surface bvs-commercial', 'purchase-rental');
}
function bvs_home_process($lang) {
    $copy = $lang === 'lv' ? [
        'No ieceres līdz piedāvājumam',
        ['Pastāstiet par savu plānu', 'Norādiet produktus un iecerēto atrašanās vietu. Varat sākt arī ar jautājumu.'],
        ['Izvēlieties risinājumu', 'Pārrunājam modeli, iegādi vai nomu, maksājumus un aplīmēšanu.'],
        ['Saskaņojiet nosacījumus', 'Vienojamies par konfigurāciju, izmaksām, piegādi un ikdienas pienākumiem.'],
    ] : [
        'From your idea to a proposal',
        ['Tell us your plan', 'Share your products and intended location. You can start with a question, too.'],
        ['Choose a solution', 'Discuss the model, purchase or rental, payments and wrapping.'],
        ['Agree the details', 'Agree the configuration, costs, delivery and daily responsibilities.'],
    ];
    $steps = [];
    foreach (array_slice($copy, 1) as $index => [$title, $description]) {
        $steps[] = bvs_home_paragraph(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), 'bvs-step-number').bvs_home_heading($title, 3).bvs_home_paragraph($description);
    }
    return bvs_home_group(bvs_home_heading($copy[0]).bvs_home_columns($steps, 'bvs-process-steps'), 'bvs-section bvs-process bvs-process-compact');
}
function bvs_home_form_options($blocks, $lang) {
    foreach ($blocks as &$block) {
        if ($block['blockName'] === 'bvs/quote-form') {
            $block['attrs']['collapseOptional'] = true;
            $block['attrs']['optionalLabel'] = $lang === 'lv' ? 'Papildu informācija un dizains (nav obligāti)' : 'More details and design (optional)';
        }
        $block['innerBlocks'] = bvs_home_form_options($block['innerBlocks'], $lang);
    }
    return $blocks;
}
function bvs_refine_home_content($content, $lang) {
    $lv = $lang === 'lv';
    $result = '';
    foreach (parse_blocks($content) as $block) {
        $class = $block['attrs']['className'] ?? '';
        $anchor = $block['attrs']['anchor'] ?? '';
        $html = serialize_block($block);
        if (str_contains($class, 'bvs-hero')) {
            $heading = $lv ? 'Viedie ledusskapji jūsu produktu tirdzniecībai' : 'Smart fridges for selling your products';
            $intro = $lv ? 'Iekārtas iepakotas pārtikas un dzērienu tirdzniecībai. Iegādājieties vai nomājiet, un pielāgojiet aplīmējumu savam zīmolam.' : 'Equipment for selling packaged food and drinks. Buy or rent, with custom wrapping for your brand.';
            $html = preg_replace('/<h1 class="wp-block-heading">.*?<\/h1>/s', '<h1 class="wp-block-heading">'.esc_html($heading).'</h1>', $html, 1);
            $html = preg_replace('/<p class="bvs-lead">.*?<\/p>/s', '<p class="bvs-lead">'.esc_html($intro).'</p>', $html, 1);
            $html .= bvs_home_audience($lang);
        } elseif ($anchor === 'equipment') {
            $old = $lv ? ['13,3 collu skārienekrāns un Vision AI kameras produktu atpazīšanai.', 'Skārienekrāns vitrīnas kreisajā pusē un pieci regulējami plauktu līmeņi.'] : ['A 13.3-inch touchscreen and Vision AI cameras for product recognition.', 'A touchscreen beside the display and five adjustable shelf levels.'];
            $new = $lv ? ['Vision AI kameras atpazīst paņemtos produktus. Vendlive pārvaldības panelī attālināti mainiet produktu katalogu un cenas.', 'Pieci regulējami plauktu līmeņi iepakotām maltītēm un dzērieniem. Sekojiet krājumu, pirkumu un temperatūras datiem.'] : ['Vision AI cameras recognise products taken. Update your product catalogue and prices remotely in the Vendlive management dashboard.', 'Five adjustable shelf levels for packaged meals and drinks. Monitor stock, purchases and temperature.'];
            $html = str_replace($old, $new, $html);
            $capacity = $lv ? 'Produktu ietilpība' : 'Product capacity';
            $html = preg_replace('/<tr><td>'.preg_quote($capacity, '/').'<\/td><td>.*?<\/td><\/tr>/s', '', $html);
            $html = substr_replace($html, bvs_home_comparison_note($lang), strrpos($html, '</div>'), 0);
        } elseif (str_contains($class, 'bvs-performance')) {
            continue;
        } elseif ($anchor === 'purchase-rental') {
            $html = bvs_home_commercial($lang);
        } elseif ($anchor === 'branding') {
            $html = str_replace('var(--wp--preset--color--branding-highlight)', 'var(--wp--preset--color--accent-light)', $html);
            $caption = $lv ? 'Aplīmējuma piemērs: JāņogaGO zīmola krāsas un logotips uz iekārtas.' : 'Wrapping example: JāņogaGO brand colours and logo on the machine.';
            $html = str_replace('</figure>', '<figcaption class="wp-element-caption">'.esc_html($caption).'</figcaption></figure>', $html);
        } elseif (str_contains($class, 'bvs-process')) {
            $html = bvs_home_process($lang);
        } elseif ($anchor === 'faq') {
            $html = str_replace('Krājumu papildināšana, produktu kvalitāte un ikdienas pienākumi ir jāvienojas.', 'Par krājumu papildināšanu, produktu kvalitāti un ikdienas pienākumiem vienojamies pirms darba sākuma.', $html);
        } elseif ($anchor === 'quote') {
            $html = serialize_blocks(bvs_home_form_options([$block], $lang));
        }
        $result .= $html;
    }
    return $result;
}

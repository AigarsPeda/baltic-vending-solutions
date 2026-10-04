<?php
/** Native starter blocks for payments and product management. */
function bvs_operations_block($name, $attributes, $html) {
    return '<!-- wp:'.$name.($attributes ? ' '.wp_json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '').' -->'.$html.'<!-- /wp:'.$name.' -->';
}
function bvs_operations_heading($text, $level = 2) {
    return bvs_operations_block('heading', $level === 2 ? [] : ['level'=>$level], '<h'.$level.' class="wp-block-heading">'.esc_html($text).'</h'.$level.'>');
}
function bvs_operations_paragraph($text, $class = '') {
    return bvs_operations_block('paragraph', $class ? ['className'=>$class] : [], '<p'.($class ? ' class="'.esc_attr($class).'"' : '').'>'.esc_html($text).'</p>');
}
function bvs_operations_content($lang) {
    $copy = $lang === 'lv' ? [
        'title'=>'Ērti norēķini un pārskatāmi krājumi',
        'intro'=>'Pircējs norēķinās pie iekārtas. Jūs attālināti sekojat krājumiem un pārdošanai.',
        'paymentTitle'=>'Ar karti vai telefonu',
        'paymentText'=>'Pircējs pietuvina bezkontakta bankas karti, telefonu vai viedpulksteni maksājumu terminālim.',
        'methods'=>'Visa, Mastercard, Apple Pay un Google Pay',
        'paymentNote'=>'Smart Fridge piedāvājumā ir Payter terminālis. Maksājumu pieslēgumu un komisijas precizējam piedāvājumā.',
        'managementTitle'=>'Pārvaldiet produktus attālināti',
        'tasks'=>[
            ['Krājumu atlikumi', 'Skatiet, cik produktu palicis, un plānojiet, ko vest nākamajā papildināšanas reizē.'],
            ['Pārdošanas dati', 'Sekojiet pirkumiem un redziet, kuri produkti tiek pārdoti.'],
            ['Produkti un cenas', 'Compact Cooler produktu katalogu un cenas mainiet Vendlive panelī, nebraucot pie iekārtas.'],
        ],
        'note'=>'Mūsu Compact Cooler komplektācijā Vision AI atpazīst paņemtos produktus. Smart Fridge sniedz krājumu, pirkumu un temperatūras datus. Programmatūras piekļuvi un maksājumu nosacījumus precizējam piedāvājumā.',
    ] : [
        'title'=>'Easy payments and a clear view of your stock',
        'intro'=>'Customers pay at the machine. You follow stock and sales remotely.',
        'paymentTitle'=>'Tap a card or phone',
        'paymentText'=>'The customer taps a contactless bank card, phone or smartwatch on the payment terminal.',
        'methods'=>'Visa, Mastercard, Apple Pay and Google Pay',
        'paymentNote'=>'The Smart Fridge proposal includes a Payter terminal. Payment setup and fees are agreed in your proposal.',
        'managementTitle'=>'Manage products remotely',
        'tasks'=>[
            ['Check stock', 'See how many products remain and plan what to bring on your next restocking visit.'],
            ['Follow sales', 'Review purchases and see which products are selling.'],
            ['Products and prices', 'Update the Compact Cooler catalogue and prices in Vendlive without travelling to the machine.'],
        ],
        'note'=>'Our Compact Cooler configuration includes Vision AI to recognise products taken. Smart Fridge provides stock, purchase and temperature data. Software access and payment terms are specified in your proposal.',
    ];
    $payment = bvs_operations_heading($copy['paymentTitle'], 3)
        .bvs_operations_paragraph($copy['paymentText'])
        .bvs_operations_paragraph($copy['methods'], 'bvs-payment-methods')
        .bvs_operations_paragraph($copy['paymentNote'], 'bvs-payment-note');
    $payment = bvs_operations_block('group', ['className'=>'bvs-payment'], '<div class="wp-block-group bvs-payment">'.$payment.'</div>');
    $management = bvs_operations_heading($copy['managementTitle'], 3);
    foreach ($copy['tasks'] as [$title, $description]) {
        $task = bvs_operations_heading($title, 4).bvs_operations_paragraph($description);
        $management .= bvs_operations_block('group', ['className'=>'bvs-operation-task'], '<div class="wp-block-group bvs-operation-task">'.$task.'</div>');
    }
    $columns = bvs_operations_block('column', [], '<div class="wp-block-column">'.$payment.'</div>')
        .bvs_operations_block('column', [], '<div class="wp-block-column">'.$management.'</div>');
    $columns = bvs_operations_block('columns', ['className'=>'bvs-operations-columns'], '<div class="wp-block-columns bvs-operations-columns">'.$columns.'</div>');
    $footer = bvs_operations_block('group', ['className'=>'bvs-operations-footer'], '<div class="wp-block-group bvs-operations-footer">'.bvs_operations_paragraph($copy['note'], 'bvs-operations-note').'</div>');
    $content = bvs_operations_heading($copy['title']).bvs_operations_paragraph($copy['intro'], 'bvs-lead').$columns.$footer;
    return bvs_operations_block('group', ['className'=>'bvs-section bvs-operations', 'anchor'=>'payments-management', 'layout'=>['type'=>'constrained']], '<div id="payments-management" class="wp-block-group bvs-section bvs-operations">'.$content.'</div>');
}

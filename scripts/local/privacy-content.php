<?php
/** Starter privacy copy only. Saved WordPress pages remain authoritative after setup. */
function bvs_privacy_block($name, $attrs, $html) {
    return '<!-- wp:'.$name.($attrs ? ' '.wp_json_encode($attrs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : '').' -->'.$html.'<!-- /wp:'.$name.' -->';
}
function bvs_privacy_paragraph($text, $class = '') {
    return bvs_privacy_block('paragraph', $class ? ['className'=>$class] : [], '<p'.($class ? ' class="'.esc_attr($class).'"' : '').'>'.wp_kses_post($text).'</p>');
}
function bvs_privacy_heading($text, $level = 2, $anchor = '') {
    return bvs_privacy_block('heading', array_filter(['level'=>$level, 'anchor'=>$anchor]), '<h'.$level.($anchor ? ' id="'.esc_attr($anchor).'"' : '').' class="wp-block-heading">'.esc_html($text).'</h'.$level.'>');
}
function bvs_privacy_copy($lang) {
    return $lang === 'lv' ? [
        'title'=>'Privātums un sīkdatnes',
        'intro'=>'Kā rīkojamies ar jūsu pieprasījuma datiem un sīkdatņu izvēli.',
        'updated'=>'Atjaunināts 2026. gada 4. oktobrī',
        'controller'=>'Kas atbild par jūsu datiem',
        'controllerText'=>'Baltic Vending Solutions vietnes datu pārzinis: <strong>[juridiskais nosaukums, reģistrācijas numurs un juridiskā adrese jāapstiprina]</strong>.',
        'contact'=>'Jautājumi par personas datiem: <strong>[privātuma kontaktadrese jāapstiprina]</strong>.',
        'enquiries'=>'Jūsu pieprasījumi',
        'enquiriesText'=>'Nosūtot piedāvājuma pieprasījumu, jūs sniedzat kontaktpersonas vārdu, e-pastu un informāciju par produktiem, kurus vēlaties pārdot. Varat norādīt arī uzņēmuma nosaukumu, tālruni, vēlamo iekārtu, interesi par iegādi vai nomu, plānoto atrašanās vietu un papildu ziņu.',
        'purpose'=>'Šo informāciju izmantojam, lai izskatītu pieprasījumu, ieteiktu piemērotu iekārtu un sagatavotu atbildi vai piedāvājumu. Formas nosūtīšanai nav nepieciešama piekrišana analītikai. Pieprasījuma saturs netiek nosūtīts Google Analytics.',
        'designs'=>'Jūsu dizaina faili',
        'designsText'=>'Dizaina redaktors automātiski saglabā vienu dizainu šī pārlūka vietējā krātuvē, lai vēlāk varētu turpināt darbu. Saglabājam paneļu krāsas, attēlus, logotipu izvietojumu un zīmējumu; saziņas formas dati tajā netiek saglabāti. Dizains paliek pārlūkā, līdz redaktorā izvēlaties “Sākt no jauna” vai notīrāt vietnes datus. Tas netiek automātiski nosūtīts mums un nav saistīts ar analītikas izvēli. Lejupielāde saglabā failu jūsu ierīcē. Ja pievienojat dizainu formai un to nosūtāt, normalizētu attēlu glabājam kopā ar privāto pieprasījumu. Administratori to var lejupielādēt, lai sagatavotu aplīmēšanas piedāvājumu. Uz failu attiecas pieprasījumu glabāšanas un dzēšanas kārtība.',
        'basis'=>'<strong>Jāapstiprina pirms publicēšanas:</strong> tiesiskais pamats pieprasījumu izskatīšanai un saziņai ar uzņēmumu pārstāvjiem.',
        'security'=>'Vietnes darbība un drošība',
        'securityText'=>'Vietnes servera žurnālos var būt IP adrese, pieprasījuma laiks, apmeklētā adrese un pārlūka informācija. Forma izmanto īslaicīgu pieprasījumu ierobežotāju, kura atslēga veidota no IP adreses aizsargātas jaucējvērtības. Ierobežojuma laika logs ir piecas minūtes; IP adrese netiek saglabāta pieprasījuma ierakstā.',
        'securityPending'=>'Servera žurnālu glabāšanas ilgums un drošības datu apstrādes tiesiskais pamats jāapstiprina pirms publicēšanas.',
        'cookies'=>'Sīkdatnes un jūsu izvēle',
        'cookiesText'=>'Pirms apstiprinātas izvēles publiskā vietne neveido sīkdatnes un nesaglabā izvēli pārlūka krātuvē. Paziņojumā varat atļaut vai noraidīt analītiku. Abas izvēles tiek ievērotas.',
        'cookieItems'=>[
            '<code>bvs_consent</code> glabā apstiprināto analītikas izvēli, tās laiku un paziņojuma versiju līdz 180 dienām.',
            '<code>wp_consent_*</code> nodod apstiprināto izvēli saderīgiem WordPress spraudņiem. Šo sīkdatņu termiņš ir 30 dienas, un tās tiek atjaunotas turpmākajos apmeklējumos, kamēr izvēle ir spēkā.',
            '<code>pll_language</code> saglabā valodu līdz vienam gadam. Arī šo sīkdatni veidojam tikai pēc apstiprinātas izvēles.',
            '<code>bvs_consent_changed</code> īslaicīgi paziņo citām atvērtām pārlūka cilnēm par izmaiņām un uzreiz tiek dzēsts no vietējās krātuves.',
        ],
        'change'=>'Izvēli jebkurā laikā varat mainīt kājenes sadaļā “Sīkdatņu iestatījumi” vai ar pogu zemāk. Noraidot iepriekš atļautu analītiku, vietne atjaunina piekrišanu un dzēš savas Analytics sīkdatnes. Tas pats par sevi neizdzēš datus, kas jau būtu nosūtīti pakalpojuma sniedzējam.',
        'settings'=>'Mainīt sīkdatņu izvēli',
        'analytics'=>'Google Analytics',
        'analyticsText'=>'Google Analytics šajā vietējās izstrādes versijā nav pievienots. Analītikas dati netiek vākti, arī ja paziņojumā izvēlaties to atļaut. Reklāmas integrācijas nav aktivizētas.',
        'analyticsFuture'=>'Pirms analītikas ieslēgšanas publiskajā vietnē jāpapildina šī lapa ar izmantoto datu, Google pakalpojumu, glabāšanas termiņu un iespējamās datu nosūtīšanas ārpus Eiropas Ekonomikas zonas aprakstu. Analītikas tags drīkst darboties tikai pēc jūsu atļaujas.',
        'access'=>'Kam dati ir pieejami',
        'accessText'=>'Pieprasījumi tiek glabāti privātos WordPress ierakstos. Tiem var piekļūt vietnes administratori. Vietējās izstrādes versijā e-pasta paziņojumi tiek pārtverti un netiek nosūtīti ārējam e-pasta pakalpojumam.',
        'providers'=>'Publisko vietni plānots mitināt DigitalOcean. Gala servera atrašanās vieta, e-pasta pakalpojuma sniedzējs un citi datu apstrādātāji vēl jāapstiprina; šis teksts neapraksta jau darbojošos publisku infrastruktūru.',
        'retention'=>'Cik ilgi glabājam datus',
        'retentionText'=>'<strong>[Pieprasījumu, saziņas, servera žurnālu un rezerves kopiju glabāšanas termiņi jāapstiprina.]</strong> Pašreizējā izstrādes versijā pieprasījumiem nav automātiskas dzēšanas grafika. Pirms publicēšanas jānosaka glabāšanas termiņi un dzēšanas kārtība.',
        'rights'=>'Jūsu tiesības',
        'rightsText'=>'Atbilstoši piemērojamajiem nosacījumiem varat pieprasīt piekļuvi personas datiem, to labošanu, dzēšanu, apstrādes ierobežošanu vai pārnesamību, kā arī iebilst pret noteiktu apstrādi. Piekrišanu analītikai varat atsaukt jebkurā laikā.',
        'rightsContact'=>'Privātuma jautājumu kontaktadrese vēl jāapstiprina. Informācija par tiesībām un sūdzību iesniegšanu pieejama <a href="https://www.dvi.gov.lv/lv">Datu valsts inspekcijas vietnē</a>.',
    ] : [
        'title'=>'Privacy & cookies',
        'intro'=>'How we handle your enquiry information and cookie choice.',
        'updated'=>'Updated 4 October 2026',
        'controller'=>'Who is responsible for your data',
        'controllerText'=>'Controller for the Baltic Vending Solutions website: <strong>[legal company name, registration number and registered address to be confirmed]</strong>.',
        'contact'=>'Personal data questions: <strong>[privacy contact email to be confirmed]</strong>.',
        'enquiries'=>'Your enquiries',
        'enquiriesText'=>'When requesting a quote, you provide your contact name, email and information about the products you want to sell. You can also provide a business name, phone number, preferred machine, interest in purchase or rental, planned location and an additional message.',
        'purpose'=>'We use this information to review your request, recommend suitable equipment and prepare a reply or proposal. Submitting the form does not require Analytics consent. Enquiry contents are not sent to Google Analytics.',
        'designs'=>'Your design files',
        'designsText'=>'The design editor automatically saves one draft in this browser’s local storage so you can resume later. It saves panel colours, images, logo positions and drawing; contact-form details are excluded. The draft remains until you choose Start again in the editor or clear site data. It is not automatically sent to us and is independent of your Analytics choice. Downloading saves the file on your device. If you attach a design and submit the form, we store a normalised image with your private enquiry. Administrators can download it to prepare your wrapping quote. The enquiry retention and deletion policy applies to the file.',
        'basis'=>'<strong>To confirm before publication:</strong> the legal grounds for handling enquiries and communicating with business representatives.',
        'security'=>'Website operation and security',
        'securityText'=>'Server logs may contain your IP address, request time, visited URL and browser information. The form uses a short-lived request limit keyed by a protected hash of the IP address. The rate-limit window is five minutes; the IP address is not saved in the enquiry record.',
        'securityPending'=>'Server-log retention and the legal grounds for security processing must be confirmed before publication.',
        'cookies'=>'Cookies and your choice',
        'cookiesText'=>'Before a confirmed choice, the public website creates no cookies and stores no choice in browser storage. You can allow or reject Analytics in the banner. Both choices are respected.',
        'cookieItems'=>[
            '<code>bvs_consent</code> stores your confirmed Analytics choice, its time and the notice version for up to 180 days.',
            '<code>wp_consent_*</code> shares the confirmed choice with compatible WordPress plugins. These cookies last 30 days and are refreshed on later visits while the choice remains valid.',
            '<code>pll_language</code> remembers your language for up to one year. This cookie is also created only after a confirmed choice.',
            '<code>bvs_consent_changed</code> briefly notifies other open browser tabs of changes and is immediately removed from local storage.',
        ],
        'change'=>'Change your choice at any time through Cookie settings in the footer or the button below. Rejecting previously allowed Analytics updates consent and clears this site’s Analytics cookies. It does not itself delete information that may already have been sent to a provider.',
        'settings'=>'Change cookie settings',
        'analytics'=>'Google Analytics',
        'analyticsText'=>'Google Analytics is not connected in this Local development version. No Analytics data is collected, even if you choose to allow it in the banner. Advertising integrations are inactive.',
        'analyticsFuture'=>'Before enabling Analytics on the public site, update this page with the data collected, Google services used, retention periods and any transfers outside the European Economic Area. The Analytics tag may operate only after your permission.',
        'access'=>'Who can access the data',
        'accessText'=>'Enquiries are stored in private WordPress records accessible to site administrators. In Local development, email notifications are intercepted and are not sent to an external email provider.',
        'providers'=>'The public site is planned for DigitalOcean hosting. The final server location, email provider and other processors still need confirmation; this text does not describe an existing production infrastructure.',
        'retention'=>'How long we keep data',
        'retentionText'=>'<strong>[Enquiry, correspondence, server-log and backup retention periods to be confirmed.]</strong> The current development version has no automatic enquiry-deletion schedule. Retention periods and deletion procedures must be set before publication.',
        'rights'=>'Your rights',
        'rightsText'=>'Subject to applicable conditions, you can request access, correction, deletion, restriction or portability of your personal data, and object to certain processing. You can withdraw Analytics consent at any time.',
        'rightsContact'=>'The privacy contact email still needs confirmation. Information about your rights and submitting a complaint is available from <a href="https://www.dvi.gov.lv/en">Latvia’s Data State Inspectorate</a>.',
    ];
}
function bvs_privacy_content($lang) {
    $c = bvs_privacy_copy($lang);
    $html = bvs_privacy_heading($c['title'], 1).bvs_privacy_paragraph($c['intro'], 'bvs-policy-lead').bvs_privacy_paragraph($c['updated'], 'bvs-policy-updated');
    foreach ([['controller',['controllerText','contact']], ['enquiries',['enquiriesText','purpose','basis']], ['designs',['designsText']], ['security',['securityText','securityPending']], ['cookies',['cookiesText']], ['analytics',['analyticsText','analyticsFuture']], ['access',['accessText','providers']], ['retention',['retentionText']], ['rights',['rightsText','rightsContact']]] as [$heading,$paragraphs]) {
        $html .= bvs_privacy_heading($c[$heading], 2, $heading === 'cookies' ? 'cookie-choices' : '');
        foreach ($paragraphs as $key) $html .= bvs_privacy_paragraph($c[$key]);
        if ($heading === 'cookies') {
            $items = '';
            foreach ($c['cookieItems'] as $item) $items .= bvs_privacy_block('list-item', [], '<li>'.wp_kses_post($item).'</li>');
            $html .= bvs_privacy_block('list', [], '<ul class="wp-block-list">'.$items.'</ul>').bvs_privacy_paragraph($c['change']);
            $button = bvs_privacy_block('button', ['className'=>'is-style-outline bvs-cookie-open'], '<div class="wp-block-button is-style-outline bvs-cookie-open"><a class="wp-block-button__link wp-element-button" href="#cookie-choices">'.esc_html($c['settings']).'</a></div>');
            $html .= bvs_privacy_block('buttons', [], '<div class="wp-block-buttons">'.$button.'</div>');
        }
    }
    return bvs_privacy_block('group', ['className'=>'bvs-policy bvs-cookie-policy','layout'=>['type'=>'constrained']], '<div class="wp-block-group bvs-policy bvs-cookie-policy">'.$html.'</div>');
}

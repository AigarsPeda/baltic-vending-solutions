<?php
/** One-time Local content import. Run with wp-local.sh eval-file. Never run on production. */
if (!defined('WP_CLI') || !WP_CLI || !function_exists('bvs_is_local') || !bvs_is_local()) throw new RuntimeException('This seed requires the BVS plugin and a Local .local environment.');
if (get_option('bvs_seed_version')) { WP_CLI::success('Starter content already imported. Existing edits and deletions are preserved.'); return; }
require_once __DIR__.'/privacy-content.php';
require_once __DIR__.'/branding-content.php';
require_once __DIR__.'/operations-content.php';
require_once __DIR__.'/process-content.php';
if (!function_exists('PLL') || !function_exists('pll_set_post_language')) throw new RuntimeException('Activate Polylang first.');
$equipment_dir = $args[0] ?? '';
if (!is_dir($equipment_dir)) throw new RuntimeException('Pass the proposal equipment directory as the first argument.');
foreach ([['name'=>'Latviešu','slug'=>'lv','locale'=>'lv','rtl'=>0,'flag'=>'lv'],['name'=>'English','slug'=>'en','locale'=>'en_GB','rtl'=>0,'flag'=>'us']] as $language) {
    if (!PLL()->model->get_language($language['slug'])) PLL()->model->add_language($language);
}
require __DIR__.'/setup-language-flags.php';
foreach (['default_lang'=>'lv','hide_default'=>true,'force_lang'=>1,'rewrite'=>true,'browser'=>false,'redirect_lang'=>true] as $key=>$value) PLL()->options->set($key,$value);
PLL()->options->save();
update_option('blogname','Baltic Vending Solutions'); update_option('blogdescription',''); update_option('blog_public',0);
update_option('bvs_interface',['en'=>['menu'=>'Menu','skip'=>'Skip to content'],'lv'=>['menu'=>'Izvēlne','skip'=>'Pāriet uz saturu']]);
update_option('bvs_recipient', get_option('admin_email'));
update_option('permalink_structure','/%postname%/');
function seed_block($name,$attrs,$html) { return '<!-- wp:'.$name.($attrs ? ' '.wp_json_encode($attrs,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : '').' -->'.$html.'<!-- /wp:'.$name.' -->'; }
function seed_p($text,$class='') { return seed_block('paragraph',$class ? ['className'=>$class] : [],'<p'.($class ? ' class="'.esc_attr($class).'"' : '').'>'.wp_kses_post($text).'</p>'); }
function seed_h($text,$level=2,$class='') { $attrs=$level===2 ? [] : ['level'=>$level]; if($class) $attrs['className']=$class; return seed_block('heading',$attrs,'<h'.$level.' class="wp-block-heading'.($class ? ' '.esc_attr($class) : '').'">'.esc_html($text).'</h'.$level.'>'); }
function seed_group($content,$class,$anchor='') { return seed_block('group',array_filter(['className'=>$class,'anchor'=>$anchor,'layout'=>['type'=>'constrained']]),'<div'.($anchor ? ' id="'.esc_attr($anchor).'"' : '').' class="wp-block-group '.esc_attr($class).'">'.$content.'</div>'); }
function seed_columns($columns,$center=false) { $content=''; foreach($columns as $column) $content.=seed_block('column',[],'<div class="wp-block-column">'.$column.'</div>'); return seed_block('columns',$center ? ['verticalAlignment'=>'center'] : [],'<div class="wp-block-columns'.($center ? ' are-vertically-aligned-center' : '').'">'.$content.'</div>'); }
function seed_button($label,$url,$outline=false) { return seed_block('button',$outline ? ['className'=>'is-style-outline'] : [],'<div class="wp-block-button'.($outline ? ' is-style-outline' : '').'"><a class="wp-block-button__link wp-element-button" href="'.esc_url($url).'">'.esc_html($label).'</a></div>'); }
function seed_buttons($content) { return seed_block('buttons',[],'<div class="wp-block-buttons">'.$content.'</div>'); }
function seed_table($rows) { $html='<figure class="wp-block-table"><table class="has-fixed-layout"><tbody>'; foreach($rows as $row) $html.='<tr><td>'.esc_html($row[0]).'</td><td>'.esc_html($row[1]).'</td></tr>'; return seed_block('table',[],$html.'</tbody></table></figure>'); }
function seed_list($items) { $content=''; foreach($items as $item) $content.=seed_block('list-item',[],'<li>'.esc_html($item).'</li>'); return seed_block('list',[],'<ul class="wp-block-list">'.$content.'</ul>'); }
function seed_image($id,$class,$alt,$caption='',$link='') {
    $url=wp_get_attachment_image_url($id,'large');
    $image='<img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" class="wp-image-'.$id.'"/>';
    if($link) $image='<a href="'.esc_url($link).'">'.$image.'</a>';
    return seed_block('image',['id'=>$id,'sizeSlug'=>'large','linkDestination'=>$link ? 'custom' : 'none','className'=>$class],'<figure class="wp-block-image size-large '.esc_attr($class).'">'.$image.($caption ? '<figcaption class="wp-element-caption">'.esc_html($caption).'</figcaption>' : '').'</figure>');
}
require_once ABSPATH.'wp-admin/includes/media.php'; require_once ABSPATH.'wp-admin/includes/file.php'; require_once ABSPATH.'wp-admin/includes/image.php';
$media=[];
$clean_media_dir=$args[1] ?? dirname(__DIR__,2).'/output/media';
foreach (['compact'=>'compact-cooler-unbranded.png','fridge'=>'smart-fridge-unbranded.png'] as $key=>$file) {
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','meta_key'=>'_bvs_source','meta_value'=>$key,'numberposts'=>1]);
    if ($existing) { $media[$key]=$existing[0]->ID; continue; }
    $source=$clean_media_dir.'/'.$file;
    if (!is_file($source)) throw new RuntimeException('Missing cleaned equipment photo: '.$file.'. Supply the unbranded media directory as the second argument, or migrate database and uploads.');
    $temp=wp_tempnam($file); if (!copy($source,$temp)) throw new RuntimeException('Unable to copy supplied media.');
    $id=media_handle_sideload(['name'=>$file,'tmp_name'=>$temp],0,'Boost inc '.($key==='compact' ? 'Compact Cooler' : 'Smart Fridge'));
    if(is_wp_error($id)) { @unlink($temp); throw new RuntimeException($id->get_error_message()); }
    update_post_meta($id,'_bvs_source',$key); update_post_meta($id,'_bvs_variant','unbranded'); update_post_meta($id,'_wp_attachment_image_alt','Boost inc '.($key==='compact' ? 'Compact Cooler, three-quarter view' : 'Smart Fridge, front view'));
    $media[$key]=$id;
}
$copy=[
'en'=>[
 'home'=>'Home','compact'=>'Compact Cooler','fridge'=>'Smart Fridge','privacy'=>'Privacy notice',
 'eyebrow'=>'Equipment for your business','hero'=>'Create a point of sale for your products','lead'=>'Smart vending machines to buy or rent for selling packaged food and drinks. We also offer custom machine wrapping in your brand colours.',
 'quote'=>'Request a quote','equipment'=>'View equipment','buyrent'=>'Purchase & rental','branding'=>'Machine wrapping',
 'equipTitle'=>'Compare the machines','equipLead'=>'Two smart fridges for selling packaged food and drinks.',
 'compactIntro'=>'A 13.3-inch touchscreen and Vision AI cameras for product recognition.','fridgeIntro'=>'A touchscreen beside the display and five adjustable shelf levels.',
 'compactDesc'=>'A compact smart cooler for drinks, sandwiches, salads and prepared meals. Vision AI recognises the products customers take.',
 'fridgeDesc'=>'A smart fridge with a touchscreen beside the display. Adjustable shelving accommodates packaged meals, snacks and drinks.',
 'performanceLabel'=>'Average revenue uplift','performanceTitle'=>'Easy product selection and contactless payment','performanceText'=>'A touchscreen and contactless payments help customers choose their products and pay at the machine. We select the configuration around your product range and location.','performanceNote'=>'Boost inc publishes this average for its solutions. It is not a revenue forecast for your machine or location.',
 'capacity'=>'Product capacity','capacityValue'=>'Depends on packaging and shelf layout','temp'=>'Temperature','screen'=>'Touchscreen','recognition'=>'Product recognition','shelves'=>'Shelves','connection'=>'Connectivity','payments'=>'Payments','management'=>'Management',
 'compactScreen'=>'13.3-inch','fridgeScreen'=>'Left-side touchscreen','vision'=>'Vision AI in our proposed configuration','compactShelves'=>'Height-adjustable wire shelves','fridgeShelves'=>'5 adjustable shelf levels','net'=>'4G / Wi-Fi / Ethernet','pay'=>'Contactless card and mobile payments','vendlive'=>'Vendlive cloud management',
 'detail'=>'View','commercialLabel'=>'A model that fits your plans','commercialTitle'=>'Buy for the long term or rent to get started','commercialLead'=>'We prepare a proposal around the equipment, location and branding you need. Prices and terms are agreed for your configuration.',
 'purchase'=>'Purchase','purchaseText'=>'Own the equipment and build it into your operation. Discuss your product range, payment setup and proposed location before choosing a configuration.',
 'rental'=>'Rental','rentalText'=>'Discuss a rental arrangement for a new sales channel or an initial trial. Availability, duration, included services and operating responsibilities are confirmed in your proposal.',
 'processLabel'=>'Working together','processTitle'=>'From your idea to an equipment quote','steps'=>[['Tell us your plan','What will you sell, who will buy it and where will the machine stand?'],['Choose your machine','Compare the models, purchase or rental, connectivity and wrapping options.'],['Agree the next steps','Agree the quote, delivery and who will look after the machine each day.']],
 'faqTitle'=>'Before you choose your equipment','faq'=>[
 ['Can I use my own brand?','Yes. We offer custom wrapping to match your business identity. The artwork and application scope are agreed for the selected machine.'],
 ['Which products are suitable?','Our proposed equipment is intended for chilled, packaged food and drinks. We will review your actual packaging and product range when preparing the configuration.'],
 ['Who stocks the machine?','Restocking, product quality and daily operating responsibilities need to be agreed. Tell us how you plan to run the machine so the proposal reflects that.'],
 ['Can I manage products remotely?','The proposed Compact Cooler uses Vendlive cloud management for stock, products and prices. The Smart Fridge proposal includes real-time stock, purchase and temperature information.'],
 ['What does rental cost?','We quote for the equipment and agreed services. Rental duration, availability and costs have not yet been published.']],
 'contactLabel'=>'Plan your next sales channel','contactTitle'=>'Tell us what you want to sell','contactText'=>'Share your products, intended location and interest in purchase or rental. You can also ask for advice on which machine fits your plans.',
 'contactNote'=>'You do not need to have every detail decided before making an enquiry.',
 'proposed'=>'Proposed equipment','specTitle'=>'The proposed specification','specNote'=>'Specifications follow our equipment proposal. Final configuration, payment provider and service scope are confirmed in your quote.',
 'compactMore'=>[['Display and interface','13.3-inch touchscreen with Boost inc interface'],['Recognition','Vision AI cameras, without weight sensors or RFID product stickers'],['Management','Live inventory and remote product / price management'],['Cabinet','Black interior and exterior; toughened glass door'],['Door','Automatic closing with electronic lock integration'],['Lighting','Vertical interior LED lighting'],['Product information','Allergen filters, nutrition, product photos and promotional content']],
 'fridgeMore'=>[['Display and interface','Touchscreen on the left of the product display'],['Payments','Payter, NFC, Visa, Mastercard, Apple Pay and Google Pay'],['Door','Double-glazed glass with anti-fog treatment'],['Lighting','LED lighting at shelf levels'],['Cabinet','Black metal finish'],['Monitoring','Real-time stock, purchase and temperature data']],
 'footerText'=>'Smart equipment for selling your products. Purchase, rental and custom machine wrapping.',
 'privacyText'=>'Local development draft. This site is not ready to collect real customer data.','privacyBody'=>'The quote form stores contact details and the enquiry in private WordPress records so administrators can review and respond. Local testing uses notification capture and does not send external email. Business identity, contact details, retention periods, legal basis and data-processing providers must be completed before launch.',
],
'lv'=>[
 'home'=>'Sākums','compact'=>'Compact Cooler','fridge'=>'Smart Fridge','privacy'=>'Privātuma informācija',
 'eyebrow'=>'Iekārtas jūsu biznesam','hero'=>'Izveidojiet tirdzniecības vietu saviem produktiem','lead'=>'Viedās tirdzniecības iekārtas iegādei vai nomai, lai pārdotu iepakotu pārtiku un dzērienus. Piedāvājam arī individuālu iekārtu aplīmēšanu jūsu zīmolam.',
 'quote'=>'Pieprasīt piedāvājumu','equipment'=>'Apskatīt iekārtas','buyrent'=>'Iegāde un noma','branding'=>'Iekārtu aplīmēšana',
 'equipTitle'=>'Salīdziniet iekārtas','equipLead'=>'Divi viedie ledusskapji iepakotas pārtikas un dzērienu tirdzniecībai.',
 'compactIntro'=>'13,3 collu skārienekrāns un Vision AI kameras produktu atpazīšanai.','fridgeIntro'=>'Skārienekrāns vitrīnas kreisajā pusē un pieci regulējami plauktu līmeņi.',
 'compactDesc'=>'Kompakts viedais ledusskapis dzērieniem, sviestmaizēm, salātiem un gatavām maltītēm. Vision AI atpazīst pircēja paņemtos produktus.',
 'fridgeDesc'=>'Viedais ledusskapis ar skārienekrānu blakus produktu vitrīnai. Regulējami plaukti iepakotām maltītēm, uzkodām un dzērieniem.',
 'performanceLabel'=>'Vidējais ieņēmumu pieaugums','performanceTitle'=>'Vienkārša produktu izvēle un bezkontakta apmaksa','performanceText'=>'Skārienekrāns un bezkontakta maksājumi palīdz pircējam izvēlēties produktus un norēķināties pie iekārtas. Konfigurāciju izvēlamies atbilstoši jūsu produktu klāstam un atrašanās vietai.','performanceNote'=>'Boost inc publicē šo vidējo rādītāju par saviem risinājumiem. Tas nav ieņēmumu prognoze jūsu iekārtai vai atrašanās vietai.',
 'capacity'=>'Produktu ietilpība','capacityValue'=>'Atkarīga no iepakojuma un plauktu izkārtojuma','temp'=>'Temperatūra','screen'=>'Skārienekrāns','recognition'=>'Produktu atpazīšana','shelves'=>'Plaukti','connection'=>'Savienojamība','payments'=>'Maksājumi','management'=>'Pārvaldība',
 'compactScreen'=>'13,3 collas','fridgeScreen'=>'Skārienekrāns kreisajā pusē','vision'=>'Vision AI mūsu piedāvātajā konfigurācijā','compactShelves'=>'Regulējama augstuma režģu plaukti','fridgeShelves'=>'5 regulējami plauktu līmeņi','net'=>'4G / Wi-Fi / Ethernet','pay'=>'Bezkontakta karšu un mobilie maksājumi','vendlive'=>'Vendlive mākoņpārvaldība',
 'detail'=>'Apskatīt','commercialLabel'=>'Jūsu plāniem piemērots modelis','commercialTitle'=>'Izvēlieties iegādi ilgtermiņam vai nomu sākumam','commercialLead'=>'Sagatavojam piedāvājumu vajadzīgajai iekārtai, vietai un zīmola dizainam. Cenu un nosacījumus vienojam jūsu konfigurācijai.',
 'purchase'=>'Iegāde','purchaseText'=>'Iegādājieties iekārtu un iekļaujiet to sava uzņēmuma darbībā. Pirms konfigurācijas izvēles pārrunāsim produktu klāstu, maksājumu risinājumu un plānoto atrašanās vietu.',
 'rental'=>'Noma','rentalText'=>'Pārrunājiet nomas iespējas jaunam pārdošanas kanālam vai sākotnējam izmēģinājumam. Pieejamību, termiņu, pakalpojumus un ikdienas pienākumus apstiprinām piedāvājumā.',
 'processLabel'=>'Sadarbības sākums','processTitle'=>'No idejas līdz iekārtas piedāvājumam','steps'=>[['Pastāstiet par savu plānu','Ko pārdosiet, kas būs pircēji un kur atradīsies iekārta?'],['Izvēlieties iekārtu','Salīdziniet modeļus, iegādi vai nomu, savienojamību un aplīmēšanas iespējas.'],['Saskaņojiet nākamos soļus','Vienojamies par piedāvājumu, piegādi un to, kurš ikdienā apkalpos iekārtu.']],
 'faqTitle'=>'Pirms iekārtas izvēles','faq'=>[
 ['Vai varu izmantot savu zīmolu?','Jā. Piedāvājam individuālu aplīmēšanu atbilstoši jūsu uzņēmuma identitātei. Dizainu un uzklāšanas apjomu saskaņojam izvēlētajai iekārtai.'],
 ['Kādiem produktiem iekārtas ir piemērotas?','Mūsu piedāvātās iekārtas paredzētas atdzesētai, iepakotai pārtikai un dzērieniem. Gatavojot konfigurāciju, izvērtēsim jūsu produktu klāstu un iepakojumu.'],
 ['Kas papildina iekārtas krājumus?','Krājumu papildināšana, produktu kvalitāte un ikdienas pienākumi ir jāvienojas. Pastāstiet, kā plānojat apkalpot iekārtu, lai to ņemtu vērā piedāvājumā.'],
 ['Vai produktus var pārvaldīt attālināti?','Piedāvātais Compact Cooler izmanto Vendlive mākoņpārvaldību krājumiem, produktiem un cenām. Smart Fridge piedāvājums ietver krājumu, pirkumu un temperatūras datus reāllaikā.'],
 ['Cik maksā noma?','Sagatavojam cenu piedāvājumu izvēlētajai iekārtai un pakalpojumiem. Nomas termiņi, pieejamība un izmaksas vēl nav publicētas.']],
 'contactLabel'=>'Plānojiet nākamo pārdošanas vietu','contactTitle'=>'Pastāstiet, ko vēlaties pārdot','contactText'=>'Norādiet produktus, plānoto atrašanās vietu un interesi par iegādi vai nomu. Varat arī lūgt padomu piemērotas iekārtas izvēlē.',
 'contactNote'=>'Pirms saziņas nav jābūt izlemtiem visiem jautājumiem.',
 'proposed'=>'Piedāvātā iekārta','specTitle'=>'Piedāvātā specifikācija','specNote'=>'Specifikācija atbilst mūsu iekārtu piedāvājumam. Galīgo konfigurāciju, maksājumu pakalpojuma sniedzēju un pakalpojumu apjomu apstiprinām piedāvājumā.',
 'compactMore'=>[['Ekrāns un saskarne','13,3 collu skārienekrāns ar Boost inc saskarni'],['Atpazīšana','Vision AI kameras bez svara sensoriem vai RFID produktu uzlīmēm'],['Pārvaldība','Krājumi reāllaikā un attālināta produktu un cenu vadība'],['Korpuss','Melna iekšpuse un ārpuse; rūdīta stikla durvis'],['Durvis','Automātiska aizvēršanās un elektroniskās slēdzenes integrācija'],['Apgaismojums','Vertikāls iekšējais LED apgaismojums'],['Produktu informācija','Alergēnu filtri, uzturvērtība, foto un reklāmas saturs']],
 'fridgeMore'=>[['Ekrāns un saskarne','Skārienekrāns produktu vitrīnas kreisajā pusē'],['Maksājumi','Payter, NFC, Visa, Mastercard, Apple Pay un Google Pay'],['Durvis','Dubultstikls ar pret aizsvīšanas apstrādi'],['Apgaismojums','LED apgaismojums plauktu līmeņos'],['Korpuss','Melna metāla apdare'],['Uzraudzība','Krājumu, pirkumu un temperatūras dati reāllaikā']],
 'footerText'=>'Viedās iekārtas jūsu produktu tirdzniecībai. Iegāde, noma un individuāla iekārtu aplīmēšana.',
 'privacyText'=>'Vietējās izstrādes melnraksts. Vietne vēl nav gatava īstu klientu datu saņemšanai.','privacyBody'=>'Piedāvājuma forma glabā kontaktinformāciju un pieprasījumu privātos WordPress ierakstos, lai administratori varētu tos izskatīt un atbildēt. Vietējie testi izmanto paziņojumu pārtveršanu un nesūta ārējos e-pastus. Pirms publicēšanas jāpapildina uzņēmuma identitāte, kontaktinformācija, glabāšanas termiņi, tiesiskais pamats un datu apstrādes pakalpojumu sniedzēji.',
]];
$pages=[];
$administrators=get_users(['role'=>'administrator','number'=>1]);
$author_id=$administrators ? $administrators[0]->ID : 0;
foreach($copy as $lang=>$c) foreach(['home','compact','fridge','privacy'] as $key) {
    $existing=get_posts(['post_type'=>'page','post_status'=>'any','meta_key'=>'_bvs_seed_key','meta_value'=>$lang.'-'.$key,'numberposts'=>1]);
    if ($existing) $id=$existing[0]->ID;
    else {
        $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_author'=>$author_id,'post_title'=>$c[$key],'post_name'=>['lv'=>['home'=>'sakums','compact'=>'kompaktais-ledusskapis','fridge'=>'viedais-ledusskapis','privacy'=>'privatums'],'en'=>['home'=>'home','compact'=>'compact-cooler','fridge'=>'smart-fridge','privacy'=>'privacy']][$lang][$key],'meta_input'=>['_bvs_seed_key'=>$lang.'-'.$key]],true);
        if(is_wp_error($id)) throw new RuntimeException($id->get_error_message());
    }
    pll_set_post_language($id,$lang); $pages[$lang][$key]=$id;
}
foreach(['home','compact','fridge','privacy'] as $key) pll_save_post_translations(['lv'=>$pages['lv'][$key],'en'=>$pages['en'][$key]]);
update_option('show_on_front','page'); update_option('page_on_front',$pages['lv']['home']);
// A fresh CLI request will load updated language URL options. Use explicit URLs for this initial import.
$base=untrailingslashit(home_url('/'));
$url=[];
foreach($pages as $lang=>$ids) foreach($ids as $key=>$id) $url[$lang][$key]=$key==='home' ? $base.($lang==='lv' ? '/' : '/en/') : $base.($lang==='lv' ? '/' : '/en/').get_post_field('post_name',$id).'/';
foreach($copy as $lang=>$c) {
    $form=bvs_quote_attributes(['language'=>$lang,'formId'=>'quote-'.$lang,'privacyUrl'=>$url[$lang]['privacy']]);
    if($lang==='lv') $form=array_merge($form,[
      'intro'=>'Obligātie lauki ir atzīmēti ar vārdu "obligāti".','nameLabel'=>'Kontaktpersonas vārds (obligāti)','companyLabel'=>'Uzņēmuma nosaukums','emailLabel'=>'E-pasts (obligāti)','phoneLabel'=>'Tālrunis','modelLabel'=>'Iekārta','modelAny'=>'Palīdziet izvēlēties','modeLabel'=>'Interese','modeAdvice'=>'Konsultācija','modeBuy'=>'Iegāde','modeRent'=>'Noma','productsLabel'=>'Ko plānojat pārdot? (obligāti)','locationLabel'=>'Plānotā atrašanās vieta','messageLabel'=>'Kas vēl mums būtu jāzina?','namePlaceholder'=>'Jūsu vārds','companyPlaceholder'=>'Uzņēmuma vai projekta nosaukums','emailPlaceholder'=>'jus@uznemums.lv','phonePlaceholder'=>'Nav obligāts','productsPlaceholder'=>'Piemēram, iepakotas maltītes un dzērieni','locationPlaceholder'=>'Pilsēta un telpu veids, vai vēl nav izlemts','messagePlaceholder'=>'Jūsu plāni, jautājumi vai dizaina vajadzības','submitLabel'=>$c['quote'],'sendingLabel'=>'Saglabājam…','successMessage'=>'Jūsu pieprasījums ir saglabāts.','errorMessage'=>'Pārbaudiet obligātos laukus un mēģiniet vēlreiz.','unavailableMessage'=>'Pieprasījumu neizdevās saglabāt. Lūdzu, mēģiniet pēc brīža.','privacyText'=>'Jūsu sniegto informāciju izmantojam, lai atbildētu uz pieprasījumu.','privacyLabel'=>$c['privacy']]);
    $quote='<!-- wp:bvs/quote-form '.wp_json_encode($form,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).' /-->';
    $contact=seed_group(seed_columns([seed_h($c['contactTitle']).seed_p($c['contactText'],'bvs-lead').seed_p($c['contactNote'],'bvs-note'),$quote]),'bvs-section bvs-contact bvs-surface','quote');
    $cards=[]; $spec=[];
    foreach(['compact','fridge'] as $model) {
        $rows=[[$c['temp'],$model==='compact' ? '+1 to +8 °C' : '+2 to +8 °C'],[$c['screen'],$c[$model.'Screen']],[$c['shelves'],$c[$model.'Shelves']],[$c['capacity'],$c['capacityValue']],[$c['connection'],$c['net']]];
        if($lang==='lv') $rows[0][1]=$model==='compact' ? '+1 līdz +8 °C' : '+2 līdz +8 °C';
        $spec[$model]=$rows;
        $cards[]=seed_group(seed_image($media[$model],'bvs-product-image','Boost inc '.$c[$model],'',$url[$lang][$model]).seed_h($c[$model],3).seed_p($c[$model.'Intro']).seed_table($rows).seed_buttons(seed_button($c['detail'].' '.$c[$model],$url[$lang][$model],true)),'bvs-product');
        $details=seed_group(seed_columns([seed_h('Boost inc '.$c[$model],1).seed_p($c[$model.'Desc'],'bvs-lead').seed_buttons(seed_button($c['quote'],$url[$lang]['home'].'?model='.$model.'#quote').seed_button($c['equipment'],$url[$lang]['home'].'#equipment',true)),seed_image($media[$model],'bvs-hero-image','Boost inc '.$c[$model])],true),'bvs-section bvs-hero');
        $details.=seed_group(seed_h($c['specTitle']).seed_table(array_merge($rows,$c[$model.'More'])).seed_p($c['specNote'],'bvs-note'),'bvs-section bvs-specs');
        $details.=$contact;
        if(!get_post_meta($pages[$lang][$model],'_bvs_seed_complete',true)) { wp_update_post(wp_slash(['ID'=>$pages[$lang][$model],'post_content'=>$details])); update_post_meta($pages[$lang][$model],'_bvs_seed_complete',1); }
    }
    $hero=seed_columns([seed_h(str_replace('\n',' ',$c['hero']),1).seed_p($c['lead'],'bvs-lead').seed_buttons(seed_button($c['quote'],'#quote').seed_button($c['equipment'],'#equipment',true)),seed_image($media['compact'],'bvs-hero-image','Boost inc Compact Cooler')],true);
    $content=seed_group($hero,'bvs-section bvs-hero');
    $content.=seed_group(seed_h($c['equipTitle']).seed_p($c['equipLead'],'bvs-lead').seed_columns($cards),'bvs-section','equipment');
    $content.=seed_group(seed_columns([seed_p('+75%','bvs-counter').seed_p($c['performanceLabel'],'bvs-performance-label'),seed_h($c['performanceTitle']).seed_p($c['performanceText'],'bvs-lead').seed_p($c['performanceNote'],'bvs-note')]),'bvs-section bvs-performance','shopping-experience');
    $content.=bvs_operations_content($lang);
    $content.=seed_group(seed_h($c['commercialTitle']).seed_p($c['commercialLead'],'bvs-lead').seed_columns([seed_h($c['purchase'],3,'bvs-purchase-title').seed_p($c['purchaseText']),seed_h($c['rental'],3,'bvs-rental-title').seed_p($c['rentalText'])]).seed_buttons(seed_button($c['quote'],'#quote')),'bvs-section bvs-surface bvs-commercial','purchase-rental');
    $content.=bvs_branding_content($lang);
    $content.=bvs_process_content($c['processTitle'],$c['steps'],bvs_process_media(),$lang);
    $faq=seed_h($c['faqTitle']); foreach($c['faq'] as $entry) $faq.=seed_block('details',[],'<details class="wp-block-details"><summary>'.esc_html($entry[0]).'</summary>'.seed_p($entry[1]).'</details>');
    $content.=seed_group($faq,'bvs-section bvs-surface','faq'); $content.=$contact;
    if(!get_post_meta($pages[$lang]['home'],'_bvs_seed_complete',true)) { wp_update_post(wp_slash(['ID'=>$pages[$lang]['home'],'post_content'=>$content])); update_post_meta($pages[$lang]['home'],'_bvs_seed_complete',1); }
    if(!get_post_meta($pages[$lang]['privacy'],'_bvs_seed_complete',true)) { wp_update_post(wp_slash(['ID'=>$pages[$lang]['privacy'],'post_title'=>bvs_privacy_copy($lang)['title'],'post_content'=>bvs_privacy_content($lang)])); update_post_meta($pages[$lang]['privacy'],'_bvs_seed_complete',1); update_post_meta($pages[$lang]['privacy'],'_bvs_privacy_version','1'); }
}
$widgets=get_option('widget_block', []); $sidebars=get_option('sidebars_widgets',[]);
foreach($copy as $lang=>$c) {
    foreach(['header'=>seed_buttons(seed_button($c['quote'],$url[$lang]['home'].'#quote')),'footer'=>seed_columns([seed_h('Baltic Vending Solutions').seed_p($c['footerText']),seed_p('<a href="'.esc_url($url[$lang]['home'].'#quote').'">'.$c['quote'].'</a> &nbsp; / &nbsp; <a href="'.esc_url($url[$lang]['privacy']).'">'.$c['privacy'].'</a>')])] as $area=>$blocks) {
        $sidebar='bvs-'.$area.'-'.$lang;
        { $numbers=array_filter(array_keys($widgets),'is_int'); $number=$numbers ? max($numbers)+1 : 2; $widgets[$number]=['content'=>$blocks]; $sidebars[$sidebar]=['block-'.$number]; }
    }
    $name='Primary '.strtoupper($lang); $menu=wp_get_nav_menu_object($name); $menu_id=$menu ? $menu->term_id : wp_create_nav_menu($name);
    if(is_wp_error($menu_id)) throw new RuntimeException($menu_id->get_error_message());
    if(!wp_get_nav_menu_items($menu_id)) foreach([[$c['equipment'],'#equipment'],[$c['buyrent'],'#purchase-rental'],[$c['branding'],'#branding']] as $item) wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$item[0],'menu-item-url'=>$url[$lang]['home'].$item[1],'menu-item-type'=>'custom','menu-item-status'=>'publish']);
    $menus[$lang]=$menu_id;
}
$widgets['_multiwidget']=1; update_option('widget_block',$widgets); update_option('sidebars_widgets',$sidebars);
// Native Polylang menu assignments and switcher, using its own documented menu metadata.
foreach($menus as $lang=>$menu_id) {
    $nav_menus=PLL()->options->get('nav_menus'); $nav_menus[get_stylesheet()]['primary'][$lang]=$menu_id; PLL()->options->set('nav_menus',$nav_menus);
    PLL()->model->term->set_language($menu_id,$lang);
    $items=wp_get_nav_menu_items($menu_id); $has=false; foreach($items as $item) if(get_post_meta($item->ID,'_pll_menu_item',true)) $has=true;
    if(!$has) {
        $id=wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Languages','menu-item-url'=>'#pll_switcher','menu-item-type'=>'custom','menu-item-status'=>'publish']);
        update_post_meta($id,'_pll_menu_item',['hide_current'=>1,'hide_if_no_translation'=>1,'show_flags'=>0,'show_names'=>1,'force_home'=>0,'dropdown'=>0]);
    }
}
PLL()->options->save(); set_theme_mod('nav_menu_locations',['primary'=>$menus['lv']]);
PLL()->model->term->save_translations($menus);
flush_rewrite_rules(); update_option('bvs_seed_version','0.1.0');
require __DIR__.'/setup-cookies.php';
require __DIR__.'/setup-privacy.php';
WP_CLI::success('Created editable LV/EN pages, native menus/widgets, quote blocks and Media Library attachments.');

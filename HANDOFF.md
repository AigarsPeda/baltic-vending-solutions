# Baltic Vending Solutions projekta handoff

## Mērķis

Izveidot WordPress vietni tirdzniecības iekārtu pārdošanai, nomai un aplīmēšanai klienta zīmola stilā. Izstrādi veikt Local App; gatavo vietni vēlāk pilnībā pārcelt uz DigitalOcean. Pirmais posms ir projekta dokumentācija un pielāgoti skripti ar tukšu konfigurāciju.

## Pašreizējais stāvoklis

2026-10-04 ir izveidots projekta sākums. Ir klienta un mērķauditorijas profils, Boost iekārtu izpētes piezīmes, izstrādes un izvietošanas instrukcijas, seši izpildāmi skripti un konfigurācijas piemērs. WordPress instalācija, tēma, Local vietne, domēns un droplet vēl nav izveidoti. Reālā konfigurācija nav aizpildīta. Šajā posmā nav veikta WordPress vai DigitalOcean izvietošana.

GitHub mērķis ir `https://github.com/AigarsPeda/baltic-vending-solutions.git`, zars `main`. Pirms pirmā commit repozitorijs pārbaudīts un bija tukšs. Aktuālo commit un publicēšanas stāvokli pārbaudīt ar `git status`, `git log -1` un `git remote -v`.

## Faili un atrašanās vietas

| Vienība | Atrašanās vieta |
| --- | --- |
| Projekts | `/Users/aigarspeda/Desktop/Baltic-Vending-Solutions` |
| Klienta profils | `CLIENT_PROFILE.md` |
| Iekārtu izpēte | `docs/equipment-research.md` |
| Izstrāde un izvietošana | `docs/development-and-deployment.md` |
| Tēmas paredzētā vieta | `theme/baltic-vending-solutions/`, vēl nav izveidota |
| Konfigurācijas piemērs | `scripts/baltic-vending-solutions.env.example` |
| Privātā konfigurācija | `scripts/baltic-vending-solutions.env`, ignorēta Git |
| Pārbaudes | `scripts/tests/smoke.py` |

JanogaGo HANDOFF izmantots kā darbplūsmas piemērs. Tā servera adrese, domēns, SSH dati, WordPress ierakstu ID, klientu sasniegumi un pārtikas katalogs nepieder šim projektam. Skripti nedrīkst mērķēt uz JanogaGo instalāciju pēc noklusējuma. Abas sākotnējās Pages specifikācijas paliek `/Users/aigarspeda/Desktop/JanogaGo-doc/automati/`; pārnesams kopsavilkums ir izpētes piezīmēs.

## Satura un dizaina pamats

Vispirms lasīt `CLIENT_PROFILE.md`. Galvenās auditorijas ir uzņēmumi, kas vēlas tirgot savus produktus, un jauni uzņēmēji, kas grib sākt ar vienu iekārtu. Šīs ir pasūtītāja idejas un izpētes darba hipotēzes. Tās nav intervijās apstiprināti klientu profili.

Piedāvājums ir iekārtas iegāde vai noma un aplīmējums. Nepārņemt JanogaGo pārvaldītas ēdināšanas biznesa solījumus. Juridiskais uzņēmums, teritorija, cenas, logo, kontakti, nomas un servisa nosacījumi vēl jānoskaidro. LV pamatvaloda un iespējamā EN versija ir darba ieteikums, vēl jāapstiprina.

Pasūtītājs precizēja, ka abas lokālās Pages specifikācijas ir mūsu piedāvājums, tāpēc tās ir primārais produktu satura pamats. Mūsu Compact komplektācijā Vision AI ir norādīts kā iekļauts; ražotāja kopējā klāstā tas ir opcija. Ražotāja vietne šo piedāvājumu neatceļ. Smart Fridge Series jāprecizē ar modeļa kodu. Nepublicēt aptuvenus izmērus vai garantētu peļņu. Aplīmējuma demonstrācijas skaidri norādīt kā vizualizācijas, kamēr nav īstu klientu piemēru.

## WordPress izstrādes principi

- Saturu, produktu informāciju, foto, navigāciju un kontaktus uzturēt WordPress redaktorā, Media Library un iestatījumos. Rutīnas redakcijām jābūt iespējamām bez koda izvietošanas.
- Tēmas sākotnējā satura izveide nedrīkst pārrakstīt redaktora saglabātās vai apzināti dzēstās sadaļas.
- Foto glabāt uploads ar Media Library ierakstiem. Repozitorijā turēt tēmas kodu un nepieciešamos interfeisa aktīvus.
- Pirms dizaina noskaidrot identitāti; sākuma virziens ir skaidra B2B produktu vietne ar reāliem iekārtu attēliem un modeļu salīdzinājumu.
- Pieprasījuma formai jāpārbauda saglabāšana, kļūdu atgriezeniskā saite un e-pasta piegāde. Saņēmējam jābūt rediģējamam WordPress.

## Skriptu darbība

`sync-code-to-local.sh` kopē tēmu Local. `sync-code-to-droplet.sh` kopē tēmu serverī un veido iepriekšējās tēmas arhīvu. `sync-plugins-to-droplet.sh` kopē pluginus un mu-plugins ar servera rezerves kopiju. `sync-uploads-to-droplet.sh` kopē tikai failus bez dzēšanas. `push-db-to-droplet.sh` un `pull-db-from-droplet.sh` aizvieto attiecīgā galamērķa pilnu datubāzi pēc rezerves kopijas. Visiem ir `--help` un `--dry-run`; datubāzes dry-run ir pieejamības pārbaude.

Četri servera/DB skripti pielāgoti no JanogaGo avota. Noņemti iepriekšējā projekta iestatījumi, pievienota kopīga konfigurācijas pārbaude, DB preflight un Local socket parametri. Tēmas un backup ceļi ir šī projekta konfigurācija, nevis pārņemti no esoša servera. Reālā `.env`, SQL un atslēgas ir ignorētas Git.

Izolētās pārbaudes ir sekmīgas. Tās pārbauda Bash sintaksi, palīdzību, tukšās konfigurācijas apturēšanu, konfigurācijas faila noklusējumu, dry-run bez izmaiņām, nederīgus ceļus un rezerves kopiju secību. Servera pārbaudēs SSH un WP-CLI ir aizvietoti ar testa procesiem. Reālais datorā instalētais rsync pārbaudīts pagaidu Local struktūrā, arī tēmas failu kopēšana, novecojušu failu dzēšana un SSH atslēgas ceļš ar atstarpi. Šie skripti vēl nav pārbaudīti īstā jaunā Local vietnē vai droplet. Tie neinstalē serveri. Pilnai pārcelšanai vajag WordPress pamatu un servera konfigurāciju, tēmu, pluginus, uploads un datubāzi. Git push nav vietnes izvietošana. Detalizēta secība ir izvietošanas dokumentā.

## Kas darbojās

Mūsu piedāvājuma Pages tabulas nodrošināja sākotnējos produkta parametrus; Boost vietne papildināja informāciju par platformu un ražotāja klāstu. JanogaGo vispārīgie sinhronizācijas skripti deva izmantojamu pamatu. Mērķa GitHub repozitorija pieejamība pārbaudīta ar `git ls-remote`.

## Ierobežojumi un neveiksmīgās pieejas

Pirmais GitHub pieprasījums no ierobežotās tīkla vides nespēja atrisināt hostname. Autorizēts tīkla pieprasījums izdevās. Boost minēšana vien neapstiprina izplatītāja statusu, funkciju komplektāciju vai servisu Latvijā. JanogaGo selektīvie satura un pārtikas helperi nav pārnesami bez pielāgošanas jaunajam datu modelim.

## Nākamie darbi

1. Apstiprināt juridisko nosaukumu, identitāti, kontaktus, valodas, teritoriju un pārdošanas/nomas/servisa nosacījumus.
2. Precizēt abus iekārtu modeļus, komplektāciju un foto izmantošanas tiesības.
3. Izveidot atsevišķu Local vietni un aizpildīt tās runtime laukus privātajā konfigurācijā.
4. Izveidot tēmu, rediģējamu produktu saturu un pamatlapas pēc klienta profila.
5. Pārbaudīt Local desktop/mobile, tastatūru, formas un izvēlētās valodas.
6. Sagatavot šī projekta droplet, domēnu, HTTPS, SMTP un backup plānu. Tikai pēc tam aizpildīt remote konfigurāciju.
7. Pārskatīt dry-run, veikt autorizēto pirmo pilno pārcelšanu un pierakstīt faktisko servera stāvokli un atjaunošanas kopiju ceļus šajā failā.

# Local izstrāde un DigitalOcean izvietošana

Current deployment, 2026-10-05: the full Local site is live at [http://167.71.44.25/](http://167.71.44.25/), without a domain. The ignored environment file now contains the correct droplet variables. See [the deployment record](droplet-deployment.md) for server setup, backups and verification. The workflow below remains the general transfer reference.

Skripti pielāgoti no JanogaGo projekta četrām vispārīgām sinhronizācijas darbībām. Konkrētā projekta servera dati, tēma, pārtikas katalogs, saturs un biznesa migrācijas nav pārkopēti.

## Local App

1. Local App izveidot jaunu WordPress vietni šim projektam un palaist to.
2. Reālos šīs vietnes iestatījumus ierakstīt ignorētajā `scripts/baltic-vending-solutions.env`.
3. Tēmas avotu veidot `theme/baltic-vending-solutions/`.
4. Pārbaudīt un kopēt tēmu ar zemāk norādītajām komandām. Aktivizēt tēmu WordPress adminā.

```bash
./scripts/sync-code-to-local.sh --dry-run
./scripts/sync-code-to-local.sh
```

`LOCAL_WP_PATH` ir Local vietnes `app/public`. `LOCAL_URL` ir tās `.local` adrese. `LOCAL_PHP_BIN`, `LOCAL_PHP_INI`, `LOCAL_MYSQL_BIN_DIR` un `LOCAL_MYSQL_SOCKET` jāņem no šīs vietnes aktīvā Local runtime. Tie var mainīties pēc Local atjauninājuma. `LOCAL_WP_CLI` norāda WP-CLI PHAR. Mac datorā tā parasti ir `/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar`; piemēra failā šī vērtība apzināti nav aizpildīta.

PHP skripti lieto norādīto MySQL socket arī mysqli un PDO savienojumiem. `BACKUP_DIR` ir privāta vietējā mape ārpus WordPress. `BACKUP_KEEP` ir neobligāts pozitīvs skaitlis, pēc noklusējuma 3. Vecākas attiecīgā datubāzes skripta kopijas pēc veiksmīgas izpildes tiek dzēstas; tēmu un pluginu arhīvi automātiski netiek dzēsti.

Local tēmas sinhronizācija padara tikai `wp-content/themes/baltic-vending-solutions/` par avota kopiju, tāpēc novecojušus tēmas failus dzēš. Augšupielādētas fotogrāfijas turēt WordPress uploads un Media Library.

## Servera sagatavošana

Šie ir pārcelšanas skripti, nevis droplet izveides vai operētājsistēmas instalācijas skripti. Pirms to izmantošanas jāsagatavo SSH piekļuve, PHP un MySQL vai MariaDB, WordPress, WP-CLI, rsync un tīmekļa serveris. Skripti paredz Linux serveri ar `www-data` failu īpašnieku un attālināti lieto `wp --allow-root`. Konfigurētajam SSH lietotājam jābūt šīm darbībām nepieciešamajām tiesībām.

`REMOTE_HOST` ir `user@hostname` vai `user@IPv4`, `SSH_KEY` ir vietējās privātās atslēgas ceļš, `REMOTE_WP_PATH` ir atsevišķs šī projekta WordPress katalogs. `REMOTE_URL` ir gala vietnes HTTP(S) adrese, `REMOTE_BACKUP_DIR` ir privāta servera mape ārpus publiskā WordPress. Attālinātos ceļus lietot bez atstarpēm. SSH servera atslēgu sākumā pārbaudīt un pievienot zināmo hostu sarakstam; skripti to automātiski neapstiprina.

DNS, domēns, HTTPS sertifikāts, atjaunošana, wp-config datubāzes dati, servera rezerves kopiju politika un SMTP savienojums jāizveido serverim. Vietējo `wp-config.php` nekopēt uz serveri. Skripti to nepārsūta.

## Pirmā pilnas vietnes pārcelšana

WordPress vietne sastāv no koda, pluginu failiem, uploads un datubāzes. Git commit satur dokumentus, skriptus un vēlāk pielāgoto tēmas kodu. Ar Git push vai tēmas sinhronizāciju vien nepietiek.

Sagatavot svaigu servera WordPress instalāciju, pārskatīt skriptu dry-run un tad pārcelt tēmu, pluginus un uploads. Pluginu skripts nedzēš servera papildu pluginus un neaktivizē tos. Iekļautie komerciālie plugini drīkst tikt pārcelti tikai saskaņā ar licenci.

```bash
./scripts/sync-code-to-droplet.sh --dry-run
./scripts/sync-plugins-to-droplet.sh --dry-run
./scripts/sync-uploads-to-droplet.sh --dry-run
./scripts/push-db-to-droplet.sh --dry-run
```

Datubāzes dry-run pārbauda pieejamību un Local home adresi, neaprēķina ierakstu izmaiņu sarakstu. Failu dry-run vēl neesošu attālinātu galamapi var parādīt kā jaunizveidojamu. Apply sākumā saglabā esošās tēmas vai pluginu kopijas un izveido galamapes.

Pēc pārskatīšanas un autorizētas pirmās izvietošanas palaist atbilstošos skriptus bez `--dry-run`, datubāzi pēdējo. Pilns push aizvieto arī servera lietotājus, iestatījumus, spraudņu datus un visus pieprasījumus. Tas nav ikdienas satura publicēšanas paņēmiens. Local datubāzi push nemaina; WP-CLI izveido eksportu ar serializētu vērtību URL pārrakstīšanu. WordPress GUID kolonnas paliek nemainītas.

Uploads skripts pats neveido Media Library ierakstus. Pirmajā pilnajā pārcelšanā tos nodrošina datubāzes imports. Atsevišķa failu kopēšana vēlāk nenodrošina pilnu jaunu satura ierakstu pārcelšanu.

Pēc pārcelšanas pārbaudīt WordPress home/siteurl, aktīvo tēmu un pluginus, Media Library attēlus, izvēlnes, valodas, formas, e-pasta konfigurāciju, HTTPS un failu atļaujas. No Local pārnestā SMTP vai analītikas konfigurācija jāpārskata. Izdrukātie arhīvu ceļi jāfiksē HANDOFF; kļūmes gadījumā tie ir manuālas atjaunošanas pamats.

## Turpmākās izmaiņas

Tēmas izmaiņām izmantot tikai tēmas skriptu. Pirms satura pārcelšanas salīdzināt, vai serverī nav jaunāku redaktora izmaiņu. Selektīvu satura sinhronizāciju veidot pēc jaunās tēmas un datu modeļa izstrādes. JanogaGo homepage un pārtikas kataloga helperi ir saistīti ar tā ierakstu tipiem, valodām un migrācijām, tāpēc šeit nav pievienoti.

Pilnai Local atsvaidzināšanai ir `pull-db-from-droplet.sh`. Tas saglabā Local datubāzes kopiju, importē servera datubāzi, pielāgo URL un kopē uploads bez dzēšanas. Tēmas un pluginu kodu tas nepārnes. Darbība aizvieto visu Local saturu, tāpēc vispirms jābūt autorizētam tieši šādam atsvaidzinājumam.

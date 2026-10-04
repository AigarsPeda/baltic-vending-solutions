# Baltic Vending Solutions

WordPress vietnes sākuma repozitorijs iekārtu pārdošanai, nomai un aplīmēšanai klienta zīmola stilā. Izstrāde paredzēta Local App, izvietošana vēlāk DigitalOcean droplet.

Šajā posmā ir dokumenti un sinhronizācijas skripti. WordPress, tēma, Local vietne un droplet vēl nav izveidoti.

- [HANDOFF](HANDOFF.md) apraksta projekta stāvokli un nākamos darbus.
- [Klienta profils](CLIENT_PROFILE.md) nosaka auditoriju, saturu un dizaina virzienu.
- [Iekārtu izpēte](docs/equipment-research.md) apkopo mūsu piedāvājuma specifikācijas un ražotāja papildinformāciju.
- [Local un izvietošana](docs/development-and-deployment.md) apraksta skriptu konfigurāciju un pilnu vietnes pārcelšanu.

GitHub repozitorijs: [AigarsPeda/baltic-vending-solutions](https://github.com/AigarsPeda/baltic-vending-solutions).

## Konfigurācija

```bash
cp scripts/baltic-vending-solutions.env.example scripts/baltic-vending-solutions.env
```

Visi konfigurācijas lauki ir tukši. Reālais `.env` fails ir ignorēts Git. Skripti pārbauda nepieciešamos laukus un apstājas pirms savienojuma vai izmaiņām, ja tie nav aizpildīti. `--help` darbojas bez konfigurācijas. Local un servera ceļi jāpārbauda šim projektam.

## Skripti

| Skripts | Darbība |
| --- | --- |
| `scripts/sync-code-to-local.sh` | Kopē repozitorija tēmu Local instalācijā |
| `scripts/sync-code-to-droplet.sh` | Kopē tēmu droplet, saglabā iepriekšējās tēmas arhīvu |
| `scripts/sync-plugins-to-droplet.sh` | Kopē plugins un mu-plugins, saglabā servera kopiju |
| `scripts/sync-uploads-to-droplet.sh` | Kopē uploads failus, neveido Media Library ierakstus |
| `scripts/push-db-to-droplet.sh` | Aizvieto servera datubāzi pēc servera rezerves kopijas |
| `scripts/pull-db-from-droplet.sh` | Aizvieto Local datubāzi pēc Local rezerves kopijas un lejupielādē uploads |

Katrs atbalsta `--dry-run`. Failu skripti rāda rsync izmaiņas, datubāzes skripti pārbauda savienojumus un datubāzes, neveicot eksportu vai importu. Push un pull pēc pārbaudes prasa precīzu apstiprinājuma tekstu; `--yes` ir paredzēts jau autorizētai datubāzes aizvietošanai.

## Pārbaudes

```bash
python3 scripts/tests/smoke.py
```

Pārbaudes izmanto pagaidu failus un aizvietotus SSH/WP-CLI procesus. Tās nepieslēdzas droplet un nemaina īstu WordPress vietni. Pēc konfigurācijas nepieciešama arī reāla Local un testa servera pārbaude.

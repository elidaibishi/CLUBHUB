# Analiza e projektit 2: Club Hub

## Qëllimi

Të planifikohet zgjerimi i Club Hub në një mini-aplikacion dinamik për regjistrimin e pjesëmarrësve dhe organizimin e tyre në grupe. Aplikacioni do të përdorë PHP procedural, MySQL dhe Bootstrap. JavaScript do të përdoret vetëm nëse nevojitet për një komponent të ndërfaqes, si menuja mobile.

Ky dokument është analizë dhe propozim. Në këtë fazë nuk shkruhet kod dhe nuk krijohet databazë ose skedar SQL.

## 1. Funksionalitetet kryesore

1. Regjistrimi i një pjesëmarrësi të ri.
2. Shfaqja e listës së pjesëmarrësve.
3. Krijimi i një grupi.
4. Shfaqja e listës së grupeve.
5. Shtimi i një pjesëmarrësi ekzistues në një grup.
6. Shfaqja e pjesëmarrësve të një grupi të zgjedhur.
7. Largimi i një pjesëmarrësi nga një grup pa e fshirë pjesëmarrësin nga sistemi.
8. Parandalimi i anëtarësimit të dyfishtë të të njëjtit pjesëmarrës në të njëjtin grup.

Fshirja e pjesëmarrësve ose grupeve nuk përfshihet në versionin fillestar.

## 2. Rrjedha e përdorimit

1. Organizatori hap aplikacionin dhe zgjedh menaxhimin e pjesëmarrësve ose grupeve.
2. Për të regjistruar dikë, plotëson formularin e pjesëmarrësit. Pas ruajtjes, aplikacioni kthehet te lista dhe tregon konfirmimin.
3. Për të krijuar grup, organizatori plotëson emrin dhe, nëse dëshiron, përshkrimin. Grupi i ri shfaqet në listën e grupeve.
4. Organizatori hap një grup nga lista për të parë anëtarët.
5. Në faqen e grupit, zgjedh pjesëmarrës ekzistues nga lista dhe e shton në grup.
6. Për ta larguar një anëtar, organizatori përdor veprimin “Largo nga grupi” pranë emrit të tij.
7. Aplikacioni heq vetëm lidhjen me atë grup. Pjesëmarrësi mbetet në listën e përgjithshme dhe mund të jetë anëtar i grupeve të tjera.

## 3. Struktura teknike

Zgjidhje e thjeshtë për fillestarë:

- **PHP procedural** për marrjen e formularëve, kontrollin e të dhënave dhe shfaqjen e faqeve.
- **MySQL** për ruajtjen e pjesëmarrësve, grupeve dhe anëtarësimeve.
- **MySQLi procedural** për lidhjen me MySQL dhe query të parametrizuara (`prepared statements`).
- **Bootstrap 5** për container, grid, formularë, butona, tabela dhe paraqitjen responsive.
- **JavaScript** vetëm nëse përdoret komponent Bootstrap që e kërkon, p.sh. navbar collapse.

Faqet mund të përmbajnë HTML dhe PHP bashkë. Veprimet që ndryshojnë të dhënat dërgohen me `POST`; pas ruajtjes së suksesshme, përdoret ridrejtim te faqja përkatëse. Nuk nevojiten OOP, MVC, framework, ORM, API ose arkitekturë me shtresa.

### Strukturë e propozuar e skedarëve

```text
ClubHub/
├── index.php                 # Faqja kryesore dhe lidhjet për menaxhim
├── participants.php          # Regjistrimi dhe lista e pjesëmarrësve
├── groups.php                # Krijimi dhe lista e grupeve
├── group.php                 # Anëtarët dhe veprimet për një grup
├── includes/
│   ├── db.php                # Lidhja me MySQL
│   ├── header.php            # Pjesa e përbashkët e sipërme
│   └── footer.php            # Pjesa e përbashkët e poshtme
├── assets/
│   └── css/
│       └── style.css         # Bootstrap e para, pastaj stilet e Club Hub
└── database/
    └── schema.sql            # Skema SQL për krijim manual në fazën e implementimit
```

Kjo strukturë është propozim, jo krijim skedarësh. Para implementimit duhet vendosur nëse `index.html` ekzistues do të zëvendësohet me `index.php`, apo landing page-i do të mbahet veçmas nga aplikacioni. Kredencialet e databazës nuk vendosen në kod publik ose në repository.

## 4. Tabelat e propozuara

Nevojiten tri tabela për të ruajtur entitetet dhe lidhjen shumë-me-shumë:

### `participants` — pjesëmarrësit

| Fusha | Lloji i përafërt | Qëllimi |
|---|---|---|
| `id` | numër i plotë, çelës primar, auto-increment | Identifikues unik |
| `full_name` | tekst, i detyrueshëm | Emri i plotë |
| `email` | tekst, opsional ose i detyrueshëm sipas vendimit | Kontakt dhe mundësi për identifikim |
| `created_at` | datë/orë, vlerë automatike | Koha e regjistrimit |

### `groups` — grupet

| Fusha | Lloji i përafërt | Qëllimi |
|---|---|---|
| `id` | numër i plotë, çelës primar, auto-increment | Identifikues unik |
| `name` | tekst, i detyrueshëm | Emri i grupit |
| `description` | tekst, opsional | Përshkrim i shkurtër |
| `created_at` | datë/orë, vlerë automatike | Koha e krijimit |

### `participant_groups` — anëtarësimet

| Fusha | Lloji i përafërt | Qëllimi |
|---|---|---|
| `participant_id` | numër i plotë, çelës i huaj | Pjesëmarrësi i lidhur |
| `group_id` | numër i plotë, çelës i huaj | Grupi i lidhur |
| `joined_at` | datë/orë, vlerë automatike | Koha e anëtarësimit |

Çifti (`participant_id`, `group_id`) duhet të jetë unik, për shembull si çelës primar i përbërë, për të mos lejuar dy rreshta për të njëjtin anëtarësim.

Fushat e kontaktit dhe detajet e grupeve janë propozime minimale. Vendimi përfundimtar nëse email-i është i detyrueshëm dhe unik duhet marrë para implementimit.

## 5. Relacionet ndërmjet tabelave

- Një pjesëmarrës mund të ketë zero, një ose shumë anëtarësime në `participant_groups`.
- Një grup mund të ketë zero, një ose shumë anëtarësime në `participant_groups`.
- `participants` dhe `groups` kanë marrëdhënie shumë-me-shumë, të zbatuar përmes tabelës lidhëse `participant_groups`.
- `participant_groups.participant_id` referon `participants.id`.
- `participant_groups.group_id` referon `groups.id`.

Kjo strukturë e mban një kopje të të dhënave të pjesëmarrësit dhe një rresht të veçantë për çdo anëtarësim.

## 6. Skemë e thjeshtë tekstuale e databazës

```text
PARTICIPANTS
  id (PK)
  full_name
  email
  created_at
       1
       |
       | shumë anëtarësime
       *
PARTICIPANT_GROUPS
  participant_id (PK, FK -> participants.id)
  group_id       (PK, FK -> groups.id)
  joined_at
       *
       | shumë anëtarësime
       |
       1
GROUPS
  id (PK)
  name
  description
  created_at
```

`PK` do të thotë çelës primar; `FK` do të thotë çelës i huaj. Dy fushat e shënuara si PK në `PARTICIPANT_GROUPS` përbëjnë çelësin primar të përbërë.

## 7. Si trajtohet pjesëmarrësi në disa grupe

Pjesëmarrësi ruhet vetëm një herë në `participants`. Për çdo grup ku anëtarësohet, shtohet një rresht i ri në `participant_groups`, me të njëjtin `participant_id` dhe një `group_id` tjetër.

Shembull tekstual:

```text
participants:
  12 | Arta Shembull

participant_groups:
  12 | 3
  12 | 5

Kjo do të thotë: pjesëmarrësja me id 12 është në grupet me id 3 dhe 5.
```

Çelësi unik i përbërë lejon grupet e ndryshme për të njëjtin pjesëmarrës, por nuk lejon përsëritjen e çiftit `12 | 3`. Për ta larguar pjesëmarrësen nga grupi 3, hiqet vetëm rreshti `12 | 3`; rreshti i grupit 5 dhe të dhënat e saj në `participants` mbeten.

## 8. Faqet ose skedarët që mund të nevojiten

- `index.php` — faqe hyrëse e aplikacionit dhe navigim.
- `participants.php` — formulari i regjistrimit dhe tabela/lista e pjesëmarrësve.
- `groups.php` — formulari i krijimit dhe lista e grupeve.
- `group.php?id=...` — anëtarët e grupit, formulari për shtim dhe veprimi për largim.
- `includes/db.php` — lidhja me MySQL, e përfshirë nga faqet PHP që përdorin databazën.
- `includes/header.php` dhe `includes/footer.php` — markup i përbashkët për të shmangur përsëritjen.
- `assets/css/style.css` — personalizimet vizuale mbi Bootstrap.
- `database/schema.sql` — përshkrimi SQL i tabelave, për t'u përdorur vetëm kur të fillojë implementimi.

Nuk kërkohet skedar JavaScript i personalizuar për funksionet bazë.

## 9. Rrjedha Browser → PHP → MySQL → PHP → Browser

```text
Browser
  |  Kërkon faqe ose dërgon formular me GET/POST
  v
PHP procedural
  |  Lexon kërkesën dhe kontrollon të dhënat
  |  Përgatit query MySQLi me parametra
  v
MySQL
  |  Lexon ose ruan të dhënat dhe kthen rezultatin
  v
PHP procedural
  |  Përgatit HTML-në dhe shfaq mesazh suksesi/gabimi
  |  Për veprime të suksesshme POST, ridrejton te faqja përkatëse
  v
Browser
     Shfaq listën, formularin ose detajet e përditësuara
```

Shembull: kur hapet faqja e një grupi, PHP merr `id` e grupit, lexon anëtarësimet dhe pjesëmarrësit nga MySQL dhe kthen HTML me listën e anëtarëve. Kur dërgohet formulari “Largo nga grupi”, PHP kontrollon ID-të dhe fshin vetëm rreshtin përkatës nga `participant_groups`.

## 10. Siguria dhe sjellja bazë

- Përdor prepared statements të MySQLi për të dhënat që vijnë nga formulari ose URL-ja.
- Kontrollo në PHP emrin, email-in nëse përdoret, dhe ID-të para query-ve.
- Escapo tekstet para se t'i shfaqësh në HTML.
- Përdor `POST` për shtimin dhe largimin e anëtarësimeve; mos ndrysho të dhëna përmes një linku `GET`.
- Largimi duhet të synojë çiftin e saktë pjesëmarrës-grup, jo të fshijë pjesëmarrësin.
- Shfaq mesazh të kuptueshëm pas suksesit ose gabimit dhe mos shfaq kredenciale apo gabime të brendshme të databazës.

## 11. Acceptance criteria

- Organizatori mund të regjistrojë pjesëmarrës me fushat e vendosura dhe t'i shohë në listë.
- Organizatori mund të krijojë grupe dhe t'i shohë në listë.
- Çdo grup mund të hapet për të parë vetëm pjesëmarrësit e atij grupi.
- I njëjti pjesëmarrës mund të jetë në më shumë se një grup pa u kopjuar në tabelën `participants`.
- Një grup mund të përmbajë shumë pjesëmarrës.
- Aplikacioni nuk lejon anëtarësimin dy herë të të njëjtit pjesëmarrës në të njëjtin grup.
- “Largo nga grupi” heq vetëm anëtarësimin; pjesëmarrësi vazhdon të shfaqet në listën e përgjithshme dhe në grupet e tjera.
- Formularët kontrollohen në PHP dhe japin mesazh të qartë për suksesin ose gabimin.
- Query-t që përdorin të dhëna të përdoruesit janë të parametrizuara dhe teksti i shfaqur është i escapuar.
- Faqet përdorin Bootstrap për layout dhe funksionojnë në mobile, tablet dhe desktop.
- Implementimi mbetet PHP procedural dhe MySQL; nuk përdor OOP, MVC, framework, ORM ose arkitekturë të avancuar.

## 12. Zgjerimet e kërkuara për Club Hub

Kërkesat e mëvonshme e zgjerojnë scope-in fillestar të këtij dokumenti:

- Pjesëmarrësit mund të kërkohen sipas emrit/email-it, filtrohen sipas grupit dhe renditen sipas datës, emrit ose grupit.
- Administratori mund të editojë/fshijë pjesëmarrës; fshirja konfirmohet dhe pastron vetëm lidhjet përkatëse përpara fshirjes së pjesëmarrësit.
- Faqja e detajeve të pjesëmarrësit shfaq të dhënat dhe grupet e tij, si dhe lejon shtimin në disa grupe.
- Paneli shfaq regjistrimet e fundit, grupin me më shumë pjesëmarrës dhe pjesëmarrësit pa grup.
- Lista mund të eksportohet në CSV.
- Faqet e menaxhimit kërkojnë autentikim administratori; fjalëkalimet ruhen të hash-uara dhe formularët POST mbrohen me token CSRF.
- Databaza e aplikacionit quhet `clubhub`; të dhënat e databazës së mëparshme `techworkshop` mund të kopjohen me migrimin përkatës.

Këto zgjerime e zëvendësojnë pjesën e mëparshme që përjashtonte fshirjen dhe kufizonte funksionet te versioni fillestar.

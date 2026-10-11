# Plani i implementimit 2: Club Hub

Ky plan bazohet në [PROJECT2_ANALYSIS.md](./PROJECT2_ANALYSIS.md). Përshkruan rendin e punës; në këtë fazë nuk krijohen kod, databazë ose skedarë SQL.

## Rendi i implementimit

1. Konfirmo vendimin e analizës: a do të zëvendësohet `index.html` me `index.php`, apo landing page-i do të qëndrojë i veçantë.
2. Përgatit MySQL në mjedisin lokal dhe krijo databazën e aplikacionit.
3. Krijo tabelat dhe relacionet sipas seksionit “Databaza”.
4. Përgatit lidhjen MySQLi procedurale dhe pjesët e përbashkëta të faqes.
5. Krijo faqen e pjesëmarrësve: formulari i regjistrimit dhe lista.
6. Krijo faqen e grupeve: formulari i krijimit dhe lista.
7. Krijo faqen e detajeve të grupit: lista e anëtarëve, shtimi dhe largimi i anëtarësimit.
8. Lidhi faqet me navigim Bootstrap dhe testo rrjedhën e plotë në mobile, tablet dhe desktop.

## Databaza që duhet krijuar

Krijo një databazë MySQL për Club Hub, me tri tabela:

### `participants`

- `id` — çelës primar, numër unik, auto-increment.
- `full_name` — emri i plotë, i detyrueshëm.
- `email` — email; detyrueshmëria dhe unikaliteti konfirmohen para krijimit të skemës.
- `created_at` — koha e regjistrimit.

### `groups`

- `id` — çelës primar, numër unik, auto-increment.
- `name` — emri i grupit, i detyrueshëm.
- `description` — përshkrim opsional.
- `created_at` — koha e krijimit.

### `participant_groups`

- `participant_id` — çelës i huaj drejt `participants.id`.
- `group_id` — çelës i huaj drejt `groups.id`.
- `joined_at` — koha e anëtarësimit.
- Çelës primar i përbërë (`participant_id`, `group_id`) që parandalon anëtarësime të dyfishta.

Relacione:

```text
participants 1 ─── shumë participant_groups shumë ─── 1 groups
```

Kjo zbaton marrëdhënien shumë-me-shumë: të dhënat e pjesëmarrësit ruhen një herë, ndërsa secili grup ku merr pjesë përfaqësohet nga një rresht në `participant_groups`.

## Fajllat dhe roli i secilit

```text
ClubHub/
├── index.php
├── participants.php
├── groups.php
├── group.php
├── includes/
│   ├── db.php
│   ├── header.php
│   └── footer.php
├── assets/
│   └── css/
│       └── style.css
└── database/
    └── schema.sql
```

- **`index.php`** — faqja hyrëse me navigim drejt pjesëmarrësve dhe grupeve. Vendimi nëse zëvendëson landing page-in ekzistues merret para implementimit.
- **`participants.php`** — shfaq formularin e regjistrimit dhe listën e pjesëmarrësve.
- **`groups.php`** — shfaq formularin e krijimit dhe listën e grupeve; secili grup lidhet me faqen e detajeve.
- **`group.php?id=...`** — shfaq anëtarët e grupit të zgjedhur; përmban formularin për të shtuar pjesëmarrës dhe veprimin për largimin e një anëtarësimi.
- **`includes/db.php`** — krijon lidhjen MySQLi procedurale. Kredencialet caktohen në mjedis lokal dhe nuk publikohen.
- **`includes/header.php`** — HTML i përbashkët i dokumentit dhe navbar-i Bootstrap.
- **`includes/footer.php`** — footer-i dhe përfshirjet e përbashkëta të faqes.
- **`assets/css/style.css`** — ruan personalizimet e nevojshme të Club Hub; Bootstrap ngarkohet para tij.
- **`database/schema.sql`** — përmban përshkrimin SQL të tri tabelave për krijimin e databazës/tabelave gjatë fazës së implementimit.

## Formularët që duhen

1. **Regjistrimi i pjesëmarrësit** në `participants.php`
   - Emri i plotë.
   - Email-i sipas vendimit të marrë për detyrueshmërinë dhe unikësinë.
   - Dërgohet me `POST`.
2. **Krijimi i grupit** në `groups.php`
   - Emri i grupit.
   - Përshkrimi opsional.
   - Dërgohet me `POST`.
3. **Shtimi në grup** në `group.php`
   - Zgjedhje e një pjesëmarrësi ekzistues nga listë zgjedhjeje.
   - Identifikuesi i grupit të zgjedhur dërgohet bashkë me kërkesën `POST`.
4. **Largimi nga grupi** në `group.php`
   - Buton “Largo nga grupi” pranë secilit anëtar.
   - Dërgon me `POST` identifikuesit e pjesëmarrësit dhe grupit.

Të gjitha vlerat kontrollohen në PHP në server, edhe nëse formulari përdor atributet bazë HTML të validimit.

## Si ruhet një pjesëmarrës

1. Organizatori plotëson formularin e pjesëmarrësit.
2. `participants.php` merr fushat me `POST`, kontrollon vlerat dhe shfaq gabim të kuptueshëm nëse mungon e dhëna e kërkuar.
3. PHP përdor MySQLi procedural dhe prepared statement për të shtuar rreshtin në `participants`.
4. Pas suksesit, faqja ridrejton te lista e pjesëmarrësve dhe shfaq konfirmim.
5. Lista merret nga tabela `participants`; të dhënat e paraqitura në HTML escapohen.

## Si krijohet një grup

1. Organizatori plotëson emrin dhe, nëse dëshiron, përshkrimin.
2. `groups.php` merr të dhënat me `POST` dhe verifikon që emri është plotësuar.
3. PHP ruan grupin e ri në `groups` me prepared statement.
4. Pas suksesit ridrejton te lista e grupeve dhe shfaq konfirmim.

## Si lidhet pjesëmarrësi me një ose më shumë grupe

1. Organizatori hap `group.php?id=...` për grupin përkatës.
2. Formulari i shtimit ofron pjesëmarrësit ekzistues nga `participants`.
3. Organizatori zgjedh pjesëmarrësin dhe dërgon formularin me `POST`.
4. PHP verifikon që ID-ja e pjesëmarrësit dhe ID-ja e grupit ekzistojnë.
5. PHP shton çiftin (`participant_id`, `group_id`) në `participant_groups`.
6. Çelësi i përbërë pengon përsëritjen e të njëjtit çift; aplikacioni e kthen rastin në mesazh të qartë, jo në dështim të pashpjeguar.
7. Për grupet e tjera, krijohen rreshta të tjerë me të njëjtin `participant_id` dhe `group_id` të ndryshëm.

## Si shfaqen pjesëmarrësit e një grupi

1. `group.php` lexon dhe kontrollon `id` e grupit nga URL-ja.
2. PHP përdor një `SELECT` me `JOIN` mes `participant_groups` dhe `participants`, të filtruar sipas `group_id`.
3. Rezultatet shfaqen si listë ose tabelë Bootstrap brenda faqes së grupit.
4. Nëse grupi nuk ekziston, ose nuk ka anëtarë, shfaqet mesazh i qartë.

## Si largohet vetëm lidhja me grupin

1. Organizatori shtyp “Largo nga grupi” te rreshti i anëtarit të duhur.
2. Formulari dërgon me `POST` `participant_id` dhe `group_id`.
3. `group.php` kontrollon vlerat dhe fshin vetëm rreshtin nga `participant_groups` ku përputhen të dy ID-të.
4. PHP ridrejton te i njëjti grup dhe rifreskon listën.
5. Rreshti në `participants` nuk preket; pjesëmarrësi vazhdon të shfaqet në listën e përgjithshme dhe në grupet e tjera.

## Përdorimi i kufizuar i JavaScript

- Mund të përdoret vetëm Bootstrap bundle nëse navbar-i përdor collapse në mobile.
- Nëse nuk përdoret komponent Bootstrap që kërkon JavaScript, mos përfshi JavaScript.
- Validimi, ruajtja dhe largimi i anëtarësimeve funksionojnë me formularë HTML, PHP dhe `POST`, pa JavaScript të personalizuar.

## Checklist për testim

- [ ] Databaza dhe tri tabelat krijohen sipas analizës.
- [ ] Çelësat e huaj lidhin saktë tabelat dhe çifti i anëtarësimit është unik.
- [ ] Pjesëmarrësi i ri ruhet dhe shfaqet në listën e përgjithshme.
- [ ] Grupi i ri ruhet dhe shfaqet në listën e grupeve.
- [ ] Pjesëmarrësi ekzistues mund të shtohet në një grup.
- [ ] I njëjti pjesëmarrës mund të shtohet në një grup të dytë pa u kopjuar te `participants`.
- [ ] Shtimi i të njëjtit pjesëmarrës në të njëjtin grup për herë të dytë parandalohet me reagim të qartë.
- [ ] Faqja e grupit shfaq vetëm anëtarët e atij grupi.
- [ ] Largimi e heq anëtarësimin nga grupi, por jo pjesëmarrësin ose anëtarësimet e tij të tjera.
- [ ] Fushat dhe ID-të kontrollohen në PHP; query-t përdorin prepared statements.
- [ ] Teksti i përdoruesit escapohen para shfaqjes në HTML.
- [ ] Formularët japin konfirmim pas suksesit dhe gabime të kuptueshme pas dështimit.
- [ ] Navbar-i dhe faqet Bootstrap përdoren në mobile, tablet dhe desktop.
- [ ] Nuk janë shtuar funksione jashtë scope-it, OOP, classes, MVC, framework ose ORM.

## Zgjerimet e kërkuara pas planit fillestar

Kërkesat e mëvonshme të projektit e zgjerojnë planin fillestar. Implementimi aktual përfshin:

- `clubhub` si emrin e databazës; `migrate_to_clubhub.sql` kopjon pjesëmarrësit, grupet dhe anëtarësimet nga databaza e vjetër `techworkshop`, nëse ekziston.
- Tabelën `administrators`, `login.php`, `setup_admin.php`, `logout.php` dhe mbrojtjen e faqeve të menaxhimit me sesion.
- Kërkim me emër/email, filtrim sipas grupit dhe renditje te `participants.php`.
- Editim dhe fshirje transaksionale të pjesëmarrësit; fshirja heq fillimisht lidhjet e tij me grupet.
- `participant.php` për detajet e pjesëmarrësit, grupet e tij dhe shtimin në disa grupe.
- Përmbledhje me statistika dhe pjesëmarrësit e fundit në `index.php`.
- Eksport CSV për listën e filtruar në `export_participants.php`.
- Token CSRF në formularët që ndryshojnë të dhëna dhe konfirmim para fshirjes/largimit.

Për hapat e importimit të databazës, konfigurimin dhe krijimin e administratorit, ndiq [ADMIN_GUIDE.md](./ADMIN_GUIDE.md).

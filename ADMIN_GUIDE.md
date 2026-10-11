# Udhëzuesi i administratorit të Club Hub

## 1. Krijimi i databazës

Përdor një instalim PHP 7.3 ose më të ri me MySQLi aktiv dhe MySQL/InnoDB.

1. Në phpMyAdmin ose MySQL, ekzekuto `database.sql`. Ky skedar krijon databazën `clubhub`, tabelat e pjesëmarrësve/grupeve/anëtarësimeve dhe tabelën e administratorëve.
2. Kontrollo vlerat e lidhjes në `includes/db.php` dhe vendos host-in, përdoruesin e MySQL-it dhe fjalëkalimin e instalimit tënd. Emri i databazës duhet të jetë `clubhub`.
3. Nëse ke të dhëna në databazën e vjetër `techworkshop`, bëj fillimisht kopje rezervë. Pasi të jetë krijuar databaza e re `clubhub`, ekzekuto `migrate_to_clubhub.sql`. Migrimi i kopjon rreshtat, por nuk e fshin databazën e vjetër. Mos e ekzekuto mbi `clubhub` me të dhëna që dëshiron t'i ruash pa i kontrolluar më parë identifikuesit.

## 2. Krijimi i administratorit dhe hyrja

Në kompjuterin ku po përgatitet ky projekt, XAMPP është instaluar, folderi i projektit është lidhur me `htdocs` dhe databaza `clubhub` është krijuar. Apache dhe MariaDB dëgjohen vetëm në `127.0.0.1`. Nëse serveri nuk është aktiv pas rindezjes së kompjuterit, hape `C:\xampp\xampp-control.exe` dhe shtyp **Start** te Apache dhe MySQL.

1. Instalo dhe hape XAMPP, pastaj shtyp **Start** për Apache dhe MySQL.
2. Vendose projektin `ClubHub` në folderin `htdocs` të instalimit XAMPP (zakonisht `C:\xampp\htdocs\ClubHub`). Nëse dëshiron ta mbash burimin në Desktop dhe ndryshimet të pasqyrohen menjëherë, krijo një junction nga Command Prompt:
   ```text
   mklink /J C:\xampp\htdocs\ClubHub C:\Users\hp\Desktop\ClubHub
   ```
   Përdore junction vetëm kur `C:\xampp\htdocs\ClubHub` nuk ekziston ende.
3. Mos e hap `index.php` nga File Explorer ose me adresë `file:///...`; kjo e shfaq kodin PHP si tekst, sepse PHP duhet të përpunohet nga Apache.
   - Nëse e hap faqen publike si fajll lokal, butonat e panelit kontrollojnë `http://localhost/ClubHub/` për server aktiv. Kur Apache nuk përgjigjet, faqja shfaq udhëzim dhe nuk hap kodin PHP.
4. Hape `http://127.0.0.1/ClubHub/setup_admin.php` në shfletues. Nëse folderi i projektit ka emër tjetër, përdor atë emër në adresën pas `127.0.0.1/`.
5. Krijo administratorin me email dhe fjalëkalim prej të paktën 12 karakteresh. Fjalëkalimi ruhet në databazë si hash.
6. Konfigurimi fillestar punon vetëm kur nuk ka ende administrator dhe kërkesa vjen nga `localhost`. Pasi të krijohet llogaria, faqja nuk lejon krijimin e një administratori tjetër.
7. Hyr te `http://127.0.0.1/ClubHub/login.php` ose paneli te `http://127.0.0.1/ClubHub/index.php`. Faqet e panelit ridrejtojnë te hyrja nëse përdoruesi nuk është autentikuar. Përdor butonin “Dil” për të mbyllur sesionin.

## 3. Menaxhimi i të dhënave

- **Pjesëmarrësit:** regjistro një person; përdor kërkimin për emër/email dhe filtrat e grupit/renditjes. “Detajet” hap faqen me informacionin e tij, ndërsa “Edit” lejon ndryshimin e emrit/email-it.
- **Fshirja:** butoni “Fshi” kërkon konfirmim. Pas konfirmimit fshihen pjesëmarrësi dhe lidhjet e tij me grupet; veprimi nuk mund të zhbëhet.
- **Grupet:** dashboard-i dhe faqja `groups.php` tregojnë të gjitha grupet e krijuara dhe numrin aktual të anëtarëve. Hape grupin për të parë listën, për të shtuar pjesëmarrësit e regjistruar që nuk janë ende anëtarë dhe për t'i larguar me konfirmim. Largimi nga një grup nuk e fshin pjesëmarrësin.
- **Regjistrimi i anëtarëve:** regjistroje personin te `participants.php`, pastaj hape grupin për ta shtuar. Lista e zgjedhjes lexohet përsëri nga databaza sa herë hapet grupi, prandaj pjesëmarrësi i ri shfaqet pas regjistrimit.
- **Dashboard:** faqja “Përmbledhja” tregon numrin e pjesëmarrësve/grupeve, grupin dhe numrin e anëtarëve për secilin, pjesëmarrësit e fundit, grupin më të madh dhe personat pa grup.
- **CSV:** te lista e pjesëmarrësve shtyp “Shkarko CSV”. Eksportohen rezultatet që përputhen me filtrat aktivë dhe skedari mund të hapet në Excel.

## 4. Kontroll i shpejtë pas instalimit

- [ ] Vizitori pa hyrë ridrejtohet te faqja e hyrjes kur hap panelin.
- [ ] Administratori i krijuar mund të hyjë me kredencialet e sakta; fjalëkalimi i gabuar refuzohet.
- [ ] Regjistrimi, editimi, kërkimi, filtrimi dhe renditja e pjesëmarrësve funksionojnë.
- [ ] Fshirja kërkon konfirmim dhe nuk lë anëtarësime të papërdorura.
- [ ] Pjesëmarrësi mund të regjistrohet në disa grupe dhe të largohet veçmas nga secili.
- [ ] Dashboard-i shfaq statistika që përputhen me të dhënat.
- [ ] CSV hapet dhe përmban rezultatet e filtruara.

## 5. Para publikimit në internet

Projekti mund të shërbejë faqet HTML nga hostim statik, por paneli PHP kërkon hostim me PHP dhe MySQL. Para se t'ua dërgosh vizitorëve:

- Përdor një llogari të veçantë MySQL me lejet minimale të nevojshme; mos përdor `root` pa fjalëkalim si në konfigurimin tipik lokal të XAMPP. Përditëso kredencialet te `includes/db.php` dhe mos i publiko ato.
- Aktivizo HTTPS dhe vendos një fjalëkalim unik e të fortë për administratorin.
- Krijo administratorin nga kompjuteri i serverit. `setup_admin.php` lejohet vetëm nga `localhost` dhe vetëm para se të ekzistojë administratori i parë.
- Testo hyrjen, regjistrimin, grupet, anëtarësimet, fshirjen dhe eksportin CSV në serverin e publikuar; serveri lokal nuk përfaqëson konfigurimin e hostimit.
- Konfirmo email-in ose kanalin e kontaktit, datat dhe aktivitetet para se t'i reklamosh. Faqja aktuale nuk ka formular publik regjistrimi dhe përfitimet/ofertat e përmendura janë mundësi, jo premtime të konfirmuara.
- Për kartën e shpërndarjes në rrjete sociale, cakto domain-in përfundimtar dhe shto një imazh Open Graph me URL publike absolute. Metadata bazë e titullit dhe përshkrimit është e përfshirë, por domain-i dhe imazhi nuk mund të paracaktohen lokalisht.

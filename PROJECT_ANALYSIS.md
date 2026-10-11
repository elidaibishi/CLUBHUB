# Analiza e projektit: Club Hub

## 1. Qëllimi i projektit

Të planifikohet një landing page one-page për “Club Hub”, që prezanton një workshop në mënyrë të qartë dhe tërheqëse. Versioni i parë do të ndërtohet vetëm me HTML5 dhe CSS3, pa Bootstrap dhe pa JavaScript. Kjo fazë fokusohet te struktura semantike dhe paraqitja vizuale; kalimi në Bootstrap dhe përshtatja responsive do të trajtohen më vonë.

## 2. Audienca

- Persona të interesuar për workshop-in, komunitetin dhe aktivitetet e Club Hub.
- Vizitorë që duan të kuptojnë shpejt çfarë ofron workshop-i, kur zhvillohet dhe si mund të marrin pjesë.
- Pjesëmarrës potencialë që kërkojnë përfitimet, agjendën dhe mënyrën e kontaktit.

Teksti përfundimtar, data, vendndodhja dhe të dhënat e kontaktit duhen konfirmuar para publikimit. Deri atëherë mund të përdoren formulime të përgjithshme ose shënime zëvendësuese të dallueshme.

## 3. Seksionet e faqes

Faqja do të ketë këto seksione, sipas rrjedhës së vizitorit:

1. Navbar
2. Hero
3. Rreth Workshop-it
4. Agjenda
5. Përfitimet
6. Call to Action / Kontakti
7. Footer

## 4. Përmbajtja e propozuar për secilin seksion

### Navbar

- Emri ose logoja tipografike “Club Hub”.
- Lidhje navigimi drejt seksioneve kryesore të faqes.
- Një lidhje ose buton i theksuar për regjistrim/kontakt, që të çon te Call to Action.
- Navigimi do të përdorë lidhje ankorimi HTML; nuk kërkohet JavaScript.

### Hero

- Titull i shkurtër që komunikon vlerën e workshop-it, për shembull: “Mëso, krijo dhe lidhu me komunitetin”.
- Një fjali shpjeguese për temën dhe audiencën.
- Buton kryesor për t’u regjistruar ose për të mësuar më shumë.
- Mundësi për një element vizual statik, si fotografi ose ilustrim, nëse sigurohet material i përshtatshëm.

### Rreth Workshop-it

- Përshkrim i shkurtër i workshop-it dhe i temës së tij.
- Çfarë do të bëjnë ose mësojnë pjesëmarrësit.
- Informacion praktik si formati, vendndodhja dhe data, vetëm pasi të konfirmohen.

### Agjenda

- Programi i workshop-it në rend kohor.
- Për secilën pjesë: ora ose kohëzgjatja, titulli dhe një përshkrim i shkurtër.
- Nëse orari nuk është përcaktuar, struktura mund të paraqesë blloqe të përgjithshme pa shpikur orare.

### Përfitimet

- Listë e shkurtër me rezultate konkrete për pjesëmarrësit, si njohuri praktike, ushtrime dhe mundësi për t’u lidhur me të tjerë.
- Përfitimet të paraqiten me tituj të dallueshëm dhe përshkrime të shkurtra; ikonat janë opsionale.

### Call to Action / Kontakti

- Ftesë e qartë për regjistrim ose për të marrë informacione të mëtejshme.
- Buton/lidhje drejt kanalit të regjistrimit, kur të jetë përcaktuar.
- Të dhëna kontakti ose lidhje të konfirmuara. Nëse nuk ka formular funksional në këtë fazë, të mos paraqitet një formular që krijon përshtypjen se dërgon të dhëna.

### Footer

- Emri i Club Hub dhe një përshkrim i shkurtër opsional.
- Lidhje kontakti dhe rrjete sociale vetëm kur adresat janë të disponueshme.
- Shënimi i të drejtave të autorit.

## 5. Drejtimi vizual

- Pamje e pastër dhe moderne me hierarki të qartë tipografike dhe hapësirë të bollshme mes elementeve.
- Paletë e kufizuar ngjyrash: sfond neutral, tekst me kontrast të lartë dhe një ngjyrë kryesore për butonat/theksimet.
- Tituj të dallueshëm, paragrafë të shkurtër dhe elemente vizuale të përdorura me masë.
- Butonat dhe lidhjet të kenë gjendje vizuale të dallueshme dhe kontrast të lexueshëm.
- HTML semantik dhe CSS i organizuar do ta bëjnë më të lehtë kalimin e mëvonshëm në Bootstrap dhe përshtatjen responsive.
- Nuk do të përfshihen Bootstrap, JavaScript apo veçori që varen prej tij në versionin e parë.

## 6. Struktura e fajllave

Folderi aktual nuk përmban skedarë projekti. Struktura e propozuar për fazën e implementimit është:

```text
ClubHub/
├── PROJECT_ANALYSIS.md
├── index.html
├── css/
│   └── styles.css
└── assets/
    └── images/
```

`index.html` do të përmbajë strukturën semantike të faqes, `css/styles.css` paraqitjen, ndërsa `assets/images/` do të përdoret vetëm nëse nevojiten dhe sigurohen imazhe. Në këtë hap krijohet vetëm `PROJECT_ANALYSIS.md`.

## 7. Hapat kryesorë të implementimit

1. Konfirmimi i temës, audiencës, datës, vendndodhjes, agjendës dhe mënyrës së regjistrimit.
2. Përcaktimi i teksteve përfundimtare dhe i materialeve vizuale të disponueshme.
3. Krijimi i skeletit semantik HTML5 me seksionet dhe ankorat e navigimit.
4. Shtimi i CSS3 për tipografinë, ngjyrat, hapësirat, paraqitjen e kartave dhe gjendjet e lidhjeve/butonave.
5. Kontrolli i rrjedhës së përmbajtjes, lexueshmërisë, kontrastit dhe funksionimit të lidhjeve.
6. Në fazën e ardhshme, migrimi i paraqitjes në Bootstrap dhe implementimi/testimi i dizajnit responsive.

## 8. Kriteret e përfundimit

- Ekziston një faqe one-page me të shtatë seksionet e kërkuara, në rend logjik.
- Navbar-i lundron te seksionet përkatëse me lidhje ankorimi.
- Përmbajtja është e qartë, e qëndrueshme dhe nuk përfshin detaje të pakonfirmuara si fakte.
- Struktura përdor elemente semantike HTML5 dhe paraqitja realizohet me CSS3.
- Versioni i parë nuk përdor Bootstrap apo JavaScript.
- Dizajni është i pastër, modern, i lexueshëm dhe ka kontrast të përshtatshëm.
- Skedarët dhe materialet janë të organizuara sipas strukturës së dakorduar.
- Kodi dhe teksti janë gati për fazën e mëvonshme të migrimit në Bootstrap dhe të përshtatjes responsive.

# Plani i migrimit në Bootstrap 5

## Qëllimi

Të migrohet landing page-i ekzistues i Club Hub në Bootstrap 5 dhe të bëhet responsive pa ndryshuar përmbajtjen, rendin e seksioneve ose identitetin vizual: sfondet e ngrohta, jeshilja e butë, tipografia serif e titujve kryesorë dhe elementet grafike abstrakte.

Faqja aktuale është një dokument i vetëm me navbar, hero, rreth workshop-it, agjendë, përfitime, CTA/kontakt dhe footer. Ky dokument është plan; `index.html` dhe `style.css` nuk ndryshohen gjatë kësaj faze.

## Container dhe Container-fluid

- Përdor Bootstrap `.container` për përmbajtjen e kufizuar në gjerësi: navbar-in, tekstin/ilustrimin e Hero-s, përmbajtjen e seksioneve, CTA-në dhe footer-in.
- Mbaji sfondet dhe ngjyrat e seksioneve në elementet e jashtme të seksioneve, jashtë `.container`, që të shtrihen në gjithë gjerësinë e ekranit.
- Përdor `.container-fluid` vetëm kur kërkohet që një përmbajtje e caktuar të shtrihet pothuajse skaj më skaj. Nuk nevojitet si parazgjedhje për këtë faqe; për ilustrimin e Hero-s mund të përdoret vetëm nëse dizajni i ardhshëm kërkon që ai të dalë përtej kontejnerit.
- Hiq rregullin e personalizuar `.container` pasi faqja të përdorë kontejnerët e Bootstrap-it, për të mos pasur dy përkufizime konkurruese të gjerësisë.

## Grid system: `row` dhe `col`

- **Hero:** vendos kopjen dhe ilustrimin në `.row align-items-center`; përdor kolona si `.col-12 col-lg-6` për secilën anë. Në telefona vendosen njëra pas tjetrës, në ekrane të mëdha krah për krah.
- **Rreth workshop-it:** përdor një `.row` me dy kolona, p.sh. `.col-12 col-lg-5` për titullin dhe `.col-12 col-lg-7` për përshkrimin.
- **Agjenda:** çdo rresht i agjendës mund të jetë `.row align-items-center`; numri, teksti dhe shenja vizuale marrin kolona të përshtatshme, pa e humbur ndarjen me vijë.
- **Përfitimet:** vendos kartat brenda `.row g-4`; secila kartë përdor `.col-12 col-md-6 col-lg-4`. Kjo jep një kolonë në mobile, dy në tablet dhe tri në desktop.
- **CTA:** përdor `.row align-items-center` me një kolonë të gjerë për tekstin dhe një kolonë `col-auto` për lidhjen e veprimit; në mobile rreshtoji vertikalisht.
- **Footer:** përdor `.row` me kolona që rreshtohen vertikalisht në mobile dhe horizontalisht kur ka hapësirë.
- Përdor gutters të Bootstrap-it (`g-*`, `gx-*`, `gy-*`) për hapësirat mes kolonave; shmang margin-e negative ose width-e të personalizuara që dublikojnë grid-in.

## Bootstrap components që mund të përdoren

- **Buttons:** Bootstrap `.btn` për sjelljen bazë dhe fokusin; shto një klasë të projektit, p.sh. `.btn-club`, për gradientin, hijen dhe ngjyrat e Club Hub. Linket vazhdojnë të çojnë te ankorat ekzistuese.
- **Cards:** mund të përdoren elementet `.card` / `.card-body` për strukturën e kartave të përfitimeve. Mbaji ikonën, titullin dhe përshkrimin aktual; personalizo pamjen me CSS-në e projektit.
- **Navbar:** Bootstrap `.navbar` dhe `.navbar-expand-lg` për rreshtimin dhe paraqitjen në ekrane të ndryshme; detajet e sjelljes mobile përshkruhen më poshtë.
- **List group** nuk është i domosdoshëm për agjendën. Lista aktuale semantike me rreshta të personalizuar ruan më mirë stilin dhe renditjen.
- Mos përdor komponentë që nuk i duhen kësaj faqeje, si carousel, modal, dropdown ose form controls.

## Navbar responsive

Përdor strukturën semantike `nav.navbar.navbar-expand-lg`, me `.container` brenda. Marka mbetet në njërën anë dhe lidhjet plus CTA-ja shfaqen në rresht në desktop.

Në mobile, shfaq një buton toggler me `aria-controls`, `aria-expanded` dhe etiketë të qartë; lidhjet vendosen në një panel të lexueshëm nën markë. Për funksionin collapse të Bootstrap 5 duhet të përfshihet Bootstrap JavaScript bundle, i cili është i nevojshëm për këtë komponent (Bootstrap 5 nuk kërkon jQuery). Mos shto kod tjetër JavaScript të personalizuar. Nëse synohet të ruhet kufizimi ekzistues pa JavaScript, mos përdor collapse toggler: lejo që lidhjet të mbështillen në një rresht ose dy përmes utility classes dhe CSS.

Mbaji ankorat aktuale `#about`, `#agenda`, `#benefits` dhe `#contact`, dhe kontrollo që secili link të vazhdojë të çojë te seksioni përkatës.

## Sjellja e kartave dhe blloqeve sipas madhësisë së ekranit

| Pjesa | Mobile | Tablet | Desktop |
|---|---|---|---|
| Hero | Teksti sipër, ilustrimi poshtë; CTA-të mbështillen kur duhet | Dy kolona kur ka hapësirë të mjaftueshme, përndryshe renditje vertikale | Teksti dhe ilustrimi krah për krah |
| Rreth workshop-it | Titulli sipër përshkrimit | Mund të paraqitet në dy kolona nëse teksti mbetet i lexueshëm | Dy kolona me raport të balancuar |
| Agjenda | Një rresht i plotë për secilën pikë; ruaj hapësirën për numrin dhe tekstin | E njëjta listë me kolonat në një rresht | Rreshta të gjerë, me përshkrim dhe shenjë në pozicion të qëndrueshëm |
| Përfitimet | Një kartë për rresht | Dy karta për rresht | Tri karta për rresht |
| CTA | Teksti dhe butoni rreshtohen vertikalisht | Mund të qëndrojnë krah për krah nëse ka vend | Teksti dhe veprimi krah për krah |
| Footer | Elementet rreshtohen vertikalisht | Rreshtim i përzier sipas hapësirës | Marka, përshkrimi dhe copyright në një rresht |

Në çdo madhësi, shmang overflow-in horizontal, mbaj tekstet e agjendës të lexueshme dhe mos e zvogëlo ilustrimin e Hero-s aq sa të bëhet i palexueshëm.

## CSS i personalizuar që duhet të mbetet

Ruaj vetëm CSS-në që mban identitetin dhe detaje që nuk ofrohen nga Bootstrap:

- Variablat e paletës së Club Hub (`--color-ink`, `--color-green`, `--color-paper`, `--color-soft` dhe ngjyrat përkatëse).
- Gradientet e buta të sfondit në `body`, Hero, seksionet dhe CTA.
- Tipografia e veçantë e titujve, përfshirë titullin serif/italic në Hero.
- Marka dhe forma e ikonës së saj.
- Ilustrimi dekorativ i Hero-s (`.hero-art`, orbitat, kartat dhe format pseudo-element).
- Pamja e CTA-së së errët dhe dekorimi i saj.
- Border radius, hijet, gradientet dhe efektet e lehta hover për butonat/kartat, nëse nuk mbulohen saktë nga Bootstrap.
- Skip-link-u, fokusi i dukshëm i tastierës dhe preferenca `prefers-reduced-motion`.
- CSS për përmasat specifike të ilustrimit dhe rreshtat e agjendës.

Pas migrimit, hiq rregullat e personalizuara për layout që tashmë i caktojnë `.container`, `.row` ose `.col`-et; mos i mbivendos pa nevojë me deklarime me specifikë më të lartë.

## CSS që mund të zëvendësohet me Bootstrap utilities

- `display: flex/grid`, drejtimi dhe mbështjellja: `.d-flex`, `.d-grid`, `.flex-column`, `.flex-lg-row`, `.flex-wrap`.
- Rreshtimi dhe shpërndarja: `.align-items-*`, `.justify-content-*`, `.text-*`.
- Hapësirat e zakonshme: `.p-*`, `.px-*`, `.py-*`, `.m-*`, `.mb-*`, `.gap-*`.
- Gjerësitë dhe pozicionimi standard: `.w-100`, `.h-100`, `.position-relative`, kur përputhen me dizajnin.
- Stilet tipografike të zakonshme, nëse përputhen: `.fw-*`, `.lh-*`, `.small`; mbaji madhësitë e veçanta të titujve në CSS.
- Stilet e listave standarde (`.list-unstyled`) aty ku lista është vetëm navigim ose përmbajtje e paraqitur vizualisht si listë.
- Hapësirat mes kolonave me `g-*` në vend të gap-eve dhe margin-eve të vendosura veçmas.

Mos zëvendëso me utilities ngjyrat, hijet, gradientet dhe format dekorative që janë pjesë e identitetit vizual.

## Checklist për responsive testing

- [ ] Kontrollo pamjen në 320–375 px, rreth 576–768 px, 992 px dhe 1200 px e sipër.
- [ ] Navbar-i është i përdorshëm në mobile dhe toggler-i, nëse përdoret, hap/mbyll navigimin me tastierë dhe klikim.
- [ ] Të gjitha lidhjet e navigimit vazhdojnë të çojnë te seksionet e sakta.
- [ ] Hero kalon në renditje vertikale në ekran të ngushtë dhe nuk pret tekstin apo ilustrimin.
- [ ] Agjenda ruan rendin, numrat dhe lexueshmërinë pa overflow.
- [ ] Kartat e përfitimeve paraqiten në një, dy ose tri kolona në mobile/tablet/desktop sipas planit.
- [ ] CTA-ja dhe footer-i nuk krijojnë rreshta të ngjeshur ose elemente të prera.
- [ ] Nuk ka scrollbar horizontal në asnjë madhësi të kontrolluar.
- [ ] Hapësirat, fontet dhe kontrasti ruajnë identitetin aktual dhe mbeten të lexueshme.
- [ ] CSS i Bootstrap-it ngarkohet para `style.css`, në mënyrë që personalizimet e projektit të kenë përparësi.
- [ ] Asnjë komponent ose dependency Bootstrap që nuk përdoret nuk është shtuar.

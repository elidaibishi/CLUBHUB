# Plani i implementimit: Club Hub

Ky plan përgatit ndërtimin e landing page-it one-page sipas analizës së projektit. Në këtë fazë përshkruhen hapat; faqja nuk implementohet.

## Fajllat që do të krijohen

```text
ClubHub/
├── PROJECT_ANALYSIS.md
├── IMPLEMENTATION_PLAN.md
├── index.html
├── css/
│   └── styles.css
└── assets/
    └── images/
```

- `index.html` — struktura dhe përmbajtja semantike e faqes.
- `css/styles.css` — stilet e faqes.
- `assets/images/` — imazhe vetëm nëse nevojiten dhe sigurohen.

## Struktura e `index.html`

Dokumenti do të ndjekë një strukturë të thjeshtë HTML5:

1. Deklarimi `<!DOCTYPE html>`, elementi `html` dhe `head` me kodimin, viewport-in dhe titullin e faqes.
2. Lidhja me `css/styles.css`.
3. Brenda `body`:
   - `header` për navbar-in.
   - `main` për përmbajtjen kryesore.
   - `footer` për fundin e faqes.

### Seksionet dhe elementet semantike

- **Navbar:** `header`, `nav`, listë `ul`/`li` dhe lidhje `a` me ankorat e seksioneve. Lidhja kryesore çon te CTA/Kontakti.
- **Hero:** `section` me `id`, një titull kryesor `h1`, tekst përshkrues `p` dhe lidhje `a` të paraqitur si buton.
- **Rreth Workshop-it:** `section`, titull `h2` dhe paragrafë `p`.
- **Agjenda:** `section` me titull `h2`; programi paraqitet me listë `ul`/`li` ose elemente `article` për blloqet e agjendës.
- **Përfitimet:** `section` me titull `h2` dhe listë `ul`/`li`; secili përfitim mund të ketë titull `h3` dhe përshkrim `p`.
- **Call to Action / Kontakti:** `section` me titull `h2`, tekst ftues dhe lidhje `a` drejt një kanali regjistrimi/kontakti të konfirmuar.
- **Footer:** `footer` me emrin Club Hub, lidhje kontakti të konfirmuara dhe shënimin e të drejtave të autorit.

Çdo seksion navigues do të ketë një `id` unik. Data, vendndodhja, oraret dhe kontaktet nuk do të paraqiten si fakte pa u konfirmuar.

## Organizimi i `css/styles.css`

Stilet do të organizohen nga rregullat e përgjithshme te ato specifike:

1. **Bazat:** reset i thjeshtë, `box-sizing`, stili i `body` dhe ngjyrat bazë.
2. **Tipografia:** madhësitë e tekstit, lartësia e rreshtit, titujt dhe paragrafët.
3. **Struktura e përbashkët:** gjerësia maksimale e përmbajtjes dhe hapësirat e seksioneve.
4. **Komponentët:** lidhjet/butonat dhe stilet e navbar-it.
5. **Seksionet:** Hero, Rreth Workshop-it, Agjenda, Përfitimet dhe CTA/Kontakti.
6. **Footer:** sfondi, ngjyrat, hapësirat dhe lidhjet.

Emrat e klasave do të jenë të qartë dhe të lidhur me rolin e elementit. Stilet do të mbeten të thjeshta për t'u mirëmbajtur dhe për t'u përshtatur më vonë me Bootstrap.

## Qasja bazë për tipografi, spacing dhe layout

- Përdor një font të lexueshëm, pak madhësi të përcaktuara teksti dhe hierarki të dallueshme për `h1`, `h2`, `h3` dhe paragrafët.
- Mbaj kontrast të mirë mes tekstit dhe sfondit.
- Përdor hapësira të qëndrueshme për padding dhe margin, me ndarje të bollshme mes seksioneve.
- Kufizo gjerësinë e blloqeve të tekstit që paragrafët të lexohen lehtë.
- Përdor layout të thjeshtë me CSS, si Flexbox ose Grid vetëm aty ku ndihmon qartësinë.
- Versioni i parë synon një paraqitje bazë; përshtatja e plotë responsive planifikohet për fazën e mëvonshme.

## Stili i elementeve kryesore

- **Navbar:** sfond i pastër, emri/logoja tipografike në njërën anë dhe lidhjet në anën tjetër; linku i CTA-së dallohet me ngjyrën kryesore. Lidhjet tregojnë qartë gjendjen `hover` dhe fokusin.
- **Hero:** titull i madh dhe i qartë, përshkrim i shkurtër dhe CTA i dukshëm. Mund të përfshijë një imazh statik nëse sigurohet material i përshtatshëm.
- **Seksionet:** sfond neutral, tituj të dallueshëm dhe paragrafë të shkurtër. Agjenda dhe përfitimet paraqiten si lista ose blloqe të thjeshta, me hapësira të njëtrajtshme.
- **CTA/Kontakti:** një ftesë e qartë për veprim dhe lidhje e dukshme drejt kanalit të konfirmuar të regjistrimit/kontaktit.
- **Footer:** pamje më e përmbledhur, me emrin e Club Hub dhe vetëm lidhje që janë konfirmuar.

## Checklist për testim

- [ ] Faqja hapet si dokument HTML5 dhe lidhet me `css/styles.css`.
- [ ] Të shtatë pjesët janë të pranishme dhe në rendin e planifikuar.
- [ ] Lidhjet e navbar-it dhe CTA-së çojnë te ankorat ose destinacionet e sakta.
- [ ] HTML përdor tituj dhe elemente semantike në rend logjik.
- [ ] Teksti është i lexueshëm dhe ka kontrast të përshtatshëm.
- [ ] Nuk paraqiten data, orare, vendndodhje apo kontakte të pakonfirmuara.
- [ ] Imazhet, nëse përdoren, ekzistojnë në vendndodhjen e duhur nën `assets/images/`.
- [ ] Projekti nuk përmban Bootstrap, JavaScript apo backend.

## Çfarë nuk duhet shtuar

- **Bootstrap** — nuk përdoret në versionin e parë.
- **JavaScript** — nuk nevojitet për këtë faqe statike.
- **Backend** — nuk krijohet logjikë serveri, ruajtje të dhënash apo dërgim formularësh.

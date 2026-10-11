# Shpjegimi i databazës

Databaza quhet `clubhub` dhe ka katër tabela.

## Çfarë bën secila tabelë?

- **`participants`** ruan secilin pjesëmarrës një herë: emrin, email-in opsional dhe datën e regjistrimit.
- **`groups`** ruan grupet: emrin, përshkrimin opsional dhe datën e krijimit.
- **`participant_groups`** ruan anëtarësimet. Çdo rresht lidh një pjesëmarrës me një grup dhe mban datën kur është bashkuar.
- **`administrators`** ruan llogaritë që mund të hyjnë në panel. Fjalëkalimi ruhet si hash, jo si tekst i lexueshëm.

## Pse ekzistojnë relacionet?

Relacionet lidhin të dhënat pa i kopjuar. Çdo `participant_id` në `participant_groups` duhet t'i përkasë një pjesëmarrësi në `participants`, ndërsa çdo `group_id` duhet t'i përkasë një grupi në `groups`. Këto kontrolle bëhen me foreign keys.

## Pse duhet tabela ndërmjetëse?

Një pjesëmarrës mund të jetë në disa grupe dhe një grup mund të ketë disa pjesëmarrës. Tabela `participant_groups` përfaqëson këtë marrëdhënie shumë-me-shumë: shtohet nga një rresht për çdo anëtarësim. Çifti `participant_id` dhe `group_id` është unik, prandaj i njëjti person nuk mund të shtohet dy herë në të njëjtin grup.

Për ta larguar dikë nga grupi, më vonë do të hiqet vetëm rreshti përkatës nga `participant_groups`; pjesëmarrësi mbetet në tabelën `participants`.

Kur një pjesëmarrës fshihet, aplikacioni fillimisht largon anëtarësimet e tij nga `participant_groups` dhe pastaj fshin regjistrimin e pjesëmarrësit. Kjo bëhet brenda një transaksioni, që të mos mbeten lidhje të paplota.

Tabela `administrators` është e ndarë nga pjesëmarrësit: pjesëmarrja në Club Hub nuk jep qasje në panelin e administrimit.

## Çfarë është primary key?

Primary key (çelës primar) është vlera që identifikon në mënyrë unike çdo rresht. `id` është primary key në `participants` dhe `groups`. Te `participant_groups`, primary key përbëhet nga dy fusha së bashku: `participant_id` dhe `group_id`.

## Çfarë është foreign key?

Foreign key (çelës i huaj) është fushë që i referohet primary key në një tabelë tjetër. Në këtë databazë, `participant_id` lidhet me `participants.id`, ndërsa `group_id` lidhet me `groups.id`. Kështu databaza nuk lejon anëtarësime që tregojnë te pjesëmarrës ose grupe që nuk ekzistojnë.

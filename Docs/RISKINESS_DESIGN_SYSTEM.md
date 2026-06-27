# Riskiness Design System

Source de reference centrale pour toute l'application Riskiness.

## Objectif

Cette charte definit une base unique pour tous les ecrans, modules et outils.
Elle doit etre lue avant toute creation de composant, de page ou de variation visuelle.

Implementation UI recommandee:
- Tailwind CSS comme couche de style principale
- CSS natif pour les besoins bas niveau
- container queries pour les composants vraiment modulaires

Principes:
- identite sombre, contrastée, premium
- accents roses et oranges, avec un degrade signature
- lisibilite prioritaire sur toutes les tailles d'ecran
- composants reutilisables et coherents
- etats UI toujours previsibles

## ADN visuel

- Fond principal: noir profond / anthracite
- Accent principal: rose vif
- Accent secondaire: orange chaud
- Signature: degrade rose vers orange
- Impression generale: elegante, vivante, audacieuse, maitrisee

Le style doit evoquer:
- le hasard
- la tension
- le rythme
- la maitrise du risque
- la vie comme sequence d'oscillations

## Tokens de design

### Couleurs

Usage:
- `brand.primary` pour les actions fortes, marque, focus actif et signaux positifs d'energie
- `brand.secondary` pour les accents alternatifs, CTA secondaires et variations chaudes
- `brand.gradient` pour les zones hero, badges premium, mises en avant et branding
- `surface` pour les fonds de cartes, menus, modals et panneaux
- `border` pour les separations discretes
- `text` pour la hiérarchie typographique

Palette:
- `brand.pink` = `#F5245E`
- `brand.orange` = `#FF7A3D`
- `brand.gradient` = `linear-gradient(135deg, #F5245E 0%, #FF7A3D 100%)`
- `ink.deep` = `#241B2E`
- `neutral.white` = `#FFFFFF`

Couleurs semantiques:
- `semantic.success` = `#19C37D`
- `semantic.warning` = `#F5A524`
- `semantic.error` = `#FF4D4F`
- `semantic.info` = `#6C8CFF`

Surfaces:
- `bg.canvas` = `#0B0910`
- `bg.page` = `#120F17`
- `surface.1` = `#17131D`
- `surface.2` = `#1D1724`
- `surface.3` = `#241B2E`
- `surface.elevated` = `#2C2237`

Bordures:
- `border.subtle` = `rgba(255,255,255,0.08)`
- `border.default` = `rgba(255,255,255,0.12)`
- `border.strong` = `rgba(255,255,255,0.18)`
- `border.accent` = `rgba(245,36,94,0.45)`

Texte:
- `text.primary` = `#FFFFFF`
- `text.secondary` = `rgba(255,255,255,0.72)`
- `text.tertiary` = `rgba(255,255,255,0.52)`
- `text.muted` = `rgba(255,255,255,0.38)`
- `text.inverse` = `#0B0910`

Etats:
- `state.hover` = leger eclaircissement de la surface ou du fond
- `state.active` = accent plus dense, ombre reduite
- `state.focus` = halo rose discret
- `state.disabled` = opacite reduite, contraste amoindri
- `state.error` = rouge franc, jamais flou
- `state.success` = vert lisible, jamais trop saturé

### Typographie

Police principale:
- `Poppins`
- fallback: system-ui, sans-serif

Regles:
- titres: `700`
- sous-titres: `600`
- corps: `400` ou `500`
- micro-labels: `500`

Usage:
- titres de marque, pages, modules: Poppins 700
- boutons et labels importants: Poppins 600
- texte courant: Poppins 400/500
- conserver une hauteur de ligne confortable et un contraste fort

Hiérarchie recommandee:
- `display` : 48-64 px, 700
- `h1` : 32-40 px, 700
- `h2` : 24-28 px, 700
- `h3` : 20-22 px, 600
- `body` : 16 px, 400/500
- `caption` : 12-13 px, 500

### Iconographie

Style:
- icones SVG
- trait net, lisible, simple
- aucune icone decorative inutile
- langage visuel homogène sur tout le produit

Regles:
- taille de base: `20px` ou `24px`
- taille minimale utile: `16px`
- icones hero ou branding: `32px` et plus
- couleur heritee du texte ou de l'etat du composant
- alignement vertical centre
- poids visuel coherent avec Poppins

Etats:
- default: couleur texte secondaire
- hover: couleur plus vive
- active: accent principal
- disabled: opacite reduite
- error: rouge semantique

### Espacement

Base:
- unit = `4px`

Scale:
- `4`
- `8`
- `12`
- `16`
- `24`
- `32`
- `40`
- `48`
- `64`

Usage:
- 8/12 pour les groupes compacts
- 16 pour les composants courants
- 24 pour les sections
- 32+ pour les respirations de page

### Rayons

- `radius.xs` = `8px`
- `radius.sm` = `12px`
- `radius.md` = `16px`
- `radius.lg` = `24px`
- `radius.xl` = `32px`
- `radius.pill` = `999px`

Usage:
- boutons et inputs: `12px` a `16px`
- cards: `16px` a `24px`
- badges et chips: `999px`
- hero blocks: `24px` a `32px`

### Ombres

Regle generale:
- ombres douces
- profondeur visible mais jamais lourde
- preferer la couleur de la surface a l'ombre noire pure

Presets:
- `shadow.sm`: `0 4px 12px rgba(0,0,0,0.18)`
- `shadow.md`: `0 10px 30px rgba(0,0,0,0.24)`
- `shadow.lg`: `0 18px 48px rgba(0,0,0,0.30)`
- `shadow.glow`: `0 0 0 1px rgba(245,36,94,0.18), 0 0 24px rgba(245,36,94,0.18)`

### Motion

Regles:
- animations courtes
- courbes fluides
- pas de mouvement superflu
- transitions lisibles et discretes

Presets:
- `duration.fast` = `120ms`
- `duration.normal` = `180ms`
- `duration.slow` = `240ms`
- `ease.standard` = `cubic-bezier(0.2, 0, 0, 1)`
- `ease.emphasis` = `cubic-bezier(0.16, 1, 0.3, 1)`

Usage:
- hover: transition douce
- ouverture modal: fade + translate leger
- chargement: skeleton ou shimmer tres discret
- validation: feedback visuel immediat mais sobre

## Composants de base

Tous les composants partagent:
- meme grille d'espacement
- meme logique de bordure
- meme famille typographique
- memes etats
- meme vocabulaire de couleurs

### Boutons

Bouton primaire:
- fond degrade rose vers orange
- texte blanc
- rayon `999px` ou `16px`
- shadow glow leger

Bouton secondaire:
- fond surface
- bordure subtile
- texte principal
- accent au hover

Bouton tertiaire:
- fond transparent
- texte texte secondaire
- underline ou highlight au hover

Etats:
- default
- hover
- active
- disabled
- loading

### Champs de formulaire

Types:
- text
- email
- password
- textarea
- select
- search
- date
- checkbox
- radio
- switch
- file upload

Regles:
- fond surface 1 ou 2
- bordure subtile au repos
- bordure accent au focus
- placeholder discret
- labels au-dessus du champ
- messages d'erreur sous le champ

Etats:
- default
- hover
- focus
- active
- disabled
- error
- success

### Cards

Role:
- conteneur de contenu
- panneau de module
- bloc de synthese
- carte d'action

Regles:
- fond surface 2 ou 3
- bordure fine
- ombre douce
- rayon `16px` a `24px`
- titre clair, contenu aere

### Badges et chips

Badges:
- pour statuts et indicateurs
- fond semi-opaque
- texte compact

Chips:
- interactions rapides
- selection, filtrage, tags
- rayon pill

### Modals

Regles:
- fond sombre
- overlay profond et lisible
- titre visible
- actions primaire et secondaire bien separees
- fermeture claire

### Tooltip

Regles:
- compact
- contraste fort
- apparition rapide
- jamais bloquant

### Alerts

Types:
- success
- warning
- error
- info

Regles:
- bordure et fond semantiques
- texte court
- action claire si necessaire

### Navigation

Regles:
- lisible sur fond sombre
- etat actif marque par accent
- espacements reguliers
- icones et textes alignes

### Tables

Regles:
- lignes respirantes
- separation subtile
- header lisible
- hover discret
- version mobile via cards ou stack

## Variantes clair / sombre

### Theme sombre par defaut

- fond principal noir profond
- surfaces en couches
- texte blanc et gris clair
- accent rose/orange tres present

### Theme clair controle

- fond blanc ou ivoire tres leger
- surface blanche
- texte ink profond
- accent toujours identique
- les contrastes doivent rester premium, jamais criards

Le theme clair ne doit jamais trahir la marque:
- conserver l'identite rose/orange
- garder les rayons et la logique d'espacement
- maintenir la densite visuelle

## Regles de cohérence

- ne jamais inventer une nouvelle couleur hors tokens
- ne jamais utiliser une nouvelle ombre ou un nouveau rayon sans validation
- ne jamais changer la police par module
- ne jamais dissocier un composant de ses etats de base
- tout nouveau module doit reutiliser cette charte
- toute implementation doit passer par Tailwind et les tokens centralises

## Sources de reference

- `logo/riskiness_brand_identity_sheet.svg`
- `logo/riskiness_logo_pulse_die_life.svg`
- `logo/riskiness_brand_identity_sheet.png`
- `logo/riskiness_logo_pulse_die_life.png`

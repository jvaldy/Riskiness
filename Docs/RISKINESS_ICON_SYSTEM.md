# Riskiness Icon System

Systeme de reference pour les icones de l'application Riskiness.

## Choix de bibliotheque

Bibliotheque recommandee:
- Lucide

Variante cible pour le frontend:
- `lucide-react` si l'interface est en React
- sinon une variante Lucide equivalente pour le runtime choisi

Pourquoi Lucide:
- style moderne et regulier
- tres bon niveau de lisibilite
- icons SVG legers
- grande coherence visuelle
- facile a normaliser dans une charte unique

## Regles d'utilisation

Principe central:
- importer uniquement les icones necessaires
- ne jamais charger la bibliotheque complete en runtime
- centraliser les aliases et les tailles
- ne jamais laisser chaque module choisir sa propre icone au hasard

Optimisation:
- utiliser des imports nommés, pas des imports globaux
- regrouper les icones dans un registre central
- standardiser `size`, `strokeWidth`, `color` et `aria-label`
- reutiliser une seule couche d'adaptation pour tous les composants

Regles de rendu:
- taille par defaut: `20px`
- taille standard interface: `24px`
- taille compacte: `16px`
- taille importante ou branding: `32px`
- `strokeWidth` standard: `1.8`
- couleur heritee du contexte
- alignement vertical centre

Etats:
- default: `text.secondary`
- hover: `text.primary` ou `brand.pink`
- active: `brand.pink`
- disabled: `text.muted`
- error: `semantic.error`
- success: `semantic.success`

## Registre d'icones autorisees

Les icones doivent etre choisies pour couvrir les besoins communs de l'app:
- navigation
- actions
- statut
- recherche
- filtres
- affichage
- securite
- profil
- temps
- risque
- parametres

Set recommande de depart:
- `Dice5`
- `Sparkles`
- `Shield`
- `ShieldAlert`
- `ShieldCheck`
- `Activity`
- `HeartPulse`
- `Heart`
- `Clock3`
- `CalendarDays`
- `CalendarRange`
- `Search`
- `Filter`
- `SlidersHorizontal`
- `Plus`
- `Edit3`
- `Trash2`
- `Save`
- `Download`
- `Upload`
- `Eye`
- `EyeOff`
- `Lock`
- `Unlock`
- `User`
- `Users`
- `Settings2`
- `Bell`
- `Info`
- `CheckCircle2`
- `AlertTriangle`
- `XCircle`
- `ChevronDown`
- `ChevronRight`
- `ArrowRight`
- `ArrowLeft`

## Aliases fonctionnels

Les modules ne doivent pas importer Lucide directement partout.
Ils doivent consommer des noms fonctionnels partages.

Exemples:
- `risk:app-home` -> `Activity`
- `risk:risk-score` -> `Dice5`
- `risk:security` -> `Shield`
- `risk:security-warning` -> `ShieldAlert`
- `risk:security-ok` -> `ShieldCheck`
- `risk:search` -> `Search`
- `risk:filter` -> `Filter`
- `risk:settings` -> `Settings2`
- `risk:profile` -> `User`
- `risk:notifications` -> `Bell`
- `risk:danger` -> `AlertTriangle`
- `risk:success` -> `CheckCircle2`
- `risk:calendar` -> `CalendarDays`
- `risk:time` -> `Clock3`

## Composant de base attendu

Dans le futur frontend, tous les ecrans doivent passer par un composant wrapper unique:
- taille parametrable
- stroke parametrable
- title/aria-label obligatoire si decoratif absent
- couleur heritee ou explicite
- support de l'etat actif / inactif

Proprietes attendues:
- `name`
- `size`
- `strokeWidth`
- `className`
- `color`
- `label`
- `decorative`

## Recommandations d'implementation

Si un frontend React est cree:
- centraliser les imports Lucide dans `src/design-system/icons/`
- exposer un registre `iconName -> component`
- utiliser un composant `AppIcon`
- interdire les imports directs dans les pages metier
- garder les variantes d'epaisseur et de taille au niveau du wrapper

Si un autre framework est utilise:
- conserver le meme contrat de nommage
- conserver le meme registre fonctionnel
- conserver les memes tailles et etats

## Integration avec Riskiness

Lucide doit suivre la direction artistique globale:
- icones nettes
- lisibles sur fond sombre
- contraste eleve
- accent rose / orange sur les etats importants
- aucune surcharge decorative

Les icones doivent rester:
- utiles
- rapidement identifiables
- secondaires par rapport au contenu
- cohérentes avec le symbole logo de Riskiness


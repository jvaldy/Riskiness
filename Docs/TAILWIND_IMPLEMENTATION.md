# Riskiness Tailwind Implementation

Tailwind CSS est la couche d'implementation UI de reference pour toute l'application Riskiness et ses outils.

## Pourquoi Tailwind

- mobile-first par construction
- breakpoints simples et previsibles
- parfait pour brancher une charte centralisee
- tres bon fit avec des composants reutilisables
- rapide a decliner sur plusieurs ecrans et modules

## Regle centrale

Tout nouvel ecran, outil ou module doit:
- consommer les tokens Riskiness
- utiliser les classes Tailwind comme base de style
- eviter les styles disperses hors charte
- reutiliser les composants communs avant de creer une variante

## Breakpoints recommandes

Base mobile-first:
- `sm` = `640px`
- `md` = `768px`
- `lg` = `1024px`
- `xl` = `1280px`
- `2xl` = `1536px`

Usage:
- mobile: contenu en une colonne, navigation simplifiee, blocs empiles
- tablette: grilles a 2 colonnes, densite intermediaire
- desktop: grilles multi-colonnes, panneaux lateraux, tables complètes
- grand ecran: davantage d'air, mais pas davantage de bruit visuel

## Patterns de layout

### Page

- fond sombre global
- contenu centre avec largeur max
- sections separees par de l'espace, pas par des traits lourds

### Dashboard

- colonnes adaptatives
- cards empilables
- widget secondaire en colonne laterale sur desktop

### Formulaire

- une colonne sur mobile
- deux colonnes seulement si la lecture reste simple
- labels toujours visibles

### Liste ou table

- table desktop
- version stackee ou cards sur mobile
- actions regroupes dans un menu compact

## Mapping avec la charte

Les classes Tailwind doivent s'appuyer sur:
- les couleurs Riskiness
- les rayons Riskiness
- la typographie Riskiness
- les ombres Riskiness
- les etats Riskiness

Les composants doivent aussi respecter:
- le contraste
- les espacements generaux
- la lisibilite des contenus
- la coherence avec Lucide

## Convention de mise en oeuvre

Recommandation:
- centraliser la configuration Tailwind dans un futur `tailwind.config`
- exposer les couleurs et espacements via les tokens Riskiness
- utiliser des composants UI de base avant les compositions metier
- garder les modules independants et reutilisables

## Raccourci de lecture

Quand un ecran est cree, il doit se poser ces questions:
- est-ce lisible sur mobile ?
- est-ce que la structure reste nette sur tablette ?
- est-ce que le desktop ajoute du confort sans casser l'identite ?
- est-ce que la couleur vient bien de la charte ?
- est-ce que les icones viennent du registre Lucide ?


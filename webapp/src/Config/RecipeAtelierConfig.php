<?php

namespace App\Config;

final class RecipeAtelierConfig
{
    public const TYPES = [
        'recipe' => 'Recette',
        'preparation' => 'Preparation',
        'routine' => 'Routine',
        'kit' => 'Kit',
        'menu' => 'Menu / Liste',
        'process' => 'Process',
        'other' => 'Autre',
    ];

    public const CATEGORIES = [
        'Cuisine',
        'Organisation',
        'Courses',
        'Maison',
        'Sante',
        'Voyage',
        'Autre',
    ];

    public const RESOURCE_TYPES = [
        'ingredient' => 'Ingredient',
        'material' => 'Materiel',
        'tool' => 'Ustensile',
        'product' => 'Produit',
        'spice' => 'Epice',
        'base' => 'Base existante',
        'link' => 'Lien',
        'note' => 'Note',
        'other' => 'Autre',
    ];

    public const UNITS = [
        'g',
        'kg',
        'ml',
        'l',
        'piece',
        'c. a cafe',
        'c. a soupe',
        'pincee',
        'boite',
        'sachet',
        'unite',
        'autre',
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'ready' => 'Pret',
        'test' => 'A tester',
        'validated' => 'Valide',
        'archived' => 'Archive',
    ];

    public const DIFFICULTIES = [
        'easy' => 'Facile',
        'medium' => 'Moyen',
        'advanced' => 'Avance',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        return [
            'types' => self::TYPES,
            'categories' => self::CATEGORIES,
            'resourceTypes' => self::RESOURCE_TYPES,
            'units' => self::UNITS,
            'statuses' => self::STATUSES,
            'difficulties' => self::DIFFICULTIES,
        ];
    }
}

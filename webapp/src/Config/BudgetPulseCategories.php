<?php

namespace App\Config;

final class BudgetPulseCategories
{
    /**
     * @return array<int, array{name: string, type: string, color: string, monthlyLimit: float|null, sortOrder: int}>
     */
    public static function all(): array
    {
        return [
            ['name' => 'Salaire', 'type' => 'income', 'color' => '#3CC477', 'monthlyLimit' => null, 'sortOrder' => 10],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#70E0C8', 'monthlyLimit' => null, 'sortOrder' => 20],
            ['name' => 'Prime', 'type' => 'income', 'color' => '#8FE36B', 'monthlyLimit' => null, 'sortOrder' => 30],
            ['name' => 'Remboursement', 'type' => 'income', 'color' => '#5EA4FF', 'monthlyLimit' => null, 'sortOrder' => 40],
            ['name' => 'Aide / allocation', 'type' => 'income', 'color' => '#B578FF', 'monthlyLimit' => null, 'sortOrder' => 50],
            ['name' => 'Investissement', 'type' => 'income', 'color' => '#FFB84D', 'monthlyLimit' => null, 'sortOrder' => 60],
            ['name' => 'Vente', 'type' => 'income', 'color' => '#FF7A3D', 'monthlyLimit' => null, 'sortOrder' => 70],
            ['name' => 'Cadeau', 'type' => 'income', 'color' => '#F5245E', 'monthlyLimit' => null, 'sortOrder' => 80],
            ['name' => 'Autre revenu', 'type' => 'income', 'color' => '#FFFFFF', 'monthlyLimit' => null, 'sortOrder' => 90],
            ['name' => 'Logement', 'type' => 'expense', 'color' => '#F5245E', 'monthlyLimit' => null, 'sortOrder' => 110],
            ['name' => 'Courses', 'type' => 'expense', 'color' => '#FF7A3D', 'monthlyLimit' => null, 'sortOrder' => 120],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#5EA4FF', 'monthlyLimit' => null, 'sortOrder' => 130],
            ['name' => 'Sante', 'type' => 'expense', 'color' => '#70E0C8', 'monthlyLimit' => null, 'sortOrder' => 140],
            ['name' => 'Loisirs', 'type' => 'expense', 'color' => '#B578FF', 'monthlyLimit' => null, 'sortOrder' => 150],
            ['name' => 'Abonnements', 'type' => 'expense', 'color' => '#FFB84D', 'monthlyLimit' => null, 'sortOrder' => 160],
            ['name' => 'Epargne', 'type' => 'expense', 'color' => '#8FE36B', 'monthlyLimit' => null, 'sortOrder' => 170],
            ['name' => 'Autre', 'type' => 'expense', 'color' => '#FFFFFF', 'monthlyLimit' => null, 'sortOrder' => 180],
        ];
    }

    /**
     * @return array<string, array{name: string, type: string, color: string, monthlyLimit: float|null, sortOrder: int}>
     */
    public static function indexed(): array
    {
        $indexed = [];
        foreach (self::all() as $category) {
            $indexed[$category['name']] = $category;
        }

        return $indexed;
    }

    public static function exists(string $name): bool
    {
        return array_key_exists($name, self::indexed());
    }

    public static function defaultForType(string $type): string
    {
        return 'income' === $type ? 'Salaire' : 'Autre';
    }
}

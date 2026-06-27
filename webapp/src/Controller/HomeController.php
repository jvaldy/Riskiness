<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): Response
    {
        $modules = [
            [
                'slug' => 'password-generator',
                'title' => 'Password Generator',
                'description' => 'Créer des mots de passe solides, rapides et lisibles.',
                'tone' => 'pink',
                'icon' => $this->iconGenerator(),
                'href' => '/password-generator',
            ],
            [
                'slug' => 'password-manager',
                'title' => 'Password Manager',
                'description' => 'Centraliser l’accès et garder le contrôle sans friction.',
                'tone' => 'orange',
                'icon' => $this->iconShield(),
                'href' => '/password-manager',
            ],
            [
                'slug' => 'movie-tracker',
                'title' => 'Movie Tracker',
                'description' => 'Suivre les films, les listes et les habitudes cinéphiles.',
                'tone' => 'ink',
                'icon' => $this->iconFilm(),
                'href' => '/movie-tracker',
            ],
        ];

        return new Response(
            $this->render($modules),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    private function render(array $modules): string
    {
        $cards = '';

        foreach ($modules as $module) {
            $cards .= sprintf(
                '<a class="module-card tone-%s" href="%s" aria-label="%s">
                    <span class="module-card__icon">%s</span>
                    <span class="module-card__content">
                        <span class="module-card__title">%s</span>
                        <span class="module-card__description">%s</span>
                    </span>
                </a>',
                htmlspecialchars($module['tone'], ENT_QUOTES),
                htmlspecialchars($module['href'], ENT_QUOTES),
                htmlspecialchars($module['title'], ENT_QUOTES),
                $module['icon'],
                htmlspecialchars($module['title'], ENT_QUOTES),
                htmlspecialchars($module['description'], ENT_QUOTES)
            );
        }

        $copyrightYear = '2026';

        return <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>RISKINESS - Accueil</title>
    <style>
        :root {
            --brand-pink: #F5245E;
            --brand-orange: #FF7A3D;
            --brand-gradient: linear-gradient(135deg, #F5245E 0%, #FF7A3D 100%);
            --bg-canvas: #0B0910;
            --bg-page: #120F17;
            --surface-2: #1D1724;
            --text-primary: #FFFFFF;
            --text-secondary: rgba(255, 255, 255, 0.72);
            --text-tertiary: rgba(255, 255, 255, 0.52);
            --border: rgba(255, 255, 255, 0.10);
            --shadow-sm: 0 8px 24px rgba(0, 0, 0, 0.18);
            --radius-xl: 32px;
            --radius-pill: 999px;
            --container: 1240px;
        }

        * { box-sizing: border-box; }
        html { color-scheme: dark; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', system-ui, sans-serif;
            color: var(--text-primary);
            background:
                radial-gradient(circle at 15% 15%, rgba(245, 36, 94, 0.14), transparent 25%),
                radial-gradient(circle at 85% 20%, rgba(255, 122, 61, 0.12), transparent 28%),
                radial-gradient(circle at 50% 100%, rgba(245, 36, 94, 0.08), transparent 32%),
                linear-gradient(180deg, var(--bg-canvas) 0%, var(--bg-page) 100%);
            padding-bottom: 66px;
        }

        a { color: inherit; text-decoration: none; }
        .page {
            position: relative;
            overflow: clip;
        }
        .page::before,
        .page::after {
            content: '';
            position: fixed;
            pointer-events: none;
            z-index: 0;
            filter: blur(18px);
            opacity: 0.45;
        }
        .page::before {
            width: 420px;
            height: 420px;
            top: -120px;
            right: -120px;
            border-radius: 999px;
            background: rgba(255, 122, 61, 0.14);
        }
        .page::after {
            width: 360px;
            height: 360px;
            left: -100px;
            top: 220px;
            border-radius: 999px;
            background: rgba(245, 36, 94, 0.13);
        }

        .shell {
            position: relative;
            z-index: 1;
            width: min(calc(100% - 32px), var(--container));
            margin: 0 auto;
            padding: 28px 0 28px;
        }

        .headerline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-pill);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .brand__logo {
            display: block;
            width: auto;
            height: 42px;
            max-width: min(340px, 44vw);
            object-fit: contain;
        }
        .brand__name {
            color: var(--text-primary);
            font-size: 1.02rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .intro {
            margin: 26px 4px 12px;
        }
        .intro h2 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1;
            letter-spacing: -0.05em;
            font-weight: 700;
        }
        .intro .accent {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .intro p {
            margin: 14px 0 0;
            max-width: 64ch;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 18px;
            margin-top: 22px;
        }
        .module-card {
            grid-column: span 4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
            min-height: 230px;
            padding: 28px 24px 26px;
            text-align: center;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02)),
                var(--surface-2);
            box-shadow: var(--shadow-sm);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease, background 180ms ease;
            position: relative;
            overflow: hidden;
        }
        .module-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top, rgba(255,255,255,0.08), transparent 45%);
            opacity: 0;
            transition: opacity 180ms ease;
        }
        .module-card:hover {
            transform: translateY(-4px);
            border-color: rgba(245, 36, 94, 0.24);
            box-shadow: 0 18px 46px rgba(0, 0, 0, 0.34);
        }
        .module-card:hover::before { opacity: 1; }
        .module-card__icon {
            width: 68px;
            height: 68px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #fff;
            flex: none;
        }
        .module-card__icon svg {
            width: 32px;
            height: 32px;
            stroke-width: 1.8;
        }
        .module-card__content {
            display: grid;
            gap: 8px;
        }
        .module-card__title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .module-card__description {
            color: var(--text-secondary);
            font-size: 0.93rem;
            line-height: 1.55;
            max-width: 28ch;
        }
        .tone-pink .module-card__icon { color: #FF5D87; }
        .tone-orange .module-card__icon { color: #FFA26B; }
        .tone-ink .module-card__icon { color: #FFF4EF; }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 7px 14px;
            background: rgba(11, 9, 16, 0.40);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-tertiary);
            font-size: 0.74rem;
            line-height: 1.2;
            text-align: center;
            letter-spacing: 0.01em;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .headerline { flex-direction: column; align-items: flex-start; }
            .module-card { grid-column: span 6; min-height: 210px; }
        }

        @media (max-width: 640px) {
            .shell { width: min(calc(100% - 20px), var(--container)); }
            .brand { width: 100%; }
            .brand__logo { max-width: 100%; height: 38px; }
            .brand__name { font-size: 0.98rem; }
            .intro h2 { font-size: clamp(1.9rem, 10vw, 2.8rem); }
            .module-card { grid-column: span 12; min-height: 188px; }
            .footer { padding: 6px 10px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="shell">
            <div class="headerline">
                <div class="brand">
                    <img class="brand__logo" src="/logo/riskiness-icon-degrade.svg" alt="Riskiness">
                    <span class="brand__name">RISKINESS</span>
                </div>
            </div>

            <section class="intro" aria-label="Introduction Riskiness">
                <h2><span class="accent">La vie est un coup de dé.</span></h2>
            </section>

            <section aria-label="Modules Riskiness">
                <div class="grid">
                    {$cards}
                </div>
            </section>

            <div class="footer">
                <span>© {$copyrightYear} Riskiness. Tous droits réservés.</span>
            </div>
        </div>
    </main>
</body>
</html>
HTML;
    }

    private function iconGenerator(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V8a4 4 0 0 1 8 0v2"/><path d="M12 14v2"/><path d="M17 5l1 1 1 1-1 1-1-1-1-1 1-1z"/></svg>';
    }

    private function iconShield(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M12 3 4 6v5c0 5 3.2 8.7 8 10 4.8-1.3 8-5 8-10V6l-8-3z"/><path d="M9 12l2 2 4-4"/></svg>';
    }

    private function iconFilm(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M4.5 8.5h15a1 1 0 0 1 1 1V17a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2V9.5a1 1 0 0 1 1-1z"/><path d="M4.5 8.5 7 5h3l-2.5 3.5M10 8.5 12.5 5h3L13 8.5M15.5 8.5 18 5h1.5"/><path d="M11 12.2 14.8 14.5 11 16.8z"/></svg>';
    }
}

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
            [
                'slug' => 'budget-pulse',
                'title' => 'Budget Pulse',
                'description' => 'Piloter revenus, dépenses et équilibre financier en un seul regard.',
                'tone' => 'pink',
                'icon' => $this->iconWallet(),
                'href' => '/budget-pulse',
            ],
            [
                'slug' => 'recipe-atelier',
                'title' => 'Recipe Atelier',
                'description' => 'Composer, retrouver et partager des recettes avec une interface fluide.',
                'tone' => 'orange',
                'icon' => $this->iconChefHat(),
                'href' => '/recipe-atelier',
            ],
            [
                'slug' => 'doc-sentinel',
                'title' => 'Doc Sentinel',
                'description' => 'Surveiller les dates d’expiration et relancer les documents avant l’échéance.',
                'tone' => 'ink',
                'icon' => $this->iconFileClock(),
                'href' => '/doc-sentinel',
            ],
            [
                'slug' => 'cycle-care',
                'title' => 'Cycle Care',
                'description' => 'Suivre les cycles, noter les signaux et gérer le confort avec tact.',
                'tone' => 'pink',
                'icon' => $this->iconHeartPulse(),
                'href' => '/cycle-care',
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
            flex-wrap: nowrap;
            gap: 16px;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-pill);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
        }
        .headerline__actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex: none;
            white-space: nowrap;
        }
        .headerline__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-primary);
            flex: none;
        }
        .headerline__button svg {
            width: 22px;
            height: 22px;
            stroke-width: 1.9;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            flex: 1 1 auto;
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
            overflow: hidden;
            text-overflow: ellipsis;
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
            grid-column: span 3;
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
            .module-card { grid-column: span 6; min-height: 210px; }
        }

        @media (max-width: 640px) {
            .shell { width: min(calc(100% - 20px), var(--container)); }
            .brand__logo { max-width: 100%; height: 34px; }
            .brand__name { font-size: 0.98rem; }
            .headerline__button { width: 44px; height: 44px; border-radius: 16px; }
            .headerline__button svg { width: 20px; height: 20px; }
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
                <div class="headerline__actions">
                    <a class="headerline__button" href="/" aria-label="Accueil" title="Accueil">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img">
                            <path d="M3 11.5 12 4l9 7.5"/>
                            <path d="M6 10.5V20h12v-9.5"/>
                            <path d="M10 20v-5h4v5"/>
                        </svg>
                    </a>
                    <button class="headerline__button" type="button" aria-label="Mon compte" title="Mon compte">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img">
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M5 20a7 7 0 0 1 14 0"/>
                        </svg>
                    </button>
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

    private function iconWallet(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M5 7.5A2.5 2.5 0 0 1 7.5 5h10A1.5 1.5 0 0 1 19 6.5V8H7.5A2.5 2.5 0 0 0 5 10.5v8A2.5 2.5 0 0 0 7.5 21h10A1.5 1.5 0 0 0 19 19.5V18h-9.5A2.5 2.5 0 0 1 7 15.5v-3A2.5 2.5 0 0 1 9.5 10H19"/><circle cx="16.5" cy="14.5" r="1"/></svg>';
    }

    private function iconChefHat(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M7 20h10"/><path d="M8 20v-6.5A3.5 3.5 0 0 1 5 10a3.5 3.5 0 0 1 3.2-3.5A4.5 4.5 0 0 1 16 5.2a4 4 0 0 1 5 3.8A3.5 3.5 0 0 1 18.5 12H18v8H8z"/><path d="M10 11.5h4"/></svg>';
    }

    private function iconCalendarClock(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/><circle cx="15" cy="15" r="2.5"/><path d="M15 13.8v1.4l1 0.7"/></svg>';
    }

    private function iconShieldCheck(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M12 3 4 6v5c0 5 3.2 8.7 8 10 4.8-1.3 8-5 8-10V6l-8-3z"/><path d="M9.2 12.3 11 14l4-4"/></svg>';
    }

    private function iconFileClock(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><circle cx="15.5" cy="16.5" r="2.5"/><path d="M15.5 15.3v1.2l.8.5"/></svg>';
    }

    private function iconHeartPulse(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img"><path d="M12 21s-7-4.5-8.5-9.2C2.3 8.4 4.2 5.5 7 5.5c1.7 0 2.9.8 3.8 2 1-1.2 2.2-2 3.8-2 2.8 0 4.7 2.9 3.5 6.3C19 16.5 12 21 12 21z"/><path d="M6.5 12h2l1.2-2.2 1.7 4 1.3-2H17"/></svg>';
    }
}

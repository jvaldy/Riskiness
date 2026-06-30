<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ModulePageController extends AbstractController
{
    #[Route('/password-manager-legacy', name: 'app_password_manager_legacy', methods: ['GET'])]
    public function passwordManager(Security $security): Response
    {
        return new Response($this->renderPlaceholder(
            'Password Manager',
            'Centraliser les acces, structurer les secrets et garder le controle dans une interface Riskiness coherente.',
            $this->accountHref($security),
            $this->accountButtonClass($security)
        ), Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/budget-pulse', name: 'app_budget_pulse', methods: ['GET'])]
    public function budgetPulse(Security $security): Response
    {
        return new Response($this->renderPlaceholder(
            'Budget Pulse',
            'Piloter les revenus, les depenses et les arbitrages financiers dans une vue claire et premium.',
            $this->accountHref($security),
            $this->accountButtonClass($security)
        ), Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/recipe-atelier', name: 'app_recipe_atelier', methods: ['GET'])]
    public function recipeAtelier(Security $security): Response
    {
        return new Response($this->renderPlaceholder(
            'Recipe Atelier',
            'Organiser des recettes, structurer les ingredients et retrouver rapidement chaque preparation.',
            $this->accountHref($security),
            $this->accountButtonClass($security)
        ), Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/doc-sentinel', name: 'app_doc_sentinel', methods: ['GET'])]
    public function docSentinel(Security $security): Response
    {
        return new Response($this->renderPlaceholder(
            'Doc Sentinel',
            'Surveiller les dates d expiration, les relances et les documents sensibles avec precision.',
            $this->accountHref($security),
            $this->accountButtonClass($security)
        ), Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/cycle-care', name: 'app_cycle_care', methods: ['GET'])]
    public function cycleCare(Security $security): Response
    {
        return new Response($this->renderPlaceholder(
            'Cycle Care',
            'Suivre les cycles, les symptomes et le confort avec une experience douce et discrete.',
            $this->accountHref($security),
            $this->accountButtonClass($security)
        ), Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function renderPlaceholder(string $title, string $description, string $accountHref, string $accountButtonClass): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES);
        $safeDescription = htmlspecialchars($description, ENT_QUOTES);

        return <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>RISKINESS - {$safeTitle}</title>
    <style>
        :root {
            --brand-pink: #F5245E;
            --brand-orange: #FF7A3D;
            --brand-gradient: linear-gradient(135deg, #F5245E 0%, #FF7A3D 100%);
            --bg-canvas: #0B0910;
            --bg-page: #120F17;
            --surface-1: #17131D;
            --text-primary: #FFFFFF;
            --text-secondary: rgba(255, 255, 255, 0.72);
            --text-tertiary: rgba(255, 255, 255, 0.52);
            --border: rgba(255, 255, 255, 0.10);
            --shadow-md: 0 18px 48px rgba(0, 0, 0, 0.30);
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
                linear-gradient(180deg, var(--bg-canvas) 0%, var(--bg-page) 100%);
            padding-bottom: 52px;
        }

        a { color: inherit; text-decoration: none; }
        .page { position: relative; overflow: clip; }
        .shell {
            position: relative;
            z-index: 1;
            width: min(calc(100% - 32px), var(--container));
            margin: 0 auto;
            padding: 28px 0 24px;
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
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease;
        }
        .headerline__button:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.06);
            border-color: currentColor;
        }
        .headerline__button svg { width: 22px; height: 22px; stroke-width: 1.9; }
        .headerline__button--account-logged-in {
            color: rgba(60, 196, 119, 0.92);
            border-color: rgba(60, 196, 119, 0.22);
            background: rgba(60, 196, 119, 0.08);
        }
        .headerline__button--account-guest {
            color: rgba(255, 92, 107, 0.92);
            border-color: rgba(255, 92, 107, 0.22);
            background: rgba(255, 92, 107, 0.08);
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

        .hero {
            margin: 32px 4px 22px;
            padding: 26px;
            border-radius: 32px;
            border: 1px solid var(--border);
            background:
                radial-gradient(circle at top right, rgba(255, 122, 61, 0.12), transparent 32%),
                radial-gradient(circle at left, rgba(245, 36, 94, 0.12), transparent 28%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.03)),
                var(--surface-1);
            box-shadow: var(--shadow-md);
        }
        .hero h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1;
            letter-spacing: -0.05em;
        }
        .hero .accent {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .hero p {
            margin: 14px 0 0;
            color: var(--text-secondary);
            line-height: 1.65;
            max-width: 64ch;
        }
        .notice {
            margin-top: 20px;
            display: inline-flex;
            padding: 8px 12px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-tertiary);
            font-size: 0.84rem;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 5px 12px;
            background: rgba(11, 9, 16, 0.24);
            backdrop-filter: blur(8px);
            border-top: 1px solid rgba(255, 255, 255, 0.03);
            color: rgba(255, 255, 255, 0.34);
            font-size: 0.70rem;
            line-height: 1.15;
            text-align: center;
            letter-spacing: 0.01em;
            pointer-events: none;
        }

        @media (max-width: 640px) {
            .shell { width: min(calc(100% - 20px), var(--container)); }
            .brand__logo { max-width: 100%; height: 34px; }
            .brand__name { font-size: 0.98rem; }
            .headerline__button { width: 44px; height: 44px; border-radius: 16px; }
            .headerline__button svg { width: 20px; height: 20px; }
            .hero { padding: 20px; }
            .footer { padding: 4px 10px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="shell">
            <div class="headerline">
                <a class="brand" href="/" aria-label="Retour a l'accueil" title="Retour a l'accueil">
                    <img class="brand__logo" src="/logo/riskiness-icon-degrade.svg" alt="Riskiness">
                    <span class="brand__name">RISKINESS</span>
                </a>
                <div class="headerline__actions">
                    <a class="headerline__button" href="/" aria-label="Retour a l'accueil" title="Retour a l'accueil">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img">
                            <path d="M3 11.5 12 4l9 7.5"/>
                            <path d="M6 10.5V20h12v-9.5"/>
                            <path d="M10 20v-5h4v5"/>
                        </svg>
                    </a>
                    <a class="headerline__button {$accountButtonClass}" href="{$accountHref}" aria-label="Mon compte" title="Mon compte">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" role="img">
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M5 20a7 7 0 0 1 14 0"/>
                        </svg>
                    </a>
                </div>
            </div>

            <section class="hero" aria-label="Module {$safeTitle}">
                <h1><span class="accent">{$safeTitle}</span></h1>
                <p>{$safeDescription}</p>
                <div class="notice">Module en cours de structuration dans la charte centrale Riskiness.</div>
            </section>

            <div class="footer">
                <span>&copy; 2026 Riskiness. Tous droits reserves.</span>
            </div>
        </div>
    </main>
</body>
</html>
HTML;
    }

    private function accountHref(Security $security): string
    {
        return $security->getUser() ? '/profile' : '/login';
    }

    private function accountButtonClass(Security $security): string
    {
        return $security->getUser() ? 'headerline__button--account-logged-in' : 'headerline__button--account-guest';
    }
}

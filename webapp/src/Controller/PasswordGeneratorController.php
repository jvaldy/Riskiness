<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordGeneratorController
{
    #[Route('/password-generator', name: 'app_password_generator', methods: ['GET'])]
    public function __invoke(Security $security): Response
    {
        return new Response($this->render($this->accountHref($security), $this->accountButtonClass($security)), Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function render(string $accountHref, string $accountButtonClass): string
    {
        return <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>RISKINESS - Password Generator</title>
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
            --text-muted: rgba(255, 255, 255, 0.40);
            --border: rgba(255, 255, 255, 0.10);
            --shadow-sm: 0 8px 24px rgba(0, 0, 0, 0.18);
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
                radial-gradient(circle at 50% 100%, rgba(245, 36, 94, 0.08), transparent 32%),
                linear-gradient(180deg, var(--bg-canvas) 0%, var(--bg-page) 100%);
            padding-bottom: 52px;
        }

        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .page { position: relative; overflow: clip; }
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
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease;
        }
        .headerline__button:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.06);
            border-color: currentColor;
        }
        .headerline__button svg {
            width: 22px;
            height: 22px;
            stroke-width: 1.9;
        }
        .headerline__button--home {
            color: var(--text-primary);
        }
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

        .intro {
            margin: 26px 4px 18px;
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

        .generator {
            margin-top: 24px;
            padding: 0 4px 4px;
        }
        .generator__panel {
            border-radius: 36px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background:
                radial-gradient(circle at top right, rgba(255, 122, 61, 0.14), transparent 34%),
                radial-gradient(circle at left, rgba(245, 36, 94, 0.12), transparent 28%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.03)),
                var(--surface-1);
            box-shadow: var(--shadow-md);
            padding: 24px;
        }
        .generator__header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .generator__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
            font-size: 0.82rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .generator__eyebrow::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--brand-gradient);
            box-shadow: 0 0 18px rgba(245, 36, 94, 0.45);
        }
        .generator__title {
            margin: 12px 0 6px;
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
        }
        .generator__lead {
            margin: 0;
            max-width: 62ch;
            color: var(--text-secondary);
            line-height: 1.65;
        }
        .generator__actions-top {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .generator__button {
            appearance: none;
            border: 1px solid transparent;
            border-radius: 18px;
            padding: 12px 16px;
            cursor: pointer;
            color: var(--text-primary);
            background: var(--brand-gradient);
            font-weight: 700;
            letter-spacing: -0.01em;
            box-shadow: 0 12px 28px rgba(245, 36, 94, 0.20);
            transition: transform 180ms ease, box-shadow 180ms ease, opacity 180ms ease;
        }
        .generator__button:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 34px rgba(245, 36, 94, 0.28);
        }
        .generator__button--secondary {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: none;
            color: var(--text-secondary);
        }
        .generator__button--secondary:hover {
            box-shadow: none;
            background: rgba(255, 255, 255, 0.07);
        }
        .generator__display {
            margin-top: 18px;
            display: grid;
            gap: 12px;
        }
        .generator__password {
            width: 100%;
            min-height: 78px;
            padding: 18px 18px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(10, 8, 15, 0.68);
            color: var(--text-primary);
            font-size: clamp(1.05rem, 2vw, 1.35rem);
            line-height: 1.3;
            letter-spacing: 0.04em;
            font-family: 'SFMono-Regular', ui-monospace, Consolas, monospace;
            outline: none;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
        }
        .generator__password::selection {
            background: rgba(245, 36, 94, 0.35);
        }
        .generator__status-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            color: var(--text-tertiary);
            font-size: 0.85rem;
        }
        .generator__status {
            min-height: 1em;
        }
        .generator__controls {
            margin-top: 18px;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 16px;
        }
        .control-card {
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            padding: 16px;
        }
        .control-card__title {
            margin: 0 0 12px;
            font-size: 0.92rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--text-secondary);
        }
        .range-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .range-row input[type="range"] {
            width: 100%;
            accent-color: var(--brand-pink);
        }
        .range-value {
            flex: none;
            min-width: 56px;
            padding: 8px 10px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.07);
            text-align: center;
            font-weight: 700;
        }
        .toggle-grid {
            display: grid;
            gap: 10px;
        }
        .toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .toggle__label {
            display: grid;
            gap: 3px;
        }
        .toggle__title {
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .toggle__text {
            color: var(--text-muted);
            font-size: 0.84rem;
            line-height: 1.35;
        }
        .toggle input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--brand-pink);
            flex: none;
        }
        .support-note {
            margin-top: 12px;
            color: var(--text-muted);
            font-size: 0.84rem;
            line-height: 1.5;
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

        @media (max-width: 900px) {
            .headerline { gap: 12px; }
            .generator__controls { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .shell { width: min(calc(100% - 20px), var(--container)); }
            .brand__logo { max-width: 100%; height: 34px; }
            .brand__name { font-size: 0.98rem; }
            .headerline__button { width: 44px; height: 44px; border-radius: 16px; }
            .headerline__button svg { width: 20px; height: 20px; }
            .intro h2 { font-size: clamp(1.9rem, 10vw, 2.8rem); }
            .generator__panel { padding: 18px; border-radius: 28px; }
            .generator__actions-top { width: 100%; justify-content: stretch; }
            .generator__button { width: 100%; }
            .generator__status-row { align-items: flex-start; }
            .footer { padding: 4px 10px; }
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
                    <a class="headerline__button headerline__button--home" href="/" aria-label="Retour à l'accueil" title="Retour à l'accueil">
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

            <section class="intro" aria-label="Introduction Riskiness">
                <h2><span class="accent">Générateur de mot de passe.</span></h2>
            </section>

            <section id="password-generator" class="generator" aria-label="Générateur de mot de passe">
                <div class="generator__panel" data-password-generator>
                    <div class="generator__header">
                        <div>
                            <span class="generator__eyebrow">Outil actif</span>
                        </div>
                        <div class="generator__actions-top">
                            <button class="generator__button generator__button--secondary" type="button" data-regenerate-button>Actualiser</button>
                            <button class="generator__button" type="button" data-copy-button>Copier le mot de passe</button>
                        </div>
                    </div>

                    <div class="generator__display">
                        <input class="generator__password" type="text" value="" readonly spellcheck="false" autocapitalize="off" autocomplete="off" aria-label="Mot de passe généré" data-password-output>
                        <div class="generator__status-row">
                            <span class="generator__status" data-copy-status>Le générateur produit automatiquement un mot de passe au chargement, puis à chaque changement de réglage.</span>
                        </div>
                    </div>

                    <div class="generator__controls">
                        <div class="control-card">
                            <p class="control-card__title">Longueur</p>
                            <div class="range-row">
                                <input type="range" min="8" max="32" step="1" value="16" aria-label="Longueur du mot de passe" data-length-slider>
                                <span class="range-value" data-length-value>16</span>
                            </div>
                            <p class="support-note">Plus la longueur monte, plus la résistance brute-force augmente.</p>
                        </div>

                        <div class="control-card">
                            <p class="control-card__title">Composition</p>
                            <div class="toggle-grid">
                                <label class="toggle">
                                    <span class="toggle__label">
                                        <span class="toggle__title">Lettres</span>
                                        <span class="toggle__text">Inclure les majuscules et minuscules.</span>
                                    </span>
                                    <input type="checkbox" checked data-option="letters">
                                </label>
                                <label class="toggle">
                                    <span class="toggle__label">
                                        <span class="toggle__title">Chiffres</span>
                                        <span class="toggle__text">Ajouter les nombres pour élargir l’espace aléatoire.</span>
                                    </span>
                                    <input type="checkbox" checked data-option="numbers">
                                </label>
                                <label class="toggle">
                                    <span class="toggle__label">
                                        <span class="toggle__title">Symboles</span>
                                        <span class="toggle__text">Activer les caractères spéciaux.</span>
                                    </span>
                                    <input type="checkbox" checked data-option="symbols">
                                </label>
                                <label class="toggle">
                                    <span class="toggle__label">
                                        <span class="toggle__title">Caractères semblables</span>
                                        <span class="toggle__text">Décoche pour exclure I, l, 1, O, 0 et faciliter la lecture.</span>
                                    </span>
                                    <input type="checkbox" checked data-option="similar">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="footer">
                <span>© 2026 Riskiness. Tous droits réservés.</span>
            </div>
        </div>
    </main>

    <script>
    (function () {
        const root = document.querySelector('[data-password-generator]');

        if (!root) {
            return;
        }

        const output = root.querySelector('[data-password-output]');
        const slider = root.querySelector('[data-length-slider]');
        const lengthValue = root.querySelector('[data-length-value]');
        const copyButton = root.querySelector('[data-copy-button]');
        const regenerateButtons = root.querySelectorAll('[data-regenerate-button]');
        const status = root.querySelector('[data-copy-status]');
        const optionInputs = Array.from(root.querySelectorAll('[data-option]'));

        const charSets = {
            letters: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            numbers: '0123456789',
            symbols: '!@#$%^&*()-_=+[]{};:,.?/<>~'
        };
        const similarCharacters = new Set(['I', 'l', '1', 'O', '0', 'o']);

        function randomIndex(max) {
            const array = new Uint32Array(1);
            window.crypto.getRandomValues(array);
            return array[0] % max;
        }

        function pickCharacter(pool) {
            return pool[randomIndex(pool.length)];
        }

        function shuffle(items) {
            for (let index = items.length - 1; index > 0; index -= 1) {
                const swapIndex = randomIndex(index + 1);
                const temp = items[index];
                items[index] = items[swapIndex];
                items[swapIndex] = temp;
            }
        }

        function stripSimilarCharacters(value) {
            return Array.from(value).filter((character) => !similarCharacters.has(character)).join('');
        }

        function getPoolState() {
            const state = {
                letters: root.querySelector('[data-option="letters"]').checked,
                numbers: root.querySelector('[data-option="numbers"]').checked,
                symbols: root.querySelector('[data-option="symbols"]').checked,
                similar: root.querySelector('[data-option="similar"]').checked
            };

            if (!state.letters && !state.numbers && !state.symbols) {
                state.letters = true;
            }

            return state;
        }

        function buildPools(state) {
            const pools = [];

            if (state.letters) {
                pools.push(state.similar ? charSets.letters : stripSimilarCharacters(charSets.letters));
            }

            if (state.numbers) {
                pools.push(state.similar ? charSets.numbers : stripSimilarCharacters(charSets.numbers));
            }

            if (state.symbols) {
                pools.push(charSets.symbols);
            }

            return pools.filter((pool) => pool.length > 0);
        }

        function generatePassword() {
            const length = Number(slider.value);
            const state = getPoolState();
            const pools = buildPools(state);
            const combinedPool = pools.join('');
            const minimumLength = Math.max(length, pools.length);
            const characters = [];

            pools.forEach((pool) => {
                characters.push(pickCharacter(pool));
            });

            while (characters.length < minimumLength) {
                characters.push(pickCharacter(combinedPool));
            }

            shuffle(characters);

            const password = characters.slice(0, length).join('');
            output.value = password;
            return password;
        }

        function syncLength() {
            lengthValue.textContent = slider.value;
        }

        async function copyPassword() {
            const password = output.value;

            try {
                await navigator.clipboard.writeText(password);
                status.textContent = 'Mot de passe copié dans le presse-papiers.';
            } catch (error) {
                output.focus();
                output.select();
                const copied = document.execCommand('copy');
                status.textContent = copied
                    ? 'Mot de passe copié dans le presse-papiers.'
                    : 'Copie impossible, sélection manuelle requise.';
            }
        }

        function regenerate() {
            generatePassword();
            status.textContent = 'Le générateur produit automatiquement un mot de passe au chargement, puis à chaque changement de réglage.';
        }

        slider.addEventListener('input', () => {
            syncLength();
            regenerate();
        });

        optionInputs.forEach((input) => {
            input.addEventListener('change', regenerate);
        });

        regenerateButtons.forEach((button) => {
            button.addEventListener('click', regenerate);
        });

        copyButton.addEventListener('click', copyPassword);

        output.addEventListener('focus', () => {
            output.select();
        });

        syncLength();
        regenerate();
    })();
    </script>
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

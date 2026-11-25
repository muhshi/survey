@extends('layouts.survey')

@section('title', $survey->title)
@section('body_class', 'survey-page')
@section('hide_footer', true)

@php
    $schemaPages = $survey->schema['pages'] ?? [];
    $questionCount = collect($schemaPages)->sum(function ($page) {
        return collect($page['elements'] ?? [])->sum(function ($element) {
            return isset($element['elements']) ? count($element['elements']) : 1;
        });
    });
    $modeLabel = match ($survey->mode?->value) {
        'single' => 'Sekali isi',
        'editable' => 'Bisa diedit',
        'multi' => 'Bisa diisi berulang',
        default => 'Form',
    };
@endphp

@section('head')
<style>
    /* ================= SURVEY RUNNER ================= */
    .survey-page .main-content {
        max-width: none;
        padding: 0;
    }

    .runner {
        max-width: 880px;
        margin: 0 auto;
        padding: 26px 24px 72px;
    }

    .runner-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .runner-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--text-2);
        transition: color 0.18s ease;
    }
    .runner-back:hover { color: var(--primary); }
    .runner-back svg { width: 16px; height: 16px; }
    .runner-secure {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-3);
    }
    .runner-secure svg { width: 14px; height: 14px; color: var(--success); }

    /* --- Preview notice --- */
    .preview-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 13px 16px;
        margin-bottom: 18px;
        border: 1px solid var(--primary-100);
        background: var(--primary-50);
        border-radius: var(--radius-md);
    }
    .preview-banner__main {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.82rem;
        line-height: 1.5;
        color: var(--text-2);
    }
    .preview-banner__main strong { color: var(--text); }
    .pb-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        margin-top: 5px;
        flex-shrink: 0;
        box-shadow: 0 0 0 3px var(--primary-100);
    }
    .preview-banner__link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }
    .preview-banner__link:hover { text-decoration: underline; }

    /* --- Survey header --- */
    .head-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 30px 32px;
        box-shadow: var(--shadow-md);
    }
    .head-chips {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        background: var(--surface-3);
        color: var(--text-2);
    }
    .chip svg { width: 12px; height: 12px; }
    .chip-primary { background: var(--primary-50); color: var(--primary); }
    .chip-accent { background: var(--accent-50); color: var(--accent-600); }
    .chip-success { background: var(--success-50); color: var(--success); }

    .head-card h1 {
        font-size: clamp(1.55rem, 3.2vw, 2.05rem);
        font-weight: 800;
        line-height: 1.18;
        letter-spacing: -0.025em;
        text-wrap: balance;
    }
    .head-desc {
        margin-top: 12px;
        font-size: 0.95rem;
        line-height: 1.7;
        color: var(--text-2);
        max-width: 68ch;
        white-space: pre-line;
    }
    .head-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px 20px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
    }
    .head-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--text-3);
    }
    .head-meta svg { width: 14px; height: 14px; }

    /* --- Draft alert --- */
    .draft-alert {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 16px;
        padding: 14px 16px;
        background: var(--accent-50);
        border: 1px solid var(--accent-50);
        border-left: 3px solid var(--accent);
        border-radius: var(--radius-md);
    }
    .draft-icon { font-size: 1.2rem; line-height: 1; }
    .draft-text { flex: 1; min-width: 200px; }
    .draft-text strong {
        display: block;
        font-size: 0.845rem;
        font-weight: 700;
        color: var(--text);
    }
    .draft-text span {
        display: block;
        margin-top: 2px;
        font-size: 0.78rem;
        line-height: 1.5;
        color: var(--text-2);
    }
    .draft-actions { display: flex; gap: 8px; }
    .btn-mini {
        padding: 7px 14px;
        border-radius: 9px;
        border: 1px solid transparent;
        font-size: 0.76rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.18s ease, color 0.18s ease;
    }
    .btn-mini-primary { background: var(--accent); color: #422006; }
    .btn-mini-primary:hover { background: var(--accent-600); }
    .btn-mini-ghost {
        background: var(--surface);
        border-color: var(--border-strong);
        color: var(--text-2);
    }
    .btn-mini-ghost:hover { color: var(--text); }

    /* --- Loading & error --- */
    .state-panel {
        margin-top: 18px;
        padding: 44px 24px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        text-align: center;
    }
    .spinner {
        width: 34px;
        height: 34px;
        margin: 0 auto 14px;
        border-radius: 50%;
        border: 3px solid var(--primary-100);
        border-top-color: var(--primary);
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .state-panel p {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-2);
    }
    .state-panel.error h2 {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--danger);
        margin-bottom: 10px;
    }
    .state-panel.error #error-msg {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.78rem;
        font-weight: 400;
        color: var(--text-2);
        line-height: 1.55;
        margin-bottom: 18px;
        word-break: break-word;
    }

    .hidden { display: none !important; }

    /* --- SurveyJS surface --- */
    #surveyElement {
        margin-top: 18px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 30px 32px 18px;
        box-shadow: var(--shadow-md);
        color: var(--text);
    }
    #surveyElement:empty { display: none; }

    .sd-root-modern, .sd-root-modern--full-container, .sd-container-modern {
        background: transparent !important;
        font-family: var(--font-sans) !important;
    }
    .sd-root-modern { --sd-base-padding: 0; }

    .sd-page { padding: 0 !important; }
    .sd-page__title {
        font-family: var(--font-display) !important;
        font-size: 1.45rem !important;
        font-weight: 800 !important;
        color: var(--text) !important;
        letter-spacing: -0.02em;
        padding: 0 !important;
        margin-bottom: 8px !important;
    }
    .sd-page__description {
        font-size: 0.9rem !important;
        line-height: 1.65 !important;
        color: var(--text-2) !important;
        padding: 0 !important;
        margin-bottom: 18px !important;
    }

    .sd-element, .sd-question, .sd-panel {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 0 4px !important;
        color: var(--text) !important;
    }
    .sd-question { padding: 16px 0 !important; }
    #surveyElement .sd-question:not(:last-child) {
        border-bottom: 1px solid var(--border) !important;
        padding-bottom: 24px !important;
        margin-bottom: 6px !important;
    }

    .sd-question__title, .sd-title, .sd-question__title .sd-title {
        font-size: 1rem !important;
        font-weight: 600 !important;
        line-height: 1.55 !important;
        color: var(--text) !important;
    }
    .sd-question__title--answer { color: var(--text) !important; }
    .sd-question__required-text { color: var(--danger) !important; }
    .sd-description, .sd-question__description {
        color: var(--text-2) !important;
        font-size: 0.85rem !important;
        line-height: 1.6 !important;
    }

    /* Small, consistent question numbers */
    .sd-question__number,
    .sd-question__num,
    .sd-element__num {
        background: transparent !important;
        color: var(--text-2) !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-right: 8px !important;
    }

    /* Survey header (title + description block) must follow the page surface */
    .sd-container-modern__title,
    .sd-header,
    .sd-header__text {
        background: transparent !important;
        color: var(--text) !important;
    }
    .sd-container-modern__title { padding-left: 0 !important; }
    .sd-title { color: var(--text) !important; }


    /* Inputs */
    .sd-input, .sd-dropdown, .sd-comment, .sd-textarea {
        background: var(--surface-2) !important;
        border: 1px solid var(--border-strong) !important;
        border-radius: 10px !important;
        padding: 12px 14px !important;
        font-size: 0.925rem !important;
        color: var(--text) !important;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }
    .sd-input:focus, .sd-comment:focus, .sd-textarea:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px var(--primary-50) !important;
        outline: none !important;
    }
    .sd-input::placeholder, .sd-comment::placeholder { color: var(--text-3) !important; }
    .sd-dropdown { cursor: pointer; }
    .sd-dropdown__value { color: var(--text) !important; }
    .sd-dropdown__placeholder { color: var(--text-3) !important; }

    /* Choice items (Radio & Checkbox) */
    .sd-item {
        border-radius: 10px !important;
        padding: 8px 12px !important;
        margin-bottom: 4px !important;
        border: 1.5px solid transparent !important;
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease !important;
        cursor: pointer !important;
    }
    .sd-item:hover {
        background: var(--surface-2) !important;
    }
    
    /* Highlight the chosen answer row clearly */
    .sd-item.sd-item--checked,
    .sd-item.sd-radio--checked,
    .sd-item.sd-checkbox--checked {
        background: var(--primary-50) !important;
        border-color: var(--primary-100) !important;
    }

    /* Option Label text */
    .sd-item__control-label,
    .sd-checkbox__control-label,
    .sd-radio__control-label,
    .sd-item__control-label span {
        color: var(--text) !important;
        font-size: 0.925rem !important;
        line-height: 1.5 !important;
    }
    .sd-item--checked .sd-item__control-label,
    .sd-item--checked .sd-radio__control-label,
    .sd-item--checked .sd-checkbox__control-label {
        color: var(--text) !important;
        font-weight: 600 !important;
    }

    /* Base Decorator (Outer circle for radio, square for checkbox) */
    .sd-item__decorator,
    .sd-radio__decorator,
    .sd-checkbox__decorator {
        width: 22px !important;
        height: 22px !important;
        min-width: 22px !important;
        min-height: 22px !important;
        border: 2px solid var(--border-strong) !important;
        background-color: var(--surface) !important;
        box-shadow: var(--shadow-sm) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
        transition: all 0.15s ease !important;
        flex-shrink: 0 !important;
    }
    .sd-radio__decorator {
        border-radius: 50% !important;
    }
    .sd-checkbox__decorator {
        border-radius: 6px !important;
    }

    .sd-item:hover .sd-item__decorator,
    .sd-item:hover .sd-radio__decorator,
    .sd-item:hover .sd-checkbox__decorator {
        border-color: var(--primary) !important;
    }

    /* Checked State: Outer Decorator filled with Primary */
    .sd-item--checked .sd-item__decorator,
    .sd-item--checked .sd-radio__decorator,
    .sd-item--checked .sd-checkbox__decorator,
    .sd-radio--checked .sd-radio__decorator,
    .sd-checkbox--checked .sd-checkbox__decorator {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px var(--primary-50) !important;
    }

    /* Checked Radio: Inner Dot (:after) - ALWAYS visible (focused AND blur/unfocused) */
    .sd-radio--checked .sd-radio__decorator:after,
    .sd-radio--checked .sd-item__decorator:after,
    .sd-item--checked .sd-radio__decorator:after,
    .sd-item--checked .sd-item__decorator:after,
    .sd-radio--checked .sd-radio__control:focus + .sd-radio__decorator:after,
    .sd-radio--checked .sd-item__control:focus + .sd-item__decorator:after {
        content: " " !important;
        display: block !important;
        width: 8px !important;
        height: 8px !important;
        border-radius: 50% !important;
        background-color: #ffffff !important;
        position: static !important;
        margin: auto !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
    }

    /* In dark mode: inner dot on primary */
    [data-theme="dark"] .sd-radio--checked .sd-radio__decorator:after,
    [data-theme="dark"] .sd-radio--checked .sd-item__decorator:after,
    [data-theme="dark"] .sd-item--checked .sd-radio__decorator:after,
    [data-theme="dark"] .sd-item--checked .sd-item__decorator:after,
    [data-theme="dark"] .sd-radio--checked .sd-radio__control:focus + .sd-radio__decorator:after,
    [data-theme="dark"] .sd-radio--checked .sd-item__control:focus + .sd-item__decorator:after {
        background-color: #080f1c !important;
        box-shadow: none !important;
    }

    /* Radio SVG (in preview/readonly mode if used) */
    .sd-radio--checked .sd-radio__decorator .sd-radio__svg,
    .sd-radio--checked .sd-radio__decorator .sd-radio__svg use {
        fill: #ffffff !important;
    }
    [data-theme="dark"] .sd-radio--checked .sd-radio__decorator .sd-radio__svg,
    [data-theme="dark"] .sd-radio--checked .sd-radio__decorator .sd-radio__svg use {
        fill: #080f1c !important;
    }

    /* Checked Checkbox: Checkmark SVG - ALWAYS visible */
    .sd-checkbox--checked .sd-checkbox__svg,
    .sd-checkbox--checked .sd-checkbox__svg use,
    .sd-checkbox--checked .sd-checkbox__decorator .sd-checkbox__svg,
    .sd-checkbox--checked .sd-checkbox__decorator .sd-checkbox__svg use,
    .sd-checkbox--checked .sd-checkbox__control:focus + .sd-checkbox__decorator .sd-checkbox__svg use {
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }
    [data-theme="dark"] .sd-checkbox--checked .sd-checkbox__svg,
    [data-theme="dark"] .sd-checkbox--checked .sd-checkbox__svg use,
    [data-theme="dark"] .sd-checkbox--checked .sd-checkbox__decorator .sd-checkbox__svg,
    [data-theme="dark"] .sd-checkbox--checked .sd-checkbox__decorator .sd-checkbox__svg use,
    [data-theme="dark"] .sd-checkbox--checked .sd-checkbox__control:focus + .sd-checkbox__decorator .sd-checkbox__svg use {
        fill: #080f1c !important;
        stroke: #080f1c !important;
    }

    /* Focus ring for keyboard accessibility */
    .sd-item__control:focus + .sd-item__decorator,
    .sd-radio__control:focus + .sd-radio__decorator,
    .sd-checkbox__control:focus + .sd-checkbox__decorator {
        outline: none !important;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px var(--primary-100) !important;
    }

    /* Table / Matrix item reset */
    .sd-table__cell--item .sd-item {
        padding: 4px !important;
        margin-bottom: 0 !important;
        background: transparent !important;
        border: none !important;
    }

    /* Rating, boolean, matrix */
    .sd-rating__item-text, .sd-rating__item {
        color: var(--text) !important;
        border-color: var(--border-strong) !important;
        background: var(--surface-2) !important;
    }
    .sd-rating__item--selected .sd-rating__item-text,
    .sd-rating__item--selected { background: var(--primary) !important; color: #fff !important; }
    .sd-boolean__label, .sd-matrix__cell, .sd-matrix__text, .sd-table__cell {
        color: var(--text) !important;
        border-color: var(--border) !important;
    }
    .sd-table { background: transparent !important; }
    .sd-table__row:hover { background: var(--surface-2) !important; }

    /* Progress */
    .sd-body__progress, .sd-progress {
        background: var(--primary-100) !important;
        border-radius: 999px !important;
        height: 8px !important;
    }
    .sd-progress { margin-bottom: 22px !important; }
    .sd-progress__bar {
        background: linear-gradient(90deg, var(--primary), var(--primary-600)) !important;
        border-radius: 999px !important;
    }
    .sd-body__progress-text {
        color: var(--text-3) !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        text-align: right !important;
        padding-bottom: 6px !important;
    }
    .sd-progress__text {
        color: var(--text-3) !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
    }
    .sd-progress-buttons__list { color: var(--text-3) !important; }

    /* Navigation */
    .sd-navigation, .sd-body__navigation, .sd-action-bar {
        padding: 22px 0 10px !important;
        margin-top: 8px !important;
        border-top: 1px solid var(--border) !important;
        background: transparent !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }
    .sd-btn, .sd-navigation__next-btn, .sd-navigation__prev-btn, .sd-navigation__complete-btn {
        border-radius: 10px !important;
        padding: 11px 24px !important;
        font-family: var(--font-sans) !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease !important;
    }
    .sd-btn--action, .sd-navigation__next-btn, .sd-navigation__complete-btn {
        background: linear-gradient(135deg, var(--primary), var(--primary-600)) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 8px 20px -12px rgba(11, 107, 189, 0.9) !important;
    }
    [data-theme="dark"] .sd-btn--action,
    [data-theme="dark"] .sd-navigation__next-btn,
    [data-theme="dark"] .sd-navigation__complete-btn { color: #04121f !important; }
    .sd-btn--action:hover, .sd-navigation__next-btn:hover, .sd-navigation__complete-btn:hover {
        transform: translateY(-1px);
    }
    .sd-navigation__prev-btn, .sd-btn:not(.sd-btn--action) {
        background: var(--surface-2) !important;
        color: var(--text-2) !important;
        border: 1px solid var(--border) !important;
    }
    .sd-navigation__prev-btn:hover, .sd-btn:not(.sd-btn--action):hover {
        color: var(--text) !important;
        border-color: var(--border-strong) !important;
    }

    /* Panels */
    .sd-panel__title, .sd-panel__description { color: var(--text) !important; }
    .sd-panel { border-radius: 12px !important; }
    .sd-panel--withborder, .sd-panel--withbackground { border-color: var(--border) !important; background: var(--surface-2) !important; }

    /* Errors */
    .sd-question__errtooltip, .sd-question__error {
        color: var(--danger) !important;
        font-size: 0.8rem !important;
    }
    .sd-input--error, .sd-question--error .sd-input { border-color: var(--danger) !important; }

    /* Popups & dropdown lists */
    .sv-popup { z-index: 10000 !important; }
    .sv-popup__container, .sv-popup__body-content {
        background: var(--surface) !important;
        color: var(--text) !important;
        border-color: var(--border) !important;
    }
    .sv-list__item, .sv-list__item-body, .sv-list__item span { color: var(--text) !important; }
    .sv-list__item:hover, .sv-list__item--focused { background: var(--surface-2) !important; }
    .sv-list__filter, .sv-list__input {
        background: var(--surface) !important;
        color: var(--text) !important;
        border-color: var(--border) !important;
    }
    .sv-list__input::placeholder { color: var(--text-3) !important; }

    .survey-note {
        margin-top: 18px;
        text-align: center;
        font-size: 0.76rem;
        line-height: 1.6;
        color: var(--text-3);
    }

    /* Hide SurveyJS watermark */
    .sv_watermark, .sv-logo, .sd-root-modern__watermark {
        display: none !important;
        visibility: hidden !important;
    }

    /* Hide navbar when a dropdown popup is open */
    body.sv-popup-open .navbar { display: none !important; }

    @media (max-width: 768px) {
        .sv-popup {
            position: fixed !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 10000 !important;
            background: rgba(8, 15, 28, 0.55) !important;
        }
        .sv-popup__container {
            position: fixed !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
            max-height: 100vh !important;
            max-width: 100vw !important;
            border-radius: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        .sv-popup__body-content {
            max-height: 100vh !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .sv-popup__scrolling-content { flex: 1 !important; overflow-y: auto !important; }
        .sv-list__filter {
            padding: 12px !important;
            background: var(--surface) !important;
            border-bottom: 1px solid var(--border) !important;
            flex-shrink: 0 !important;
            display: flex !important;
        }
        .sv-list__filter input, .sv-list__input {
            font-size: 16px !important;
            padding: 12px 14px !important;
            border: 1px solid var(--primary) !important;
            border-radius: 10px !important;
            background: var(--surface-2) !important;
            width: 100% !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }
    }

    /* --- Completion panels injected by JS --- */
    .done-panel { text-align: center; padding: 32px 12px; }
    .done-emoji { font-size: 2.6rem; line-height: 1; margin-bottom: 12px; }
    .done-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
    }
    .done-desc {
        font-size: 0.9rem;
        line-height: 1.65;
        color: var(--text-2);
        margin-bottom: 24px;
    }
    .done-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .done-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .done-btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-600));
        color: #fff !important;
    }
    [data-theme="dark"] .done-btn-primary { color: #04121f !important; }
    .done-btn-secondary {
        background: var(--surface-2);
        border-color: var(--border);
        color: var(--text-2) !important;
    }

    /* --- Quiz result modal --- */
    #quiz-result-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(8, 15, 28, 0.55);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    #quiz-result-modal.show { display: flex; animation: modalFadeIn 0.28s ease; }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .quiz-result-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 34px 32px;
        max-width: 440px;
        width: 100%;
        text-align: center;
        box-shadow: var(--shadow-lg);
    }
    .quiz-result-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        font-size: 2.1rem;
    }
    .quiz-result-icon.passed { background: var(--success-50); }
    .quiz-result-icon.failed { background: var(--danger-50); }
    .quiz-result-score {
        font-family: var(--font-display);
        font-size: 3.2rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.03em;
        margin-bottom: 6px;
    }
    .quiz-result-score.passed { color: var(--success); }
    .quiz-result-score.failed { color: var(--danger); }
    .quiz-result-label {
        font-family: var(--font-display);
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .quiz-result-label.passed { color: var(--text); }
    .quiz-result-label.failed { color: var(--text); }
    .quiz-result-passing {
        font-size: 0.82rem;
        color: var(--text-3);
        margin-bottom: 26px;
    }
    .quiz-result-actions { display: flex; flex-direction: column; gap: 10px; }
    .btn-receipt {
        display: block;
        text-align: center;
        padding: 12px 20px;
        background: var(--primary);
        color: #fff !important;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
    }
    [data-theme="dark"] .btn-receipt { color: #04121f !important; }
    .btn-retake {
        display: block;
        background: linear-gradient(135deg, var(--primary), var(--primary-600));
        color: #fff !important;
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.18s ease;
    }
    [data-theme="dark"] .btn-retake { color: #04121f !important; }
    .btn-retake:hover { transform: translateY(-1px); }
    .btn-back-list {
        display: block;
        background: var(--surface-2);
        color: var(--text-2) !important;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 12px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.18s ease, border-color 0.18s ease;
    }
    .btn-back-list:hover { color: var(--text) !important; border-color: var(--border-strong); }

    @media (max-width: 680px) {
        .runner { padding: 18px 16px 56px; }
        .head-card { padding: 24px 20px; }
        #surveyElement { padding: 22px 18px 8px; }
        .sd-question { padding: 12px 0 !important; }
        #surveyElement .sd-question:not(:last-child) { padding-bottom: 18px !important; margin-bottom: 2px !important; }
        .sd-question__content { margin-top: 10px !important; }
        .head-meta { gap: 8px 14px; }
        .quiz-result-card { padding: 28px 22px; }
        .preview-banner { align-items: flex-start; }
    }
</style>
@endsection

@section('content')
<div class="runner">
    <div class="runner-bar">
        <a href="{{ route('survey.index') }}" class="runner-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 7l-5 5 5 5"/></svg>
            Daftar form
        </a>
        <span class="runner-secure">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="M9.5 12l1.8 1.8 3.4-3.4"/></svg>
            Data terkirim dengan aman
        </span>
    </div>

    @if(!empty($isManager) || !empty($isPreview))
        <div class="preview-banner">
            <div class="preview-banner__main">
                <span class="pb-dot" aria-hidden="true"></span>
                <p><strong>Mode Pratinjau Pembuat Kuesioner.</strong> Anda melihat form ini sebagai administrator. Jawaban Anda tidak dihitung sebagai responden.</p>
            </div>
            <a href="{{ route('filament.admin.resources.survey.surveys.design', ['record' => $survey->id]) }}" class="preview-banner__link">
                Kembali ke desain
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 7l5 5-5 5"/></svg>
            </a>
        </div>
    @endif

    <header class="head-card">
        <div class="head-chips">
            <span class="chip chip-primary">{{ $survey->kategori?->nama ?? 'Umum' }}</span>
            @if($survey->is_quiz)
                <span class="chip chip-accent">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0z"/><path d="M17 5h3v2a3 3 0 01-3 3M7 5H4v2a3 3 0 003 3"/></svg>
                    Kuis
                </span>
            @endif
            <span class="chip">{{ $modeLabel }}</span>
        </div>

        <h1>{{ $survey->title }}</h1>

        @if($survey->description)
            <p class="head-desc">{{ $survey->description }}</p>
        @endif

        <div class="head-meta">
            <span>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                Diterbitkan {{ $survey->created_at?->translatedFormat('d M Y') ?? date('d M Y') }}
            </span>
            @if($questionCount > 0)
                <span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    {{ $questionCount }} pertanyaan
                </span>
            @endif
            <span>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/></svg>
                @if($survey->ends_at)
                    Batas waktu {{ $survey->ends_at->format('d M Y') }}
                @else
                    Tanpa batas waktu
                @endif
            </span>
        </div>
    </header>

    <!-- Draft Recovery Alert -->
    <div id="draft-recovery-alert" class="draft-alert hidden">
        <span class="draft-icon" aria-hidden="true">💾</span>
        <div class="draft-text">
            <strong>Draf pengisian ditemukan</strong>
            <span>Sistem mendeteksi jawaban yang tersimpan di perangkat ini. Pulihkan draf untuk melanjutkan.</span>
        </div>
        <div class="draft-actions">
            <button id="btn-restore-draft" type="button" class="btn-mini btn-mini-primary">Pulihkan</button>
            <button id="btn-discard-draft" type="button" class="btn-mini btn-mini-ghost">Abaikan</button>
        </div>
    </div>

    <!-- Status Area -->
    <div id="status-card">
        <div id="loading-box" class="state-panel">
            <div class="spinner" aria-hidden="true"></div>
            <p>Menyiapkan form...</p>
        </div>

        <div id="error-box" class="state-panel error hidden">
            <h2>Akses gagal</h2>
            <p id="error-msg"></p>
            <button type="button" onclick="window.location.reload()" class="btn btn-primary">Coba lagi</button>
        </div>
    </div>

    <!-- SurveyJS Area -->
    <div id="surveyElement"></div>

    <p class="survey-note">
        Jawaban Anda hanya digunakan untuk keperluan BPS Kabupaten Demak.
        Draf pengisian tersimpan sementara di perangkat ini.
    </p>
</div>

<!-- Quiz Result Modal -->
<div id="quiz-result-modal" role="dialog" aria-modal="true" aria-labelledby="quiz-result-label">
    <div class="quiz-result-card">
        <div class="quiz-result-icon" id="quiz-result-icon">🎉</div>
        <div class="quiz-result-score" id="quiz-result-score">0%</div>
        <div class="quiz-result-label" id="quiz-result-label">Selamat, Anda lulus!</div>
        <div class="quiz-result-passing" id="quiz-result-passing">Nilai standar kelulusan: 0%</div>
        <div class="quiz-result-actions">
            <a id="btn-receipt" href="#" target="_blank" class="btn-receipt" style="display:none;">
                Cetak / unduh bukti pengisian
            </a>
            <a id="btn-retake" href="{{ route('survey.show', $survey) }}" class="btn-retake" style="display:none;">
                Ulangi kuis
            </a>
            <a href="{{ route('survey.index') }}" class="btn-back-list">
                Kembali ke daftar form
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- SurveyJS Assets: Local Bundle with CDN Fallback --}}
<link href="{{ asset('vendor/surveyjs/defaultV2.min.css') }}" type="text/css" rel="stylesheet" onerror="this.onerror=null;this.href='https://unpkg.com/survey-core@1.12.14/defaultV2.min.css';">
<script src="{{ asset('vendor/surveyjs/survey.core.min.js') }}"></script>
<script>
    if (typeof Survey === 'undefined') {
        document.write('<script src="https://unpkg.com/survey-core@1.12.14/survey.core.min.js"><\/script>');
    }
</script>
<script src="{{ asset('vendor/surveyjs/survey-js-ui.min.js') }}"></script>
<script>
    if (typeof Survey === 'undefined' || typeof Survey.Model === 'undefined') {
        document.write('<script src="https://unpkg.com/survey-js-ui@1.12.14/survey-js-ui.min.js"><\/script>');
    }
</script>
<script src="{{ asset('vendor/surveyjs/indonesian.js') }}"></script>

<script>
    (function() {
        const loading = document.getElementById('loading-box');
        const errorBox = document.getElementById('error-box');
        const errorMsg = document.getElementById('error-msg');

        function fatal(txt) {
            loading.classList.add('hidden');
            errorBox.classList.remove('hidden');
            errorMsg.innerText = txt;
        }

        window.onerror = function(msg, url, line) {
            fatal("JS Error: " + msg + " at " + line);
            return false;
        };

        try {
            let rawData = {!! json_encode($survey->schema ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

            if (!rawData || !rawData.pages || rawData.pages.length === 0) {
                return fatal("Data pertanyaan kuesioner tidak ditemukan.");
            }

            if (typeof Survey === 'undefined') return fatal("Modul integrasi gagal dimuat.");

            // Force SurveyJS to use desktop-style dropdown popup (with search input)
            if (typeof Survey.IsTouch !== 'undefined') Survey.IsTouch = false;
            if (typeof Survey.IsMobile !== 'undefined') Survey.IsMobile = false;
            if (Survey.settings && Survey.settings.environment) {
                Survey.settings.environment.isMobile = false;
            }

            // Locale
            if (typeof Survey.localization !== 'undefined') {
                Survey.localization.currentLocale = "id";
            } else if (typeof Survey.surveyLocalization !== 'undefined') {
                Survey.surveyLocalization.currentLocale = "id";
            }

            const model = new Survey.Model(rawData);
            model.locale = "id";

            // Force search enabled on all dropdown questions
            model.getAllQuestions().forEach(q => {
                if (q.getType() === 'dropdown' || q.getType() === 'tagbox') {
                    q.searchEnabled = true;
                }
            });

            @if($survey->is_quiz)
                // Randomize questions order within pages
                model.questionsOrder = "random";
                // Randomize choices order for each question
                model.getAllQuestions().forEach(q => {
                    if (q.choicesOrder !== undefined) {
                        q.choicesOrder = "random";
                    }
                });
            @endif

            // Theme follows the site theme (light by default, dark when toggled)
            function currentPalette() {
                return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            }
            function applySurveyTheme() {
                model.applyTheme({
                    "themeName": "defaultV2",
                    "colorPalette": currentPalette(),
                    "isPanelless": false
                });
            }
            applySurveyTheme();
            window.addEventListener('themechange', applySurveyTheme);

            @if(isset($existingSubmission) && $existingSubmission)
                model.data = {!! json_encode($existingSubmission->payload ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
            @elseif(Auth::check())
                model.data = {
                    "nama_lengkap": {!! json_encode(Auth::user()->name) !!},
                    "nama": {!! json_encode(Auth::user()->name) !!},
                    "name": {!! json_encode(Auth::user()->name) !!},
                    "email_peserta": {!! json_encode(Auth::user()->email) !!},
                    "email": {!! json_encode(Auth::user()->email) !!},
                    "kelas": {!! json_encode(Auth::user()->metadata['kelas'] ?? '') !!},
                    "jabatan": {!! json_encode(Auth::user()->metadata['bertugas_sebagai'] ?? Auth::user()->metadata['jabatan'] ?? '') !!},
                    "bertugas_sebagai": {!! json_encode(Auth::user()->metadata['bertugas_sebagai'] ?? Auth::user()->metadata['jabatan'] ?? '') !!}
                };
            @endif

            @if($survey->mode === App\Enums\SurveyMode::Multi)
                model.completedHtml = `
                    <div class="done-panel">
                        <div class="done-emoji">✅</div>
                        <h3 class="done-title">Berhasil disimpan</h3>
                        <p class="done-desc">Jawaban Anda telah berhasil dikirim ke server.</p>
                        <button onclick="window.resetSurveyRunner()" class="done-btn done-btn-primary">Isi form baru</button>
                    </div>
                `;
            @endif

            // Auto-Save Draft Setup
            const draftKey = "survey_draft_{{ $survey->id }}_" + ({{ Auth::id() ?? 0 }});
            const draftAlert = document.getElementById('draft-recovery-alert');
            const btnRestore = document.getElementById('btn-restore-draft');
            const btnDiscard = document.getElementById('btn-discard-draft');

            try {
                const savedDraftStr = localStorage.getItem(draftKey);
                if (savedDraftStr) {
                    const savedDraft = JSON.parse(savedDraftStr);
                    const hasData = Object.keys(savedDraft).some(k => !['nama_lengkap', 'nama', 'name', 'email_peserta', 'email'].includes(k));
                    if (hasData && draftAlert) {
                        draftAlert.classList.remove('hidden');
                        if (btnRestore) {
                            btnRestore.addEventListener('click', function() {
                                model.data = Object.assign({}, model.data, savedDraft);
                                draftAlert.classList.add('hidden');
                            });
                        }
                        if (btnDiscard) {
                            btnDiscard.addEventListener('click', function() {
                                localStorage.removeItem(draftKey);
                                draftAlert.classList.add('hidden');
                            });
                        }
                    }
                }
            } catch(e) {
                console.warn('[AutoSave] Draft read error:', e);
            }

            // Auto-save on answer change with debounce
            let autoSaveTimer = null;
            model.onValueChanged.add(function(sender) {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(function() {
                    try {
                        localStorage.setItem(draftKey, JSON.stringify(sender.data));
                    } catch(e) {}
                }, 400);
            });

            window.resetSurveyRunner = function() {
                try { localStorage.removeItem(draftKey); } catch(e) {}
                model.clear(false);
                @if(Auth::check())
                model.data = {
                    "nama_lengkap": {!! json_encode(Auth::user()->name) !!},
                    "nama": {!! json_encode(Auth::user()->name) !!},
                    "name": {!! json_encode(Auth::user()->name) !!},
                    "email_peserta": {!! json_encode(Auth::user()->email) !!},
                    "email": {!! json_encode(Auth::user()->email) !!},
                    "kelas": {!! json_encode(Auth::user()->metadata['kelas'] ?? '') !!},
                    "jabatan": {!! json_encode(Auth::user()->metadata['bertugas_sebagai'] ?? Auth::user()->metadata['jabatan'] ?? '') !!},
                    "bertugas_sebagai": {!! json_encode(Auth::user()->metadata['bertugas_sebagai'] ?? Auth::user()->metadata['jabatan'] ?? '') !!}
                };
                @endif
                model.render("surveyElement");
            };

            model.completeText = "Kirim Jawaban";
            model.pageNextText = "Lanjut";
            model.pagePrevText = "Kembali";

            model.onComplete.add(function(sender) {
                loading.classList.remove('hidden');
                loading.querySelector('p').innerText = 'Mengirim data...';

                fetch("{{ route('survey.submit', $survey) }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
                    body: JSON.stringify({ payload: sender.data })
                })
                .then(r => r.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (data.success) {
                        try { localStorage.removeItem(draftKey); } catch(e) {}
                        // Show quiz result modal if this is a quiz
                        if (data.quiz) {
                            showQuizResult(data.quiz, data.receipt_url);
                        } else if (data.receipt_url && @json($survey->mode !== App\Enums\SurveyMode::Multi)) {
                            model.completedHtml = `
                                <div class="done-panel">
                                    <div class="done-emoji">✅</div>
                                    <h3 class="done-title">Terima kasih!</h3>
                                    <p class="done-desc">Jawaban Anda telah berhasil dikirim dan dicatat oleh sistem.</p>
                                    <div class="done-actions">
                                        <a href="${data.receipt_url}" target="_blank" class="done-btn done-btn-primary">Cetak / unduh bukti pengisian</a>
                                        <a href="{{ route('survey.index') }}" class="done-btn done-btn-secondary">Kembali ke daftar form</a>
                                    </div>
                                </div>
                            `;
                        }
                        // Otherwise completedHtml from SurveyJS handles the display
                    } else {
                        fatal(data.message || "Proses simpan gagal.");
                    }
                })
                .catch(err => fatal("Koneksi gagal tersambung."));
            });

            function showQuizResult(quiz, receiptUrl) {
                const modal   = document.getElementById('quiz-result-modal');
                const icon    = document.getElementById('quiz-result-icon');
                const score   = document.getElementById('quiz-result-score');
                const label   = document.getElementById('quiz-result-label');
                const passing = document.getElementById('quiz-result-passing');
                const btnRetake = document.getElementById('btn-retake');
                const btnReceipt = document.getElementById('btn-receipt');

                const passed = quiz.passed;
                const scoreVal = parseFloat(quiz.score).toFixed(1);
                const passingVal = parseFloat(quiz.passing_score).toFixed(0);

                icon.textContent   = passed ? '🎉' : '😔';
                icon.className     = 'quiz-result-icon ' + (passed ? 'passed' : 'failed');
                score.textContent  = scoreVal + '%';
                score.className    = 'quiz-result-score ' + (passed ? 'passed' : 'failed');
                label.className    = 'quiz-result-label ' + (passed ? 'passed' : 'failed');

                if (passed) {
                    label.textContent = 'Selamat, Anda lulus!';
                } else {
                    label.textContent = 'Belum lulus — coba lagi';
                }

                if (quiz.passing_score > 0) {
                    passing.textContent = 'Nilai standar kelulusan: ' + passingVal + '%';
                } else {
                    passing.textContent = 'Jawaban Anda telah berhasil disimpan.';
                }

                if (receiptUrl && btnReceipt) {
                    btnReceipt.href = receiptUrl;
                    btnReceipt.style.display = 'block';
                }

                if (quiz.can_retake) {
                    btnRetake.style.display = 'block';
                    if (passed) {
                        btnRetake.innerHTML = 'Coba lagi untuk tingkatkan nilai';
                    } else {
                        btnRetake.innerHTML = 'Ulangi kuis';
                    }
                } else {
                    btnRetake.style.display = 'none';
                }

                // Hide the survey container
                document.getElementById('surveyElement').style.display = 'none';
                modal.classList.add('show');
            }

            const container = document.getElementById("surveyElement");
            if (container) {
                model.render(container);
                loading.classList.add('hidden');
            } else {
                fatal("Kontainer tampilan tidak tersedia.");
            }

        } catch (e) {
            fatal("Masalah Sistem: " + e.message);
        }

        // SurveyJS 1.12 registers a window scroll handler that closes an open
        // popup. Focusing the dropdown search field scrolls the page, so the
        // popup closes the instant it opens. Keep that scroll from reaching it.
        window.addEventListener('scroll', function (e) {
            var popup = document.querySelector('.sv-popup');
            if (popup && popup.style.display !== 'none') {
                e.stopImmediatePropagation();
            }
        }, true);

        // === MOBILE POPUP FIX: HIDE NAVBAR + UNLOCK KEYBOARD ===

        function unlockPopupInputs(root) {
            if (!root || !root.querySelectorAll) return;
            var inputs = root.querySelectorAll('input[type="text"], input:not([type])');
            inputs.forEach(function(input) {
                input.removeAttribute('readonly');
                if (input.getAttribute('inputmode') === 'none') {
                    input.setAttribute('inputmode', 'text');
                }
            });
        }

        function onPopupOpened(popupNode) {
            document.body.classList.add('sv-popup-open');
            unlockPopupInputs(popupNode);
            setTimeout(function() {
                unlockPopupInputs(popupNode);
                var searchInput = popupNode.querySelector('.sv-list__filter input, .sv-list__input, input[type="text"]');
                if (searchInput) {
                    searchInput.removeAttribute('readonly');
                    searchInput.setAttribute('inputmode', 'text');
                    searchInput.focus();
                }
            }, 150);
        }

        function checkPopupsClosed() {
            var visiblePopup = document.querySelector('.sv-popup[style*="visibility: visible"], .sv-popup[style*="display: block"], .sv-popup:not([style*="display: none"]):not([style*="visibility: hidden"])');
            if (!visiblePopup) {
                document.body.classList.remove('sv-popup-open');
            }
        }

        var observer = new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var mutation = mutations[i];

                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if (node.classList && node.classList.contains('sv-popup')) {
                            onPopupOpened(node);
                        }
                        if (node.querySelector) {
                            var popup = node.querySelector('.sv-popup');
                            if (popup) onPopupOpened(popup);
                        }
                    });
                    mutation.removedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if ((node.classList && node.classList.contains('sv-popup')) || (node.querySelector && node.querySelector('.sv-popup'))) {
                            setTimeout(checkPopupsClosed, 100);
                        }
                    });
                }

                if (mutation.type === 'attributes') {
                    var target = mutation.target;
                    if (target.classList && target.classList.contains('sv-popup')) {
                        var style = target.getAttribute('style') || '';
                        if (style.indexOf('visible') > -1 || style.indexOf('block') > -1) {
                            onPopupOpened(target);
                        } else {
                            setTimeout(checkPopupsClosed, 100);
                        }
                    }
                    if (target.tagName === 'INPUT' && target.closest('.sv-popup')) {
                        if (target.hasAttribute('readonly')) target.removeAttribute('readonly');
                        if (target.getAttribute('inputmode') === 'none') target.setAttribute('inputmode', 'text');
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['readonly', 'inputmode', 'style', 'class']
        });

        document.addEventListener('touchstart', function(e) {
            var input = e.target;
            if (input.tagName === 'INPUT' && input.closest('.sv-popup')) {
                input.removeAttribute('readonly');
                input.setAttribute('inputmode', 'text');
                setTimeout(function() { input.focus(); }, 50);
            }
        }, { passive: true });

        setInterval(function() {
            var popup = document.querySelector('.sv-popup');
            if (popup) {
                var style = popup.getAttribute('style') || '';
                if (style.indexOf('hidden') === -1 && style.indexOf('none') === -1) {
                    document.body.classList.add('sv-popup-open');
                    unlockPopupInputs(popup);
                } else {
                    document.body.classList.remove('sv-popup-open');
                }
            } else {
                document.body.classList.remove('sv-popup-open');
            }
        }, 500);
    })();
</script>
@endsection

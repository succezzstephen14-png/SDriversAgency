@extends('layouts.app')

@section('title', 'Home')

@section('content')
<style>
/* Color Variables from CMS Settings */
:root {
    --primary-color: {{ $settings->primary_color ?? '#6366f1' }};
    --secondary-color: {{ $settings->secondary_color ?? '#8b5cf6' }};
    --font-color: {{ $settings->font_color ?? '#1f2937' }};
}

.audience-carousel-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    max-width: 100%;
    min-height: 42px;
    margin: .75rem auto 0;
    padding: .65rem 1rem;
    border: 1px solid rgba(23, 23, 23, .25);
    border-radius: 999px;
    background: #fff;
    color: #171717;
    font-size: .85rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
    transition: background .2s ease, transform .2s ease;
}

/* Homepage hero */
.home-hero {
    position: relative;
    overflow: hidden;
    min-height: 720px;
    color: #171717;
    background: #f5f5f5;
}

.home-hero::before {
    content: '';
    position: absolute;
    inset: -8px;
    z-index: 0;
    background: url('https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=2000&q=85') center 56% / cover no-repeat;
    filter: blur(4px);
}

.home-hero-inner {
    position: relative;
    z-index: 1;
    width: 100%;
    min-height: 720px;
    margin: 0 auto;
    padding: clamp(2rem, 5vh, 3.5rem) clamp(1rem, 4vw, 4rem);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.home-hero-heading {
    text-align: center;
    max-width: none;
    margin: 0 auto 2rem;
    animation: heroRiseIn .7s ease-out both;
}

.home-hero-heading h1 span {
    color: #1D5BD8;
}

.home-hero-heading h1 {
    margin: 0 auto 1rem;
    max-width: none;
    color: #1D5BD8;
    font-size: clamp(2.65rem, 5.8vw, 5rem);
    font-weight: 800;
    letter-spacing: -.055em;
    line-height: 1.04;
    text-wrap: balance;
}

.home-hero-title-line {
    display: block;
    white-space: nowrap;
}

.home-hero-heading p {
    max-width: 640px;
    margin: 0 auto;
    padding: .45rem .8rem;
    border-radius: .5rem;
    background: rgba(255, 255, 255, .9);
    color: #171717;
    font-size: clamp(1rem, 1.6vw, 1.2rem);
    line-height: 1.7;
}

.audience-carousel {
    display: flex;
    flex: 1;
    flex-direction: column;
    width: 100%;
    margin: 0 auto;
}

.audience-carousel-viewport {
    flex: 1;
    overflow: hidden;
    border-radius: 1.35rem;
}

.audience-track {
    display: flex;
    height: 100%;
    transition: transform .5s cubic-bezier(.22, .68, 0, 1);
}

.audience-slide {
    display: flex;
    flex: 0 0 100%;
    justify-content: center;
    height: 100%;
    min-width: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, .48), rgba(0, 0, 0, .18));
}

.audience-card {
    position: relative;
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    padding: clamp(1.5rem, 3vw, 2.25rem);
    overflow: hidden;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    color: #fff;
    text-align: center;
    min-height: 460px;
    transition: transform .25s ease, box-shadow .25s ease;
    animation: heroRiseIn .7s ease-out both;
}

.audience-card:hover {
    transform: none;
    box-shadow: none;
}

.audience-card-top {
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: .9rem;
    margin-bottom: 1rem;
}


.audience-slide .audience-label,
.audience-slide .audience-card h2 {
    color: #fff;
}
.audience-icon {
    display: grid;
    place-items: center;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: transparent;
    color: #fff;
    font-size: 1.1rem;
}

.audience-label,
.audience-card h2 {
    color: #fff;
    text-shadow: 0 2px 8px rgba(0, 0, 0, .55);
}

.audience-carousel-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: .8rem;
}

.audience-carousel-arrow {
    display: inline-grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border: 1px solid rgba(23, 23, 23, .25);
    border-radius: 50%;
    background: #fff;
    color: #171717;
    transition: background .2s ease, transform .2s ease;
}

.audience-carousel-arrow:hover {
    transform: translateY(-2px);
    background: rgba(201, 33, 48, .9);
}

.audience-carousel-dots {
    display: flex;
    align-items: center;
    gap: .55rem;
}

.audience-carousel-dot {
    width: 10px;
    height: 10px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: rgba(23, 23, 23, .35);
    transition: background .2s ease, transform .2s ease;
}

.audience-carousel-dot[aria-current="true"] {
    transform: scale(1.25);
    background: #f0525f;
}

.audience-carousel-arrow:focus-visible,
.audience-carousel-toggle:focus-visible,
.audience-carousel-dot:focus-visible {
    outline: 3px solid #c92130;
    outline-offset: 4px;
}

.audience-label {
    margin: 0;
    color: #737373;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.audience-card h2 {
    margin: 0 0 .55rem;
    color: #171717;
    font-size: clamp(1.6rem, 2.6vw, 2rem);
    font-weight: 750;
    letter-spacing: -.03em;
}

.audience-card-copy {
    min-height: 3.2em;
    margin: 0 0 2rem;
    color: rgba(255, 255, 255, .96);
    font-size: 1.05rem;
    line-height: 1.75;
    text-shadow: 0 1px 5px rgba(0, 0, 0, .7);
}

.audience-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    width: min(100%, 720px);
    gap: .65rem;
}

.audience-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    min-height: 54px;
    padding: .8rem 1rem;
    border: 1px solid #dedede;
    border-radius: .7rem;
    background: #fff;
    color: #262626;
    font-size: .88rem;
    font-weight: 650;
    line-height: 1.3;
    text-align: center;
    text-decoration: none;
    transition: color .2s ease, background .2s ease, border-color .2s ease, transform .2s ease;
}

.audience-action:hover {
    transform: translateY(-2px);
    border-color: #bdbdbd;
    background: #f7f7f7;
    color: #111;
}

.audience-text-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 54px;
    padding: .8rem 1rem;
    color: #fff;
    font-size: .88rem;
    font-weight: 700;
    line-height: 1.3;
    text-align: center;
    text-decoration: underline;
    text-underline-offset: .2em;
    text-shadow: 0 1px 5px rgba(0, 0, 0, .7);
    transition: color .2s ease;
}

.audience-text-link:hover {
    color: #dbeafe;
}

.audience-action:focus-visible,
.audience-teaser:focus-visible {
    outline: 3px solid #c92130;
    outline-offset: 3px;
}

.audience-action-primary {
    grid-column: 1 / -1;
    border-color: #1D5BD8;
    background: #1D5BD8;
    color: #fff;
    font-weight: 700;
}

.audience-action-primary:hover {
    border-color: #194FB8;
    background: #194FB8;
    color: #fff;
}

.audience-teaser {
    align-self: center;
    margin-top: 1.1rem;
    color: rgba(255, 255, 255, .96);
    font-size: .86rem;
    font-weight: 600;
    text-shadow: 0 1px 5px rgba(0, 0, 0, .7);
    text-decoration: none;
    transition: color .2s ease;
}

.audience-teaser:hover {
    color: #fff;
}

.home-hero {
    min-height: calc(100svh - 72px);
    color: #fff;
    background: #080d19;
}

.home-hero::before {
    filter: blur(6px) brightness(.42);
}

.home-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(105deg, rgba(5, 10, 24, .88) 0%, rgba(7, 12, 26, .72) 55%, rgba(4, 8, 18, .54) 100%), radial-gradient(ellipse at 76% 80%, rgba(126, 104, 35, .34), transparent 48%);
}

.home-hero-inner {
    width: min(1440px, 100%);
    min-height: calc(100svh - 72px);
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(380px, .9fr);
    align-items: center;
    gap: clamp(3rem, 8vw, 8rem);
    padding: clamp(3rem, 6vh, 5rem) clamp(1.5rem, 5.5vw, 5rem);
}

.home-hero-heading {
    max-width: 560px;
    margin: 0;
    text-align: left;
}

.home-hero-heading h1 {
    max-width: 560px;
    margin: 0 0 1.25rem;
    color: #fff;
    font-size: clamp(3.2rem, 5.2vw, 4.2rem);
    letter-spacing: 0;
    line-height: 1.03;
}

.home-hero-heading h1 span {
    display: block;
    color: #83a9ff;
}

.home-hero-title-line {
    white-space: normal;
}

.home-hero-heading p {
    max-width: 500px;
    margin: 0;
    padding: 0;
    border-radius: 0;
    background: transparent;
    color: rgba(255, 255, 255, .9);
    font-size: 1.2rem;
    line-height: 1.55;
}

.home-hero-cta {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    min-height: 56px;
    margin-top: 2rem;
    padding: .9rem 1.5rem;
    border-radius: .75rem;
    background: #1D5BD8;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    transition: background .2s ease, transform .2s ease;
}

.home-hero-cta:hover {
    transform: translateY(-2px);
    background: #194FB8;
    color: #fff;
}

.audience-carousel {
    display: block;
    flex: initial;
    width: 100%;
}

.audience-carousel-viewport {
    flex: initial;
    border-radius: 1.25rem;
}

.audience-track {
    height: auto;
}

.audience-slide {
    min-height: 360px;
    background: transparent;
}

.audience-card {
    align-items: flex-start;
    justify-content: center;
    min-height: 360px;
    padding: clamp(1.5rem, 3vw, 2rem);
    border: 1px solid rgba(255, 255, 255, .17);
    border-radius: 1.25rem;
    background: rgba(9, 14, 26, .78);
    box-shadow: 0 24px 60px rgba(0, 0, 0, .22);
    text-align: left;
    backdrop-filter: blur(14px);
}

.audience-card-top {
    align-items: flex-start;
    margin-bottom: .65rem;
}

.audience-icon {
    display: none;
}

.audience-label,
.audience-slide .audience-label {
    color: #a9c2ff;
    text-shadow: none;
}

.audience-card h2,
.audience-slide .audience-card h2 {
    color: #fff;
    font-size: 1.75rem;
    text-shadow: none;
}

.audience-card-copy {
    min-height: 0;
    margin-bottom: 1.25rem;
}

.audience-actions {
    grid-template-columns: 1fr;
    width: 100%;
    gap: .55rem;
}

.audience-slide-owner .audience-card-copy,
.audience-slide-owner .audience-teaser {
    display: none;
}

.audience-option {
    display: flex;
    align-items: center;
    gap: .9rem;
    min-height: 76px;
    padding: .8rem 1rem;
    border: 1px solid rgba(255, 255, 255, .1);
    border-radius: .8rem;
    background: rgba(255, 255, 255, .09);
    color: #fff;
    text-decoration: none;
    transition: background .2s ease, border-color .2s ease;
}

.audience-option:hover {
    border-color: rgba(255, 255, 255, .25);
    background: rgba(255, 255, 255, .14);
    color: #fff;
}

.audience-option-icon {
    display: grid;
    place-items: center;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #1D5BD8;
    color: #fff;
}

.audience-option-copy {
    display: grid;
    flex: 1;
    gap: .15rem;
    text-align: left;
}

.audience-option-copy strong {
    color: #fff;
    font-size: .95rem;
}

.audience-option-copy small {
    color: rgba(255, 255, 255, .78);
    font-size: .82rem;
}

.audience-option-arrow {
    color: #b9caff;
}

@media (max-width: 991.98px) {
    .home-hero-inner {
        grid-template-columns: minmax(0, 1fr);
        align-items: stretch;
        gap: 2rem;
    }

    .home-hero-heading {
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
    }

    .audience-carousel {
        width: min(100%, 560px);
        margin: 0 auto;
    }
}

@keyframes heroRiseIn {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 767.98px) {
    .home-hero,
    .home-hero-inner {
        min-height: 0;
    }

    .home-hero-inner {
        grid-template-columns: minmax(0, 1fr);
        align-items: stretch;
        gap: 2rem;
        padding: 3.5rem 1rem 2rem;
    }

    .home-hero-heading,
    .audience-carousel {
        width: 100%;
        min-width: 0;
    }

    .home-hero-heading {
        margin-bottom: 0;
    }

    .home-hero-heading h1 {
        font-size: 2.75rem;
    }

    .audience-carousel {
        max-width: 560px;
        margin: 0 auto;
    }

    .audience-slide {
        min-height: 340px;
    }

    .audience-card {
        min-height: 340px;
    }

    .audience-slide {
        padding: 0;
    }

    .audience-card {
        min-height: 390px;
        padding: 2rem 1.5rem;
    }

    .audience-card-copy {
        min-height: 0;
    }
}

@media (max-width: 420px) {
    .home-hero-heading h1 {
        font-size: 2.5rem;
    }

    .audience-card {
        padding: 1.35rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .home-hero *,
    .home-hero *::before,
    .home-hero *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}

/* Services Section Styles */
.services-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 5rem 0;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #6b7280;
    margin-bottom: 3rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.service-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e5e7eb;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    min-height: 320px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(29, 78, 216, 0.05) 100%);
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.95) 0%, rgba(29, 78, 216, 0.95) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
    border-radius: 20px;
}

.service-card:hover::before {
    opacity: 1;
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.service-card:hover .service-icon {
    transform: scale(1.1);
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
}

.service-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
    transition: color 0.3s ease;
}

.service-card:hover .service-title {
    color: white;
}

.service-preview {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex-grow: 1;
    position: relative;
    z-index: 2;
    transition: color 0.3s ease;
}

.service-card:hover .service-preview {
    color: rgba(255, 255, 255, 0.9);
}

.service-hover-content {
    display: none;
    opacity: 0;
    transform: translateY(10px);
    margin-top: auto;
    position: relative;
    z-index: 3;
    visibility: hidden;
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.service-card:hover .service-hover-content {
    display: block;
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
}

.service-description {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
    position: relative;
    z-index: 3;
    transition: color 0.3s ease;
}

.service-card:hover .service-description {
    color: rgba(255, 255, 255, 0.9);
}

.btn-service {
    background: white;
    color: #3b82f6;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 3;
}

.btn-service:hover {
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    color: #3b82f6;
    text-decoration: none;
}

/* About Section Styles */
.about-section {
    padding: 5rem 0;
}

.about-features .feature-item {
    display: flex;
    align-items: flex-start;
}

.about-features .feature-item i {
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.about-features h5 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.about-image img {
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

/* Contact Section Styles */
.contact-section {
    color: white;
}

.contact-form-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.contact-form-card h3 {
    color: #1f2937;
    font-weight: 700;
}

.form-label {
    font-weight: 600;
    color: #374151;
}

.form-control {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.contact-info h3 {
    color: white;
    font-weight: 700;
    margin-bottom: 1rem;
}

.contact-details {
    margin-top: 2rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.contact-icon {
    width: 50px;
    height: 50px;
    background: rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.contact-content h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: white;
}

.contact-content p {
    color: #d1d5db;
    margin-bottom: 0.25rem;
    line-height: 1.5;
}

.contact-content {
    min-width: 0;
    overflow-wrap: anywhere;
}

.contact-content a {
    overflow-wrap: anywhere;
}

/* Why Choose Us Section Styles */
.why-choose-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 5rem 0;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.benefit-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.benefit-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.benefit-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin: 0 auto 1.5rem;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
}

.benefit-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.benefit-description {
    color: #6b7280;
    line-height: 1.6;
    font-size: 1rem;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.cta-buttons .btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.cta-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* Services Section Styles */
.services-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 5rem 0;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.service-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.95) 0%, rgba(29, 78, 216, 0.95) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.service-card:hover::before {
    opacity: 1;
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin: 0 auto 1.5rem;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.service-card:hover .service-icon {
    transform: scale(1.1);
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.4);
}

.service-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
    transition: color 0.3s ease;
}

.service-card:hover .service-title {
    color: white;
}

.service-preview {
    color: #6b7280;
    line-height: 1.6;
    font-size: 1rem;
    position: relative;
    z-index: 2;
    transition: color 0.3s ease;
}

.service-card:hover .service-preview {
    color: rgba(255, 255, 255, 0.9);
}

/* Success Message Styles */
.alert-success {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 1rem 1.5rem;
    margin-top: 1rem;
    display: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .service-card {
        padding: 1.5rem;
    }

    .section-title {
        font-size: 2rem;
    }

    .about-section .row {
        text-align: center;
    }

    .about-features .feature-item {
        justify-content: center;
        text-align: center;
    }

    .contact-section .row {
        --bs-gutter-x: 1.5rem !important;
    }

    .contact-section .contact-item {
        flex-direction: row;
        align-items: flex-start;
        text-align: left;
        gap: 0.875rem;
    }

    .contact-section .contact-icon {
        width: 44px;
        height: 44px;
        align-self: flex-start;
        font-size: 1rem;
    }

    .contact-section .contact-content {
        flex: 1;
    }

    .contact-section .contact-info,
    .contact-section .contact-form-card {
        min-width: 0;
    }

    .benefits-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .benefit-card {
        padding: 2rem 1.5rem;
    }

    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }

    .cta-buttons .btn {
        width: 100%;
        max-width: 300px;
    }

    .services-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .service-card {
        padding: 2rem 1.5rem;
    }

    .service-hover-content {
        display: block;
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
}

@media (max-width: 480px) {
    .services-section,
    .about-section {
        padding: 3rem 0;
    }

    .policy-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 1.25rem;
    }

    .policy-card {
        padding: 1.5rem 1.25rem;
    }

    .section-inner > .row {
        --bs-gutter-x: 1rem;
    }

    .contact-section .row {
        --bs-gutter-x: 1rem !important;
    }

    .service-card {
        padding: 1.25rem;
    }

    .contact-form-card {
        padding: 1.25rem;
    }

    .contact-section .section-title {
        font-size: 2rem;
    }

    .contact-section .section-subtitle {
        font-size: 1rem;
    }

    .contact-section .contact-content h5 {
        font-size: 1rem;
    }

    .contact-section .contact-content p,
    .contact-section .contact-content a {
        overflow-wrap: anywhere;
    }
}

/* Smooth Scrolling */
html {
    scroll-behavior: smooth;
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mission & Vision Section Styles */
.mission-vision-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 3rem 2rem;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.mission-vision-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
}

.mv-header {
    margin-bottom: 2rem;
}

.mv-icon {
    font-size: 3rem;
    color: #3b82f6;
    margin-bottom: 1rem;
    display: block;
}

.mv-header h3 {
    color: white;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.mv-text {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    line-height: 1.6;
    margin: 0;
}

/* Driver Policy Section Styles */
.policy-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.policy-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.policy-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.policy-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.policy-icon {
    font-size: 2rem;
    color: #3b82f6;
    flex-shrink: 0;
}

.policy-header h3 {
    color: #1f2937;
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
}

.policy-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.policy-list li {
    color: #6b7280;
    margin-bottom: 0.75rem;
    padding-left: 1.5rem;
    position: relative;
    line-height: 1.5;
}

.policy-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.policy-card p {
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

/* Success Modal Styles */
.success-icon i {
    color: #10b981;
    animation: checkmark 0.8s ease-in-out;
}

@keyframes checkmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.modal-content {
    border-radius: 20px;
}

.modal-body {
    padding: 3rem 2rem;
}

@media (max-width: 576px) {
    .modal-body {
        padding: 2rem 1.5rem;
    }
    .success-icon i {
        font-size: 3rem !important;
    }
}
</style>
<section class="home-hero" aria-labelledby="home-hero-title">
    <div class="home-hero-inner">
        <header class="home-hero-heading">
            <h1 id="home-hero-title">Your Next Opportunity<br><span>Starts Here.</span></h1>
            <p>Professional drivers. Trusted partnerships. Opportunities that move you forward.</p>
            <a class="home-hero-cta" href="{{ route('register') }}">Get Started <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </header>

        <div class="audience-carousel" role="region" aria-label="Choose your path" aria-roledescription="carousel" aria-keyshortcuts="ArrowLeft ArrowRight" tabindex="0" id="audienceCarousel">
            <div class="audience-carousel-viewport">
                <div class="audience-track" id="audienceTrack" aria-live="off">
                    <div class="audience-slide" role="group" aria-roledescription="slide" aria-label="1 of 4: For Drivers" aria-hidden="true" inert>
                        <article class="audience-card">
                            <div class="audience-card-top">
                                <span class="audience-icon"><i class="fas fa-id-card" aria-hidden="true"></i></span>
                                <p class="audience-label">For Drivers</p>
                            </div>
                            <h2>Are You a Driver?</h2>
                            <p class="audience-card-copy">Find opportunities, build your future, and work towards owning your own vehicle.</p>
                            <div class="audience-actions">
                                <a class="audience-text-link" href="{{ route('recruitments.index') }}">Find Driving Jobs</a>
                                <a class="audience-text-link" href="{{ route('service.register.vehicle') }}">Work &amp; Pay</a>
                                <a class="audience-action audience-action-primary" href="{{ route('register') }}">Get Started <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                            <a class="audience-teaser" href="{{ route('recruitments.index') }}">Explore opportunities <span aria-hidden="true">→</span></a>
                        </article>
                    </div>

                    <div class="audience-slide audience-slide-owner" role="group" aria-roledescription="slide" aria-label="2 of 4: For Vehicle Owners">
                        <article class="audience-card">
                            <div class="audience-card-top">
                                <span class="audience-icon"><i class="fas fa-car" aria-hidden="true"></i></span>
                                <p class="audience-label">For Vehicle Owners</p>
                            </div>
                            <h2>Own a Vehicle?</h2>
                            <p class="audience-card-copy">Connect with professional drivers and discover services designed around your vehicle.</p>
                            <div class="audience-actions">
                                <a class="audience-option" href="{{ route('vehicles.create') }}">
                                    <span class="audience-option-icon"><i class="fas fa-car" aria-hidden="true"></i></span>
                                    <span class="audience-option-copy"><strong>Register a Vehicle</strong><small>List your car for approved drivers</small></span>
                                    <i class="fas fa-chevron-right audience-option-arrow" aria-hidden="true"></i>
                                </a>
                                <a class="audience-option" href="{{ route('drivers.index') }}">
                                    <span class="audience-option-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
                                    <span class="audience-option-copy"><strong>Find a Driver</strong><small>Match with a verified professional</small></span>
                                    <i class="fas fa-chevron-right audience-option-arrow" aria-hidden="true"></i>
                                </a>
                            </div>
                            <a class="audience-teaser" href="{{ route('vehicles.create') }}">Explore owner services <span aria-hidden="true">→</span></a>
                        </article>
                    </div>

                    <div class="audience-slide" role="group" aria-roledescription="slide" aria-label="3 of 4: For Businesses and Companies" aria-hidden="true" inert>
                        <article class="audience-card">
                            <div class="audience-card-top">
                                <span class="audience-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                                <p class="audience-label">For Businesses &amp; Companies</p>
                            </div>
                            <h2>Looking for a Driver?</h2>
                            <p class="audience-card-copy">Request a professional driver for your business and keep your people and operations moving.</p>
                            <div class="audience-actions">
                                <a class="audience-action audience-action-primary" href="{{ route('service.login', ['requestType' => 'driver']) }}">Request a Driver <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                                <a class="audience-text-link" href="{{ route('service.login', ['requestType' => 'driver']) }}">Show business driver requests</a>
                            </div>
                            <a class="audience-teaser" href="{{ route('service.login', ['requestType' => 'driver']) }}">Sign in to request a driver <span aria-hidden="true">→</span></a>
                        </article>
                    </div>

                    <div class="audience-slide" role="group" aria-roledescription="slide" aria-label="4 of 4: Drivers Looking for a Vehicle" aria-hidden="true" inert>
                        <article class="audience-card">
                            <div class="audience-card-top">
                                <span class="audience-icon"><i class="fas fa-car" aria-hidden="true"></i></span>
                                <p class="audience-label">For Drivers</p>
                            </div>
                            <h2>Looking for a vehicle to work with?</h2>
                            <p class="audience-card-copy">Request a vehicle and find the right opportunity to keep you moving.</p>
                            <div class="audience-actions">
                                <a class="audience-action audience-action-primary" href="{{ route('service.login', ['requestType' => 'vehicle']) }}">Request a Vehicle <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                            <a class="audience-teaser" href="{{ route('service.login', ['requestType' => 'vehicle']) }}">Sign in to request a vehicle <span aria-hidden="true">→</span></a>
                        </article>
                    </div>
                </div>
            </div>

            <div class="audience-carousel-controls" aria-label="Carousel controls">
                <button type="button" class="audience-carousel-arrow" aria-label="Previous audience" data-direction="prev">&larr;</button>
                <div class="audience-carousel-dots" aria-label="Choose a slide">
                    <button type="button" class="audience-carousel-dot" aria-label="Show slide 1" aria-current="true"></button>
                    <button type="button" class="audience-carousel-dot" aria-label="Show slide 2" aria-current="false"></button>
                    <button type="button" class="audience-carousel-dot" aria-label="Show slide 3" aria-current="false"></button>
                    <button type="button" class="audience-carousel-dot" aria-label="Show slide 4" aria-current="false"></button>
                </div>
                <button type="button" class="audience-carousel-arrow" aria-label="Next audience" data-direction="next">&rarr;</button>
            </div>
            <button type="button" class="audience-carousel-toggle" id="audienceCarouselToggle" aria-label="Pause automatic slides" aria-pressed="false">Pause automatic slides</button>
            <span class="visually-hidden" id="audienceCarouselStatus" role="status" aria-live="polite">2 of 4: For Vehicle Owners</span>
        </div>
    </div>
</section>

@if($siteSettings && $siteSettings->home_page_content)
<section class="custom-content-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                
            </div>
        </div>
    </div>
</section>
@endif

<section class="section-light">
    <div class="section-inner">
        <div class="row align-items-center">
            <div class="col-lg-6 pe-lg-5">
                <h2 class="mb-4">Intelligent driving starts here</h2>
                <p class="text-muted">Unlock next-level coordination for drivers, vehicles, and recruitment workflows. Discover the built-in tools that make recruitment more efficient and safer.</p>
                <div class="section-feature mt-4">
                    @forelse($featureCards ?? [] as $card)
                    <div class="feature-card">
                        <h5>{{ $card->title }}</h5>
                        <p>{{ $card->description }}</p>
                    </div>
                    @empty
                    <div class="feature-card">
                        <h5>Real-time tracking</h5>
                        <p>Monitor license approvals, vehicle status, and driver availability in one place.</p>
                    </div>
                    <div class="feature-card">
                        <h5>Fast approvals</h5>
                        <p>Reduce onboarding time with a streamlined driver and vehicle review process.</p>
                    </div>
                    <div class="feature-card">
                        <h5>Secure matching</h5>
                        <p>Match qualified drivers to opportunities with transparent profiles and performance details.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="listing-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="badge">90%</span>
                            <p class="mb-0 text-muted">Platform readiness</p>
                        </div>
                        <span class="text-secondary">Live metrics</span>
                    </div>
                    <div class="mb-4">
                        <h5>Modern vehicle onboarding</h5>
                        <p class="mb-0">Capture vehicle details, license status and availability with an interface built for fast decisions.</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="feature-card">
                                <p class="mb-1 text-secondary">Driver rating</p>
                                <strong>4.9/5</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-card">
                                <p class="mb-1 text-secondary">Approval speed</p>
                                <strong>2.4 days</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section-dark">
    <div class="section-inner">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 class="section-title">Why Choose SUCCEZZ Drivers Agency</h2>
            <p class="section-subtitle">Experience the difference with our professional transportation solutions</p>
        </div>

        <div class="benefits-grid">
            <!-- Professional Drivers -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 class="benefit-title">Professional Drivers</h3>
                <p class="benefit-description">Connect with verified, experienced drivers who undergo thorough background checks and license verification for your peace of mind.</p>
            </div>

            <!-- Reliable Vehicles -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3 class="benefit-title">Reliable Vehicles</h3>
                <p class="benefit-description">Access a wide range of well-maintained vehicles including cars, vans, and trucks, all with comprehensive insurance coverage.</p>
            </div>

            <!-- Easy Booking Process -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="benefit-title">Easy Booking Process</h3>
                <p class="benefit-description">Simple and secure booking system with real-time confirmation, tracking, and instant communication between all parties.</p>
            </div>

            <!-- Trusted Service -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="benefit-title">Trusted Service</h3>
                <p class="benefit-description">Building lasting partnerships with transparent profiles, performance tracking, and commitment to excellence in service delivery.</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <div class="cta-buttons">
                <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-car me-2"></i>Browse Vehicles
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-briefcase me-2"></i>Join Our Team
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-accent" id="testimonials">
    <div class="section-inner">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-subtitle">Testimonials from satisfied clients</p>
        </div>
        <style>
            .testimonial-grid {
                display: grid;
                gap: 1.5rem;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            }

            .testimonial-card {
                background: white;
                border-radius: 24px;
                padding: 2rem;
                box-shadow: 0 20px 60px rgba(15, 23, 42, 0.09);
                border: 1px solid rgba(15, 23, 42, 0.08);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .testimonial-card p {
                color: #475569;
                line-height: 1.85;
                margin-bottom: 1.75rem;
                font-size: 0.98rem;
            }

            .testimonial-author {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .testimonial-author strong {
                font-size: 1rem;
                color: #0f172a;
            }

            .testimonial-author span {
                color: #64748b;
                font-size: 0.92rem;
            }
        }

/* Contact Link Hover Effects */
.hover-primary:hover {
    color: #60a5fa !important;
    transition: color 0.3s ease;
}

/* Map Container Styles */
.map-container {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

.map-container:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.map-container iframe {
    width: 100%;
    height: 400px;
    border: none;
    border-radius: 10px;
}

/* Scroll to Top Button Styles */
#scrollToTopBtn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background-color: #0d6efd;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    display: none;
    z-index: 999;
    opacity: 0;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
    align-items: center;
    justify-content: center;
}

#scrollToTopBtn.show {
    display: flex;
    opacity: 1;
}

#scrollToTopBtn:hover {
    background-color: #0b5ed7;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(13, 110, 253, 0.6);
}

#scrollToTopBtn:active {
    transform: translateY(-1px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    #scrollToTopBtn {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
        font-size: 20px;
    }
}
</style>
        <div class="testimonial-grid">
            @forelse($testimonialCards ?? [] as $card)
            <div class="testimonial-card">
                <p>"{{ $card->content }}"</p>
                <div class="testimonial-author">
                    <strong>{{ $card->title }}</strong>
                    <span>{{ $card->badge ?? 'Client' }}</span>
                </div>
            </div>
            @empty
            <div class="testimonial-card">
                <p>"SUCCEZZ Drivers Agency has completely transformed how we manage transportation. The drivers are professional, punctual, and very respectful. Booking is simple and reliable every time."</p>
                <div class="testimonial-author">
                    <strong>Michael A.</strong>
                    <span>Business Owner</span>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"I used their daily sales service and it’s very flexible and convenient. The process is smooth, and the support team is always helpful. I highly recommend them."</p>
                <div class="testimonial-author">
                    <strong>Sandra K.</strong>
                    <span>Entrepreneur</span>
                </div>
            </div>
            <div class="testimonial-card">
                <p>"Everything from vehicle quality to driver professionalism is top-notch. SUCCEZZ truly delivers safety and reliability."</p>
                <div class="testimonial-author">
                    <strong>Daniel T.</strong>
                    <span>Private Client</span>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section-accent" id="services">
    <div class="section-inner">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Professional transportation solutions tailored to your needs</p>
        </div>

        <div class="services-grid">
            <!-- COMFI - TROSKI Service -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-route"></i>
                </div>
                <h3 class="service-title">COMFI - TROSKI</h3>
                <p class="service-preview">Book professional drivers for safe, on-demand trips.</p>
                <div class="service-hover-content">
                    <p class="service-description">Find experienced drivers immediately and schedule a ride with confidence.</p>
                    <a href="{{ route('book.index') }}" class="btn btn-service">Browse Services</a>
                </div>
            </div>

            <!-- SALES TO OWN Service -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="service-title">SALES TO OWN</h3>
                <p class="service-preview">Explore vehicle ownership opportunities and support plans.</p>
                <div class="service-hover-content">
                    <p class="service-description">View recruitment opportunities for vehicle owners and apply for the right role.</p>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-service">View Opportunities</a>
                </div>
            </div>

            <!-- CONTRACT DRIVERS Service -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h3 class="service-title">CONTRACT DRIVERS</h3>
                <p class="service-preview">Apply for contract driving roles with stable pay.</p>
                <div class="service-hover-content">
                    <p class="service-description">Find contract work and connect with employers offering steady driving jobs.</p>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-service">Explore Roles</a>
                </div>
            </div>

            <!-- DAILY SALES SERVICES Service -->
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <h3 class="service-title">DAILY SALES SERVICES</h3>
                <p class="service-preview">Find daily sales-driving work without vehicle ownership.</p>
                <div class="service-hover-content">
                    <p class="service-description">Explore opportunities for short-term daily earning assignments.</p>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-service">Browse Opportunities</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Driver Policy Section -->
<section class="section-light">
    <div class="section-inner">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 class="section-title">Driver Code of Conduct & Operational Policy</h2>
            <p class="section-subtitle">Professional standards and expectations for all SUCCEZZ drivers</p>
        </div>

        <div class="policy-grid">
            <!-- Purpose of the Policy -->
            <div class="policy-card">
                <div class="policy-header">
                    <i class="fas fa-bullseye policy-icon"></i>
                    <h3>Purpose of the Policy</h3>
                </div>
                <p>This policy establishes the professional standards, responsibilities, and behavioral expectations required of all drivers representing SUCCEZZ Drivers Agency. It ensures safety, reliability, and professionalism in all service engagements.</p>
            </div>

            <!-- Professional Conduct -->
            <div class="policy-card">
                <div class="policy-header">
                    <i class="fas fa-user-tie policy-icon"></i>
                    <h3>Professional Conduct</h3>
                </div>
                <ul class="policy-list">
                    <li>Maintain respectful and professional behavior at all times</li>
                    <li>Dress neatly and appropriately</li>
                    <li>Follow client instructions within legal limits</li>
                    <li>Avoid arguments or inappropriate discussions</li>
                    <li>Do not discuss sensitive or personal matters while on duty</li>
                </ul>
            </div>

            <!-- Safety and Driving Standards -->
            <div class="policy-card">
                <div class="policy-header">
                    <i class="fas fa-shield-alt policy-icon"></i>
                    <h3>Safety and Driving Standards</h3>
                </div>
                <ul class="policy-list">
                    <li>Obey all traffic laws</li>
                    <li>Drive responsibly and defensively</li>
                    <li>Never drive under the influence</li>
                    <li>Perform vehicle safety checks before trips</li>
                    <li>Report faults immediately</li>
                </ul>
            </div>

            <!-- Confidentiality and Trust -->
            <div class="policy-card">
                <div class="policy-header">
                    <i class="fas fa-lock policy-icon"></i>
                    <h3>Confidentiality and Trust</h3>
                </div>
                <ul class="policy-list">
                    <li>Keep all client information confidential</li>
                    <li>Do not share client details or routes</li>
                    <li>Avoid recording or photographing clients</li>
                    <li>Respect privacy at all times</li>
                    <li>Violations may lead to termination</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="section-primary">
    <div class="section-inner">
        <div class="row g-5">
            <!-- Vision Column -->
            <div class="col-lg-6">
                <div class="mission-vision-card">
                    <div class="mv-header">
                        <i class="fas fa-eye mv-icon"></i>
                        <h3>Our Vision</h3>
                    </div>
                    <p class="mv-text">To be the leading provider of premium driver services, renowned for our reliability and unparalleled expertise, ensuring peace of mind and superior experiences for all our clients.</p>
                </div>
            </div>

            <!-- Mission Column -->
            <div class="col-lg-6">
                <div class="mission-vision-card">
                    <div class="mv-header">
                        <i class="fas fa-target mv-icon"></i>
                        <h3>Our Mission</h3>
                    </div>
                    <p class="mv-text">To deliver exceptional transportation solutions by providing highly skilled, trustworthy, and experienced drivers who prioritize safety, reliability, and customer satisfaction.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section-light">
    <div class="section-inner">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title text-start">About SUCCEZZ Drivers Agency</h2>
                <p class="lead mb-4">Humanity for Others</p>
                <p class="mb-4">SUCCEZZ Drivers Agency is dedicated to connecting transportation businesses with reliable, professional drivers and vehicles. We believe in creating opportunities that benefit everyone in the transportation ecosystem.</p>

                <div class="about-features">
                    <div class="feature-item mb-3">
                        <i class="fas fa-shield-alt text-primary me-3"></i>
                        <div>
                            <h5 class="mb-1">Trusted & Verified</h5>
                            <p class="mb-0 text-muted">All our drivers undergo thorough background checks and license verification.</p>
                        </div>
                    </div>
                    <div class="feature-item mb-3">
                        <i class="fas fa-clock text-primary me-3"></i>
                        <div>
                            <h5 class="mb-1">Reliable Service</h5>
                            <p class="mb-0 text-muted">Punctual, professional service you can count on for your transportation needs.</p>
                        </div>
                    </div>
                    <div class="feature-item mb-3">
                        <i class="fas fa-handshake text-primary me-3"></i>
                        <div>
                            <h5 class="mb-1">Partnership Focus</h5>
                            <p class="mb-0 text-muted">Building lasting relationships between drivers, vehicle owners, and businesses.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1696634523711-d8ca4758cccd?auto=format&fit=crop&w=1200&q=85" alt="A professional driver and passenger riding together in a car" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section-primary contact-section">
    <div class="section-inner">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 class="section-title">Contact Us</h2>
            <p class="section-subtitle">Get in touch with our team for inquiries and support</p>
        </div>

        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-6">
                <div class="contact-form-card">
                    <h3 class="mb-4">Send us a Message</h3>
                    <form id="contactForm" action="{{ route('contact.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="contactName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="contactName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactEmail" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="contactEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactMessage" class="form-label">Message *</label>
                            <textarea class="form-control" id="contactMessage" name="message" rows="5" required placeholder="Tell us how we can help you..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-6">
                <div class="contact-info">
                    <h3 class="mb-4">Get in Touch</h3>
                    <p class="mb-4">We're here to help with all your transportation and recruitment needs. Reach out to us through any of the channels below.</p>

                    <div class="contact-details">
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-content">
                                <h5>Phone</h5>
                                <p class="mb-0"><a href="tel:+233500730901" class="text-decoration-none text-white hover-primary">+233 50 073 0901</a></p>
                                <p class="mb-0"><a href="tel:+233555962723" class="text-decoration-none text-white hover-primary">+233 55 596 2723</a></p>
                            </div>
                        </div>

                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-content">
                                <h5>Email</h5>
                                <p class="mb-0"><a href="mailto:info@succezzdriversagency.com" class="text-decoration-none text-white hover-primary">info@succezzdriversagency.com</a></p>
                                <p class="mb-0"><a href="mailto:support@succezzdriversagency.com" class="text-decoration-none text-white hover-primary">support@succezzdriversagency.com</a></p>
                            </div>
                        </div>

                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-content">
                                <h5>Office Location</h5>
                                <p class="mb-0">89 Kojo Thompson Rd, GA-074-8642<br>Accra, Ghana</p>
                                <a href="https://maps.app.goo.gl/AqaeBfgDqEpS1HBU6,+Accra,+Ghana" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fas fa-external-link-alt me-1"></i>View on Google Maps
                                </a>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-content">
                                <h5>Business Hours</h5>
                                <p class="mb-0">Monday - Friday: 8:00 AM - 6:00 PM</p>
                                <p class="mb-0">Saturday: 9:00 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section-light">
    <div class="section-inner">
        <div class="text-center mb-5">
            <h2 class="section-title">Find Our Location</h2>
            <p class="section-subtitle">Visit us at our office in Accra, Ghana</p>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.9734889999997!2d-0.186964084682623!3d5.603716995896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdc6b5b5b5b5b5b5b%3A0xfdc6b5b5b5b5b5b5b!2sAccra%2C%20Ghana!5e0!3m2!1sen!2s!4v1620000000000!5m2!1sen!2s"
                        width="100%"
                        height="400"
                        style="border:0; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted mb-2">89 Kojo Thompson Rd, GA-074-8642, Accra, Ghana</p>
                    <a href="https://maps.app.goo.gl/AqaeBfgDqEpS1HBU6" target="_blank" class="btn btn-primary">
                        <i class="fas fa-directions me-2"></i>Get Directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="modal-title mb-3" id="successModalLabel">Message Sent Successfully!</h4>
                <p class="text-muted mb-4">Thank you for contacting us. We've received your message and will get back to you as soon as possible.</p>
                <button type="button" class="btn btn-primary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Contact Form Handling
document.getElementById('contactForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    submitBtn.disabled = true;

    // Let the form submit naturally - the controller will handle the redirect
    // No preventDefault here, so the form will POST to the server
});

function showSuccessMessage(message) {
    // Remove existing success message
    const existingAlert = document.querySelector('.alert-success');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Create new success message
    const alert = document.createElement('div');
    alert.className = 'alert alert-success fade-in';
    alert.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;

    // Insert after contact form
    const contactForm = document.getElementById('contactForm');
    contactForm.parentNode.insertBefore(alert, contactForm.nextSibling);

    // Auto hide after 5 seconds
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

// Add fade-in animation to sections on scroll
function handleScrollAnimations() {
    const sections = document.querySelectorAll('.services-section, .about-section, .contact-section');

    sections.forEach(section => {
        const rect = section.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight - 100;

        if (isVisible && !section.classList.contains('fade-in')) {
            section.classList.add('fade-in');
        }
    });
}

// Initialize animations
document.addEventListener('DOMContentLoaded', function() {
    handleScrollAnimations();

    // Show success modal from session flash
    @if(session('success'))
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();

        // Reset form after modal is shown
        document.getElementById('contactForm').reset();
    @endif
});

window.addEventListener('scroll', handleScrollAnimations);

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<!-- Scroll to Top Button -->
<button id="scrollToTopBtn" title="Go to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Scroll to Top Button Functionality
const scrollToTopBtn = document.getElementById('scrollToTopBtn');

// Show/hide button based on scroll position
window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.add('show');
    } else {
        scrollToTopBtn.classList.remove('show');
    }
});

// Smooth scroll to top when button is clicked
scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

<script>
const audienceCarousel = document.getElementById('audienceCarousel');

if (audienceCarousel) {
    const audienceTrack = document.getElementById('audienceTrack');
    const audienceSlides = [...audienceTrack.querySelectorAll('.audience-slide')];
    const audienceStatus = document.getElementById('audienceCarouselStatus');
    const audienceDots = [...document.querySelectorAll('.audience-carousel-dot')];
    const audiencePrevButton = document.querySelector('.audience-carousel-arrow[data-direction="prev"]');
    const audienceNextButton = document.querySelector('.audience-carousel-arrow[data-direction="next"]');
    const audienceToggleButton = document.getElementById('audienceCarouselToggle');
    const reducedMotionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let activeAudienceSlide = 0;
    let touchStartX = null;
    let rotationTimer = null;
    let userPausedRotation = reducedMotionPreference.matches;
    let focusInCarousel = false;

    function updateAudienceDots() {
        audienceDots.forEach((dot, index) => {
            const isCurrent = index === activeAudienceSlide;
            dot.setAttribute('aria-current', String(isCurrent));
            dot.setAttribute('aria-label', `Show slide ${index + 1}`);
        });
    }

    function showAudienceSlide(index) {
        activeAudienceSlide = (index + audienceSlides.length) % audienceSlides.length;
        audienceTrack.style.transform = `translateX(-${activeAudienceSlide * 100}%)`;

        audienceSlides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeAudienceSlide;
            slide.inert = !isActive;
            slide.setAttribute('aria-hidden', String(!isActive));
        });

        audienceStatus.textContent = audienceSlides[activeAudienceSlide].getAttribute('aria-label');
        updateAudienceDots();
    }

    function updateRotation() {
        window.clearTimeout(rotationTimer);
        rotationTimer = null;

        const shouldRotate = !userPausedRotation
            && !focusInCarousel
            && !document.hidden
            && !reducedMotionPreference.matches;

        if (shouldRotate) {
            rotationTimer = window.setTimeout(() => {
                showAudienceSlide(activeAudienceSlide + 1);
                updateRotation();
            }, 6500);
        }
    }

    function moveToAudienceSlide(index) {
        showAudienceSlide(index);
        updateRotation();
    }

    audiencePrevButton.addEventListener('click', () => moveToAudienceSlide(activeAudienceSlide - 1));
    audienceNextButton.addEventListener('click', () => moveToAudienceSlide(activeAudienceSlide + 1));
    audienceDots.forEach((dot, index) => {
        dot.addEventListener('click', () => moveToAudienceSlide(index));
    });
    audienceToggleButton.addEventListener('click', () => {
        userPausedRotation = !userPausedRotation;
        audienceToggleButton.setAttribute('aria-label', userPausedRotation ? 'Resume automatic slides' : 'Pause automatic slides');
        audienceToggleButton.setAttribute('aria-pressed', String(userPausedRotation));
        audienceToggleButton.textContent = userPausedRotation ? 'Resume automatic slides' : 'Pause automatic slides';
        updateRotation();
    });

    audienceCarousel.addEventListener('focusin', () => {
        focusInCarousel = true;
        updateRotation();
    });
    audienceCarousel.addEventListener('focusout', event => {
        if (!audienceCarousel.contains(event.relatedTarget)) {
            focusInCarousel = false;
            updateRotation();
        }
    });
    document.addEventListener('visibilitychange', updateRotation);
    reducedMotionPreference.addEventListener('change', event => {
        userPausedRotation = event.matches;
        updateRotation();
    });

    audienceCarousel.addEventListener('keydown', event => {
        if (event.target !== audienceCarousel) return;

        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            moveToAudienceSlide(activeAudienceSlide + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });

    const audienceViewport = audienceCarousel.querySelector('.audience-carousel-viewport');
    audienceViewport.addEventListener('pointerdown', event => {
        touchStartX = event.pointerType === 'touch' ? event.clientX : null;
    });
    audienceViewport.addEventListener('pointerup', event => {
        if (touchStartX === null) return;

        const swipeDistance = event.clientX - touchStartX;
        touchStartX = null;

        if (Math.abs(swipeDistance) > 50) {
            moveToAudienceSlide(activeAudienceSlide + (swipeDistance < 0 ? 1 : -1));
        }
    });
    audienceViewport.addEventListener('pointercancel', () => {
        touchStartX = null;
    });

    showAudienceSlide(1);
    updateRotation();
}
</script>

@endsection

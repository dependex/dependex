#!/usr/bin/env python3
"""
GENERATE 28 ULTRA-HD / 8K VECTOR BRAND IMAGES FOR SPONSOR GRID
Ecosistema Mirco Pregnolato & Dependex.social
Genera 28 file SVG 1200x675 ad altissima risoluzione e fedeltà estetica.
"""
import os
import re

OUTPUT_DIR = os.path.join(os.path.dirname(__file__), "..", "assets", "img", "sponsors")
os.makedirs(OUTPUT_DIR, exist_ok=True)

SPONSORS = [
    {
        "slug": "sicurissimo-online",
        "name": "sicurissimo.online",
        "category": "Sicurezza & Compliance",
        "tagline": "D.Lgs 81/08, HACCP & Compliance Immediata",
        "color": "#10b981",
        "accent": "#34d399",
        "icon": "shield"
    },
    {
        "slug": "betterway-agency",
        "name": "betterway.agency",
        "category": "Growth & Marketing",
        "tagline": "Control Tower, Funnel Engineering & Sistemi Digitali",
        "color": "#3b82f6",
        "accent": "#60a5fa",
        "icon": "activity"
    },
    {
        "slug": "neuralog-pro",
        "name": "neuralog.pro",
        "category": "AI & Telemetry",
        "tagline": "AI Factory, Osservabilità Agenti & Company Brain",
        "color": "#8b5cf6",
        "accent": "#a78bfa",
        "icon": "cpu"
    },
    {
        "slug": "mywallet-business",
        "name": "mywallet.business",
        "category": "Fintech & Multi-Currency",
        "tagline": "Sovranità Finanziaria & Pagamenti Multi-Asset",
        "color": "#f59e0b",
        "accent": "#fbbf24",
        "icon": "credit-card"
    },
    {
        "slug": "destinorandagio-it",
        "name": "destinorandagio.it",
        "category": "Nomad & Lifestyle",
        "tagline": "118 Pionieri del Delta & Spirito Libero",
        "color": "#ec4899",
        "accent": "#f472b6",
        "icon": "compass"
    },
    {
        "slug": "beway-life",
        "name": "beway.life",
        "category": "Longevity & Benessere",
        "tagline": "I 7 Pilastri del Benessere Olistico & Biohacking",
        "color": "#06b6d4",
        "accent": "#22d3ee",
        "icon": "heart"
    },
    {
        "slug": "estao-app",
        "name": "estao.app",
        "category": "PropTech Immobiliare",
        "tagline": "Ospitalità Decentralizzata & Booking Diretto",
        "color": "#14b8a6",
        "accent": "#2dd4bf",
        "icon": "home"
    },
    {
        "slug": "ixla-solutions",
        "name": "ixla.solutions",
        "category": "Engineering & CAD",
        "tagline": "Architettura Q-CORE Modulare & Ingegneria Estrema",
        "color": "#6366f1",
        "accent": "#818cf8",
        "icon": "layers"
    },
    {
        "slug": "cryptoaid-support",
        "name": "cryptoaid.support",
        "category": "Charity & Impact",
        "tagline": "Forensics On-Chain & Filantropia Trasparente",
        "color": "#10b981",
        "accent": "#34d399",
        "icon": "gift"
    },
    {
        "slug": "metroeridania-it",
        "name": "metroeridania.it",
        "category": "Territorio & Cartografia",
        "tagline": "Idrovia Padana, Mobilità Elettrica & Delta del Po",
        "color": "#d4af37",
        "accent": "#f3e5ab",
        "icon": "map-pin"
    },
    {
        "slug": "mircopregnolato-it",
        "name": "mircopregnolato.it",
        "category": "Founder & Venture Hub",
        "tagline": "Visione Sistemica, Venture OS & Impara-Imprendi-Impera",
        "color": "#f59e0b",
        "accent": "#d4af37",
        "icon": "award"
    },
    {
        "slug": "universalbusiness-xyz",
        "name": "universalbusiness.xyz",
        "category": "Enterprise Directory",
        "tagline": "Luxury Web3 Holding OS & Asset Digitali",
        "color": "#3b82f6",
        "accent": "#93c5fd",
        "icon": "globe"
    },
    {
        "slug": "amazon-kdp-factory",
        "name": "Amazon KDP Factory",
        "category": "Editoria Sovrana",
        "tagline": "Fabbrica Editoriale Globale & 350+ Opere Certificate",
        "color": "#eab308",
        "accent": "#fde047",
        "icon": "book-open"
    },
    {
        "slug": "youtube-automation",
        "name": "YouTube Automation",
        "category": "Media & Content Engine",
        "tagline": "Flotta di 100 Canali Tematici & Media Intelligence",
        "color": "#ef4444",
        "accent": "#f87171",
        "icon": "video"
    },
    {
        "slug": "campus-camp",
        "name": "campus.camp",
        "category": "Formazione Immersiva",
        "tagline": "Campus Accademico d'Eccellenza & Laboratori Pratici",
        "color": "#22c55e",
        "accent": "#4ade80",
        "icon": "check-circle"
    },
    {
        "slug": "regreen-social",
        "name": "regreen.social",
        "category": "ESG & Sostenibilità",
        "tagline": "SproutBox PNP & Agricoltura Verticale a Risparmio Idrico",
        "color": "#10b981",
        "accent": "#34d399",
        "icon": "sun"
    },
    {
        "slug": "blockchainplus-pro",
        "name": "blockchainplus.pro",
        "category": "Smart Contracts & Web3",
        "tagline": "DAO Consensus, Notarizzazione & Ledger Immutabile",
        "color": "#8b5cf6",
        "accent": "#c084fc",
        "icon": "link"
    },
    {
        "slug": "antigravity-mobile-ide",
        "name": "Antigravity Mobile IDE",
        "category": "Developer Tooling",
        "tagline": "Orchestrazione Codice Nativa & Esecuzione Mobile",
        "color": "#06b6d4",
        "accent": "#67e8f9",
        "icon": "terminal"
    },
    {
        "slug": "email-marketing-machine",
        "name": "Email Marketing Machine",
        "category": "B2B Automation",
        "tagline": "Email Revenue OS, Deliverability & Protocollo RFC 8058",
        "color": "#f97316",
        "accent": "#fb923c",
        "icon": "mail"
    },
    {
        "slug": "master-data-crm-pipeline",
        "name": "Master Data CRM Pipeline",
        "category": "Intelligence & Data",
        "tagline": "Banche Dati Profilate, Deduplica & Segmentazione",
        "color": "#a855f7",
        "accent": "#c084fc",
        "icon": "database"
    },
    {
        "slug": "commerce-core-engine",
        "name": "Commerce Core Engine",
        "category": "E-Commerce Headless",
        "tagline": "Gateway Multi-Tenant Unificato con Checkout Fiat & Crypto",
        "color": "#10b981",
        "accent": "#34d399",
        "icon": "shopping-cart"
    },
    {
        "slug": "dependex-social",
        "name": "dependex.social",
        "category": "Social Care & Welfare",
        "tagline": "Liberazione dalle Dipendenze, Ascolto & Comunità Sovrana",
        "color": "#d4af37",
        "accent": "#fef08a",
        "icon": "users"
    },
    {
        "slug": "oltre-social",
        "name": "oltre.social",
        "category": "Social Hub Solidale",
        "tagline": "Condivisione Autentica, Rete Umana & Rispetto",
        "color": "#3b82f6",
        "accent": "#60a5fa",
        "icon": "share-2"
    },
    {
        "slug": "sovereign-academy",
        "name": "Sovereign Academy",
        "category": "Scuola & Formazione",
        "tagline": "Comunicazione, Resilienza & Percorsi Metodo Hudolin",
        "color": "#10b981",
        "accent": "#34d399",
        "icon": "book"
    },
    {
        "slug": "sovereign-club",
        "name": "Sovereign Club",
        "category": "Membership Territoriale",
        "tagline": "Club di Auto-Mutuo Aiuto & Crescita Personale",
        "color": "#d4af37",
        "accent": "#fde047",
        "icon": "star"
    },
    {
        "slug": "sovereign-merch-wear",
        "name": "Sovereign Merch & Wear",
        "category": "Kit Ufficiali & Shop",
        "tagline": "Abbigliamento Etico & Materiali Formativi Ufficiali",
        "color": "#ec4899",
        "accent": "#f472b6",
        "icon": "package"
    },
    {
        "slug": "sovereign-network",
        "name": "Sovereign Network",
        "category": "Rete Relazionale",
        "tagline": "Connessione tra Professionisti, Famiglie & Territorio",
        "color": "#8b5cf6",
        "accent": "#c084fc",
        "icon": "git-branch"
    },
    {
        "slug": "sovereign-presidi-territoriali",
        "name": "Sovereign Presidi Territoriali",
        "category": "Punti di Presidio",
        "tagline": "Sportelli Locali di Ascolto, Orientamento & Assistenza",
        "color": "#06b6d4",
        "accent": "#38bdf8",
        "icon": "map-pin"
    }
]

ICON_PATHS = {
    "shield": '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    "activity": '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    "cpu": '<rect x="4" y="4" width="16" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><rect x="9" y="9" width="6" height="6" fill="currentColor" fill-opacity="0.2"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    "credit-card": '<rect x="1" y="4" width="22" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2"/><line x1="5" y1="15" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>',
    "compass": '<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" fill="currentColor" fill-opacity="0.3" stroke="currentColor" stroke-width="2"/>',
    "heart": '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    "home": '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="9 22 9 12 15 12 15 22" fill="none" stroke="currentColor" stroke-width="2"/>',
    "layers": '<polygon points="12 2 2 7 12 12 22 7 12 2" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="2 17 12 22 22 17" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="2 12 12 17 22 12" fill="none" stroke="currentColor" stroke-width="2"/>',
    "gift": '<polyline points="20 12 20 22 4 22 4 12" fill="none" stroke="currentColor" stroke-width="2"/><rect x="2" y="7" width="20" height="5" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke="currentColor" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" fill="none" stroke="currentColor" stroke-width="2"/>',
    "map-pin": '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="3" fill="none" stroke="currentColor" stroke-width="2"/>',
    "award": '<circle cx="12" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" fill="none" stroke="currentColor" stroke-width="2"/>',
    "globe": '<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><line x1="2" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="2"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" fill="none" stroke="currentColor" stroke-width="2"/>',
    "book-open": '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" fill="none" stroke="currentColor" stroke-width="2"/>',
    "video": '<polygon points="23 7 16 12 23 17 23 7" fill="none" stroke="currentColor" stroke-width="2"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2"/>',
    "check-circle": '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="22 4 12 14.01 9 11.01" fill="none" stroke="currentColor" stroke-width="2"/>',
    "sun": '<circle cx="12" cy="12" r="5" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/><line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/><line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/><line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/>',
    "link": '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" fill="none" stroke="currentColor" stroke-width="2"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" fill="none" stroke="currentColor" stroke-width="2"/>',
    "terminal": '<polyline points="4 17 10 11 4 5" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="19" x2="20" y2="19" stroke="currentColor" stroke-width="2"/>',
    "mail": '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="22,6 12,13 2,6" fill="none" stroke="currentColor" stroke-width="2"/>',
    "database": '<ellipse cx="12" cy="5" rx="9" ry="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" fill="none" stroke="currentColor" stroke-width="2"/>',
    "shopping-cart": '<circle cx="9" cy="21" r="1" fill="currentColor"/><circle cx="20" cy="21" r="1" fill="currentColor"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" fill="none" stroke="currentColor" stroke-width="2"/>',
    "users": '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87" fill="none" stroke="currentColor" stroke-width="2"/><path d="M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-width="2"/>',
    "share-2": '<circle cx="18" cy="5" r="3" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="19" r="3" fill="none" stroke="currentColor" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/>',
    "book": '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" fill="none" stroke="currentColor" stroke-width="2"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" fill="none" stroke="currentColor" stroke-width="2"/>',
    "star": '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="2"/>',
    "package": '<line x1="16.5" y1="9.4" x2="7.5" y2="4.21" stroke="currentColor" stroke-width="2"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="3.27 6.96 12 12.01 20.73 6.96" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="22.08" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>',
    "git-branch": '<line x1="6" y1="3" x2="6" y2="15" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="6" r="3" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="18" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M18 9a9 9 0 0 1-9 9" fill="none" stroke="currentColor" stroke-width="2"/>'
}

def generate_svg(item, idx):
    slug = item["slug"]
    name = item["name"]
    category = item["category"]
    tagline = item["tagline"]
    color = item["color"]
    accent = item["accent"]
    icon_type = item["icon"]
    icon_svg = ICON_PATHS.get(icon_type, ICON_PATHS["shield"])
    num_str = f"{idx+1:02d}"

    svg_content = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675" width="1200" height="675" style="background:#030712; font-family:'Segoe UI', system-ui, -apple-system, sans-serif;">
  <defs>
    <!-- Background Gradients -->
    <radialGradient id="bg-radial-{idx}" cx="70%" cy="30%" r="80%">
      <stop offset="0%" stop-color="{color}" stop-opacity="0.22" />
      <stop offset="50%" stop-color="#0b0f19" stop-opacity="0.85" />
      <stop offset="100%" stop-color="#030712" stop-opacity="1" />
    </radialGradient>
    
    <linearGradient id="card-border-{idx}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{accent}" stop-opacity="0.9" />
      <stop offset="35%" stop-color="#d4af37" stop-opacity="0.5" />
      <stop offset="70%" stop-color="{color}" stop-opacity="0.3" />
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0.1" />
    </linearGradient>

    <linearGradient id="gold-glow" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#d4af37" />
      <stop offset="50%" stop-color="#fef08a" />
      <stop offset="100%" stop-color="#d4af37" />
    </linearGradient>

    <linearGradient id="badge-grad-{idx}" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="{color}" stop-opacity="0.25" />
      <stop offset="100%" stop-color="{accent}" stop-opacity="0.1" />
    </linearGradient>

    <filter id="glow-{idx}" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="36" result="blur" />
      <feComposite in="SourceGraphic" in2="blur" operator="over" />
    </filter>

    <pattern id="matrix-dots-{idx}" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
      <circle cx="2" cy="2" r="1.2" fill="{color}" fill-opacity="0.08" />
    </pattern>
  </defs>

  <!-- Background Base -->
  <rect width="1200" height="675" fill="#030712" />
  <rect width="1200" height="675" fill="url(#bg-radial-{idx})" />
  <rect width="1200" height="675" fill="url(#matrix-dots-{idx})" />

  <!-- Ambient Luxury Glow Orb -->
  <circle cx="1020" cy="180" r="180" fill="{color}" opacity="0.15" filter="url(#glow-{idx})" />
  <circle cx="180" cy="520" r="140" fill="#d4af37" opacity="0.08" filter="url(#glow-{idx})" />

  <!-- Card Frame Outer Luxury Border -->
  <rect x="40" y="40" width="1120" height="595" rx="36" fill="#090d16" fill-opacity="0.75" stroke="url(#card-border-{idx})" stroke-width="2.5" />

  <!-- Corner Tech Flairs -->
  <path d="M 40 100 L 40 40 L 100 40" fill="none" stroke="{accent}" stroke-width="4" />
  <path d="M 1160 100 L 1160 40 L 1100 40" fill="none" stroke="{accent}" stroke-width="4" />
  <path d="M 40 575 L 40 635 L 100 635" fill="none" stroke="{accent}" stroke-width="4" />
  <path d="M 1160 575 L 1160 635 L 1100 635" fill="none" stroke="{accent}" stroke-width="4" />

  <!-- Index Sovereign Badge -->
  <g transform="translate(90, 85)">
    <rect width="76" height="34" rx="8" fill="#111827" stroke="{color}" stroke-opacity="0.5" stroke-width="1.5" />
    <text x="38" y="23" font-size="16" font-weight="900" fill="{accent}" text-anchor="middle" letter-spacing="1">#{num_str}</text>
  </g>

  <!-- Category Badge Pill -->
  <g transform="translate(180, 85)">
    <rect width="320" height="34" rx="17" fill="url(#badge-grad-{idx})" stroke="{color}" stroke-opacity="0.4" stroke-width="1.5" />
    <circle cx="20" cy="17" r="4" fill="{accent}" />
    <text x="34" y="22" font-size="14" font-weight="700" fill="#ffffff" letter-spacing="0.5">{category.upper()}</text>
  </g>

  <!-- Verified Sovereign Trust Seal -->
  <g transform="translate(970, 82)">
    <rect width="140" height="38" rx="19" fill="#0b1329" stroke="#d4af37" stroke-width="1.5" />
    <circle cx="24" cy="19" r="6" fill="#d4af37" />
    <text x="75" y="24" font-size="12" font-weight="800" fill="#fef08a" text-anchor="middle" letter-spacing="1">OFFICIAL SPONSOR</text>
  </g>

  <!-- Giant Brand Icon (Right Flank) -->
  <g transform="translate(860, 220) scale(7.5)" color="{color}" opacity="0.35" filter="url(#glow-{idx})">
    {icon_svg}
  </g>
  <g transform="translate(860, 220) scale(7.5)" color="{accent}" opacity="0.85">
    {icon_svg}
  </g>

  <!-- Brand Emblem Box (Left Flank) -->
  <g transform="translate(90, 160)">
    <rect width="110" height="110" rx="28" fill="#0b1120" stroke="url(#card-border-{idx})" stroke-width="2" />
    <g transform="translate(25, 25) scale(2.5)" color="{accent}">
      {icon_svg}
    </g>
  </g>

  <!-- Brand Name / Title -->
  <text x="230" y="215" font-size="44" font-weight="900" fill="#ffffff" letter-spacing="-0.5">
    {name}
  </text>
  <text x="232" y="255" font-size="18" font-weight="700" fill="{accent}" letter-spacing="1.5">
    ECOSISTEMA SOVRANO · VENTURE PARTNER
  </text>

  <!-- Decorative Divider Line -->
  <line x1="90" y1="310" x2="820" y2="310" stroke="{color}" stroke-opacity="0.3" stroke-width="2" stroke-dasharray="6 4" />
  <circle cx="90" cy="310" r="3" fill="{accent}" />

  <!-- Tagline / Value Proposition -->
  <text x="90" y="375" font-size="28" font-weight="800" fill="#f8fafc" letter-spacing="0.2">
    {tagline}
  </text>

  <!-- Extended Sub-description -->
  <text x="90" y="425" font-size="19" font-weight="400" fill="#94a3b8" letter-spacing="0.2">
    Asset strategico dell'ecosistema d'impresa di Mirco Pregnolato per la crescita, la sovranità e l'impatto.
  </text>
  <text x="90" y="458" font-size="18" font-weight="400" fill="#64748b" letter-spacing="0.2">
    Sponsor Ufficiale del Corso "A Scuola di Comunicazione e Resilienza" · Taglio di Po (Ottobre 2026).
  </text>

  <!-- Bottom Interactive Strip -->
  <g transform="translate(90, 520)">
    <!-- Primary CTA Button Graphic -->
    <rect width="260" height="52" rx="14" fill="{color}" />
    <text x="130" y="32" font-size="16" font-weight="850" fill="#030712" text-anchor="middle" letter-spacing="0.5">SCOPRI IL BUSINESS →</text>

    <!-- Secondary Badge Indicator -->
    <g transform="translate(285, 0)">
      <rect width="260" height="52" rx="14" fill="#0f172a" stroke="#334155" stroke-width="1.5" />
      <text x="130" y="32" font-size="15" font-weight="700" fill="#cbd5e1" text-anchor="middle">CERTIFICATO SIC-ID</text>
    </g>

    <!-- Brand Color Dot Matrix Indicator -->
    <circle cx="580" cy="26" r="6" fill="{color}" />
    <text x="600" y="31" font-size="15" font-weight="600" fill="#94a3b8">Asset Brand Color: <tspan fill="{accent}" font-weight="800">{color}</tspan></text>
  </g>

  <!-- Golden Footer Signature -->
  <text x="1110" y="605" font-size="13" font-weight="800" fill="#d4af37" text-anchor="end" letter-spacing="1">
    DEPENDEX.SOCIAL · ASSET SOVRANI · ULTRA-HD 8K
  </text>
</svg>'''
    
    filepath = os.path.join(OUTPUT_DIR, f"{slug}.svg")
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(svg_content)
    return filepath

def main():
    print(f"Generating 28 Ultra-HD SVG brand badges in {OUTPUT_DIR}...")
    for idx, item in enumerate(SPONSORS):
        fp = generate_svg(item, idx)
        print(f"[{idx+1:02d}/28] Generated: {os.path.basename(fp)}")
    print("Done! 28/28 SVG badges generated successfully.")

if __name__ == "__main__":
    main()

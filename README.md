# DEPENDEX — AL CLUB. COL CLUB.

[![CI](https://github.com/dependex/dependex/actions/workflows/ci.yml/badge.svg)](https://github.com/dependex/dependex/actions/workflows/ci.yml)
[![Deploy](https://github.com/dependex/dependex/actions/workflows/deploy.yml/badge.svg)](https://github.com/dependex/dependex/actions/workflows/deploy.yml)
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![PWA Ready](https://img.shields.io/badge/PWA-Ready-5A0FC8?logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)

> **Piattaforma digitale per Club Alcologici Territoriali (CAT)**  
> Metodo Hudolin · Ecosistema ACAT · Rete Globale

🌐 **IT** → [oltre.social](https://oltre.social)  
🌐 **GLOBAL** → [dependex.social](https://dependex.social)  
📧 [info@dependex.social](mailto:info@dependex.social)

---

## 🏗 Architettura

```
dependex.social/
├── index.php              # Landing PWA responsive 9:16 / 16:9
├── app.php                # Dashboard utente
├── bootstrap.php          # Core engine, auth, DB, routing
├── service-worker.js      # PWA offline-first
├── manifest.webmanifest   # PWA manifest
├── assets/
│   ├── css/               # Design system (verde/arancione brand)
│   ├── js/                # Client-side modules
│   └── img/               # SVG logos, favicons, assets
├── knowledge/             # Hudolin knowledge base
├── data/                  # SQLite DB, census data
├── locales/               # i18n (11 lingue)
└── modules/               # Feature modules
```

## ✨ Funzionalità

| Area | Moduli |
|------|--------|
| **Club Hub** | Gestione club, card, ranking, check-in giornaliero |
| **Network** | Mappa mondiale, albero gerarchico ACAT, explorer |
| **Academy** | Corsi Hudolin/SAT/Lifestyle, progressi, certificati |
| **Eventi** | Calendar, Event Factory, Graphic Studio |
| **Sobriety** | Daily access, milestone engine, diario |
| **DAO** | Forum, proposte, votazioni |
| **Finance** | Tesoreria, social impact, fundraising |
| **Cortex** | AI assistant, research intelligence |
| **Vault** | Document factory, stampa, archivio |
| **DRX Wallet** | Token Dialogo·Relazioni·eXperienza |

## 🚀 Quick Start

### Requisiti
- PHP 8.2+ con `pdo_sqlite`
- Server web (Apache/Nginx)
- HTTPS consigliato per PWA

### Installazione

```bash
# Clone
git clone https://github.com/dependex/dependex.git
cd dependex

# Configurazione
cp .env.example .env
# Modifica .env con le tue credenziali

# Permessi
chmod -R 775 data/ storage/

# Setup
# Apri /install.php nel browser
# Crea SUPERADMIN e salva il recovery code
```

### Deploy su hosting condiviso

```bash
# Upload via FTP a public_html/
# Oppure usa GitHub Actions (deploy automatico su push)
```

## 🔧 Configurazione

| Variabile | Descrizione |
|-----------|-------------|
| `DB_PATH` | Path al database SQLite |
| `APP_ENV` | `production` / `development` |
| `APP_URL` | URL base del sito |
| `MAIL_FROM` | Email mittente (`info@dependex.social`) |

## 📊 Dati

- **538** entità di rete censite
- **374** Club attivi
- **361** entità Italia (224 Club)
- **177** entità Globali (150 Club)
- Censimento incrementale V1 — estendibile regione per regione

### API

```
GET /api.php?action=network&scope=ITALY
GET /api.php?action=network&scope=GLOBAL
GET /api-world-map.php
GET /api-world-hierarchy.php
```

## 🔒 Sicurezza

- Autenticazione senza email: dispositivo fidato + recovery code + fallback admin
- SIC-ID universale Crockford + checksum
- Password hashing `bcrypt`
- CSRF protection
- Rate limiting
- Input sanitization
- `.env` e secrets esclusi dal repository

## 🌐 i18n

Scaffold multilingua: IT, EN, DE, FR, ES, PT, HR, SL, SR, PL, RU

## 📝 Licenza

[AGPL-3.0](LICENSE) — Il codice è libero, le derivazioni devono restare open source.

## 🤝 Contributing

Vedi [CONTRIBUTING.md](CONTRIBUTING.md) per le linee guida.

---

*DEPENDEX — AL CLUB. COL CLUB.*  
*Metodo Hudolin per un mondo libero dalle dipendenze.*

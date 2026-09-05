# DEPENDEX / OLTRE V5 — Integration Guide

## Supplied modules analysed
All 208+ non-Web3 module files were retained under `modules/`; Web3 is retained under `modules/deferred-web3` but is **not activated**.

## Universal hierarchy
WORLD > CONTINENT > COUNTRY/NATIONAL > REGION/STATE > PROVINCE/DISTRICT > TERRITORIAL/LOCAL ASSOCIATION > LOCAL CLUB

The original local hierarchy remains in `original_type`, while `network_level` is the universal renderer rank.

## SIC-ID
Every registry node has an ECO-SIC compatible identifier: `SIC-XXXXXXXX-XXXXXXXX-C`. Rank is NOT encoded in the identifier.

## Map
- `world-club-explorer.php`
- `api-world-map.php`
- `assets/js/dependex-world-map.js`
- 2D + rotating 3D globe
- search by Club/city/country/SIC-ID
- rank/country/status filters
- click POI -> rich club card

### Geocoding truthfulness
No external geocoder is available in the build environment. Until exact geocoding is run, POIs use deterministic country-distributed positions and carry `geo_accuracy=COUNTRY_DISTRIBUTED_ESTIMATE`. Textual address/city is not altered. This avoids inventing exact coordinates.

## Neuralog
Universal engine is copied to `modules/neuralog`. Host bridge injects the application's PDO, so it can live in the same DB using prefixed brain tables.

## Network visual
The provided Genesys placement tree is retained as source/reference. DEPENDEX organizational hierarchy is exposed through `api-world-hierarchy.php` and `world-network-tree.php`; this avoids mixing sponsor placement semantics with association hierarchy.

## Domains
- Italy: https://oltre.social
- International: https://dependex.social

## Database
SQLite integrity: ok
Registry stats: {"registry_nodes": 483, "local_clubs": 295, "countries": 36, "edges": 482}

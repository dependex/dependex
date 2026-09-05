# SECURITY

## Implementato nel core
- `password_hash()` / `password_verify()`
- session regeneration al login
- CSRF token
- prepared statements PDO
- RBAC + scope ACL
- audit log
- recovery password senza email tramite dispositivo fidato/recovery code/fallback admin
- blocco accesso diretto DB via `.htaccess`
- security headers base
- output escaping HTML
- idempotency DRX/check-in

## Prima del go-live
- MFA/passkey per admin elevati
- WAF/rate limiting server-side
- backup cifrati e test restore
- upload malware/type validation
- rotazione segreti
- log centralizzati
- penetration test
- revisione ACL per ogni ruolo/scope

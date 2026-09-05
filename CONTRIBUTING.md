# Contributing a DEPENDEX

Grazie per il tuo interesse nel contribuire a DEPENDEX! 🎉

## Come contribuire

### Segnalare un bug
1. Apri una [issue](https://github.com/dependex/dependex/issues/new?template=bug_report.md)
2. Descrivi il comportamento atteso vs quello osservato
3. Includi screenshot se possibile

### Proporre una funzionalità
1. Apri una [issue](https://github.com/dependex/dependex/issues/new?template=feature_request.md)
2. Descrivi il caso d'uso
3. Spiega il valore per la community

### Inviare codice

```bash
# Fork e clone
git clone https://github.com/TUO-USERNAME/dependex.git
cd dependex

# Crea un branch
git checkout -b feature/nome-funzionalita

# Sviluppa e testa
php -l *.php  # Verifica sintassi

# Commit con messaggio chiaro
git commit -m "feat: descrizione della modifica"

# Push e apri una Pull Request
git push origin feature/nome-funzionalita
```

## Convenzioni

### Commit messages
Usiamo [Conventional Commits](https://www.conventionalcommits.org/):
- `feat:` nuova funzionalità
- `fix:` correzione bug
- `docs:` documentazione
- `style:` formattazione (no logic change)
- `refactor:` refactoring
- `test:` aggiunta/modifica test
- `chore:` manutenzione

### Codice
- PHP 8.2+ con strict types
- Tab = 4 spazi
- Nomi variabili/funzioni in `snake_case`
- Classi in `PascalCase`
- Commenti in italiano o inglese

### Sicurezza
- **MAI** committare `.env`, API keys, password
- Usa `htmlspecialchars()` per output
- Prepared statements per SQL
- Valida sempre l'input utente

## Codice di condotta

Questo progetto segue il [Contributor Covenant](https://www.contributor-covenant.org/).
Comportamento rispettoso e inclusivo è obbligatorio.

## Licenza

Contribuendo, accetti che il tuo codice sia rilasciato sotto [AGPL-3.0](LICENSE).

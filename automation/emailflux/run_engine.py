#!/usr/bin/env python3
"""
UNIVERSAL EMAIL REVENUE OS — CLI & RUNTIME ENGINE
Comando universale per l'orchestrazione, valutazione flussi, scoring, dispatch e reporting.
Metodo Karpathy: Spec -> Verifier -> Environment
"""

import sys
import os
import argparse
import datetime
import json
from pathlib import Path

# Configurazione root
ROOT_DIR = Path(__file__).resolve().parent.parent.parent
sys.path.insert(0, str(ROOT_DIR))

from src.events.normalizer import EventNormalizer
from src.events.store import EventStore
from src.consent.ledger import ConsentLedger
from src.scoring.calculator import ScoringCalculator
from src.segmentation.engine import SegmentationEngine
from src.journeys.state_machine import LifecycleStateMachine
from src.compiler.flow_compiler import FlowCompiler
from src.renderer.mjml_compiler import TemplateRenderer
from src.compliance.guard import ComplianceGuard
from src.providers.adapter import EmailProviderAdapter
from src.dispatch.governor import DispatchGovernor
from src.reporting.kpi import KPIReporter
from src.optimizer.pr_generator import OptimizationEngine

DB_PATH = ROOT_DIR / "automation" / "emailflux" / "data" / "emailflux.db"

def cmd_status():
    print("=" * 65)
    print(" UNIVERSAL EMAIL REVENUE OS — STATO OPERATIVO")
    print("=" * 65)
    
    import sqlite3
    if not DB_PATH.exists():
        print(f"[!] Database {DB_PATH} non trovato.")
        return

    conn = sqlite3.connect(str(DB_PATH))
    conn.row_factory = sqlite3.Row
    
    total_contacts = conn.execute("SELECT COUNT(*) FROM ghl_contact").fetchone()[0]
    active_contacts = conn.execute("SELECT COUNT(*) FROM ghl_contact WHERE status = 'ATTIVO'").fetchone()[0]
    total_enrollments = conn.execute("SELECT COUNT(*) FROM ghl_workflow_enrollment WHERE stato = 'active'").fetchone()[0]
    
    events_count = 0
    try:
        events_count = conn.execute("SELECT COUNT(*) FROM event_log").fetchone()[0]
    except Exception:
        pass

    suppressed_count = 0
    try:
        suppressed_count = conn.execute("SELECT COUNT(*) FROM suppression_list").fetchone()[0]
    except Exception:
        pass

    compiler = FlowCompiler(flows_directory=ROOT_DIR / "flows")

    print(f"• Mittente Ufficiale: info@dependex.social (Hostinger SMTP SSL 465)")
    print(f"• Destinatario Prova: labomobile.lm@gmail.com")
    print(f"• Lead Totali nel DB: {total_contacts:,} (Attivi: {active_contacts:,})")
    print(f"• Iscrizioni Flussi Attive: {total_enrollments:,}")
    print(f"• Eventi Tracciati nel Funnel: {events_count:,}")
    print(f"• Suppression List (Unsub/Bounce): {suppressed_count}")
    print(f"• Flussi Dichiarativi Caricati: {len(compiler.loaded_flows)}")
    for fid, fdef in compiler.loaded_flows.items():
        print(f"   - [{fid}] {fdef.get('name', '')} ({len(fdef.get('steps', []))} steps)")
    print("=" * 65)

def cmd_test_send(recipient="labomobile.lm@gmail.com"):
    provider = EmailProviderAdapter()
    renderer = TemplateRenderer()
    guard = ComplianceGuard()

    print(f"[>] Inizializzazione test di consegna autenticato...")
    print(f"    Mittente: {provider.from_email}")
    print(f"    Destinatario: {recipient}")
    
    subject = "Conferma Attivazione Club Dependex: Credenziali e Accesso"
    body = """
    <h2>Benvenuto nel Club Dependex</h2>
    <p>La tua richiesta di accesso prioritario è stata confermata con successo.</p>
    <p>Questo test verifica l'integrazione completa con l'infrastruttura di automazione email, gli header RFC 8058 One-Click Unsubscribe e la deliverability su Hostinger SSL.</p>
    <p><a href="https://dependex.social/world-club-explorer.php" class="cta-btn">Accedi al Club Dependex</a></p>
    """
    
    html = renderer.render(body, {
        "subject": subject,
        "first_name": "Membro",
        "unsubscribe_url": f"https://dependex.social/email/unsubscribe.php?email={recipient}",
        "preferences_url": "https://dependex.social/email/preferences.php"
    })
    
    # Validazione di conformità
    ok, reason = guard.validate_dispatch(recipient, subject, html, bypass_quiet_hours=True)
    if not ok:
        print(f"[X] Invio bloccato dal Compliance Guard: {reason}")
        return False
        
    success, msg, msg_id = provider.send_email(recipient, subject, html, dry_run=False)
    if success:
        print(f"[V] ESITO: SUCCESSO! Email recapitata a {recipient}")
        print(f"    Message ID / Tracking: {msg_id}")
        return True
    else:
        print(f"[X] ERRORE CONSEGNA: {msg}")
        return False

def cmd_dispatch(batch_limit=10, dry_run=True):
    print(f"[>] Avvio Dispatch Governor (Batch Limit: {batch_limit}, Dry-Run: {dry_run})...")
    provider = EmailProviderAdapter()
    ledger = ConsentLedger(db_path=DB_PATH)
    guard = ComplianceGuard(suppression_checker=ledger)
    governor = DispatchGovernor(provider_adapter=provider, compliance_guard=guard, delay_between_sends=0.5)
    renderer = TemplateRenderer()
    
    import sqlite3
    conn = sqlite3.connect(str(DB_PATH))
    conn.row_factory = sqlite3.Row
    
    # Preleva contatti attivi pronti
    rows = conn.execute("""
        SELECT c.id, c.email, c.nome, c.flusso
        FROM ghl_contact c
        LEFT JOIN suppression_list s ON c.email = s.email
        WHERE c.status = 'ATTIVO' AND c.unsub = 0 AND s.email IS NULL
        LIMIT ?
    """, (batch_limit,)).fetchall()
    
    messages = []
    for r in rows:
        subj = "Nuove Risorse Esclusive Disponibili nel Club Dependex"
        body = f"""
        <h2>Gentile {r['nome'] or 'Membro'},</h2>
        <p>Abbiamo aggiornato gli asset e le strategie operative del Club Dependex per potenziare la tua autonomia e presenza di mercato.</p>
        <p><a href="https://dependex.social/club" class="cta-btn">Esplora la Vault del Club</a></p>
        """
        html = renderer.render(body, {
            "subject": subj,
            "first_name": r['nome'] or 'Membro',
            "unsubscribe_url": f"https://dependex.social/email/unsubscribe.php?email={r['email']}",
            "preferences_url": "https://dependex.social/email/preferences.php"
        })
        messages.append({
            "recipient": r['email'],
            "subject": subj,
            "html_content": html,
            "is_transactional": False
        })
        
    results = governor.dispatch_batch(messages, dry_run=dry_run)
    print(f"[V] Dispatch completato!")
    print(f"    Totale: {results['total']}, Inviati: {results['sent']}, Bloccati: {results['blocked']}, Falliti: {results['failed']}")
    return results

def main():
    parser = argparse.ArgumentParser(description="Universal Email Revenue OS Engine")
    parser.add_argument("--status", action="store_true", help="Mostra lo stato operativo del sistema")
    parser.add_argument("--test", action="store_true", help="Invia email di prova autenticata verso labomobile.lm@gmail.com")
    parser.add_argument("--dispatch", action="store_true", help="Esegue un batch di invio")
    parser.add_argument("--dry-run", action="store_true", help="Esegue il dispatch in simulazione senza invio effettivo")
    parser.add_argument("--limit", type=int, default=10, help="Limite di contatti per il batch")
    
    args = parser.parse_args()
    
    if args.test:
        cmd_test_send()
    elif args.dispatch:
        cmd_dispatch(batch_limit=args.limit, dry_run=args.dry_run)
    else:
        cmd_status()

if __name__ == "__main__":
    main()

"""
import_leads.py — Ingestione e normalizzazione dei 7.445 contatti master
nel database SQLite di Dependex.social.
"""
import csv
import sys
from pathlib import Path
from config import LEADS_CSV
from db import get_connection, get_or_create_sub_account

def import_all():
    csv_path = Path(LEADS_CSV)
    if not csv_path.exists():
        print(f"[ERRORE] File CSV non trovato in {csv_path}")
        return 0

    conn = get_connection()
    sub_id = get_or_create_sub_account(conn)
    cur = conn.cursor()

    count_inserted = 0
    count_updated = 0
    count_skipped = 0

    print(f"[*] Avvio importazione lead da: {csv_path}")

    with open(csv_path, mode="r", encoding="utf-8", errors="replace") as f:
        reader = csv.DictReader(f)
        for row in reader:
            email = (row.get("EMAIL") or "").strip().lower()
            if not email or "@" not in email:
                count_skipped += 1
                continue

            nome = (row.get("FIRSTNAME") or "").strip()
            if nome.lower() in ("null", "none", ""):
                nome = "Amico"

            cognome = (row.get("LASTNAME") or "").strip()
            if cognome.lower() in ("null", "none"):
                cognome = ""

            azienda = (row.get("COMPANY") or "").strip()
            if azienda.lower() in ("null", "none"):
                azienda = ""

            telefono = (row.get("SMS") or "").strip()
            sic_id = (row.get("SIC_ID") or "").strip()
            provincia = (row.get("PROVINCIA") or "").strip()
            tema = (row.get("TEMA") or "COMMUNITY").strip()
            flusso = (row.get("FLUSSO") or "FLOW_WELCOME").strip()
            status = (row.get("STATUS") or "ATTIVO").strip()

            try:
                cur.execute("""
                INSERT INTO ghl_contact (
                    sub_account_id, sic_id, email, nome, cognome, azienda,
                    telefono, provincia, tag, flusso, consenso, unsub, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, ?)
                ON CONFLICT(email) DO UPDATE SET
                    nome=COALESCE(excluded.nome, ghl_contact.nome),
                    azienda=COALESCE(excluded.azienda, ghl_contact.azienda),
                    telefono=COALESCE(excluded.telefono, ghl_contact.telefono),
                    tag=excluded.tag,
                    status=excluded.status
                """, (sub_id, sic_id, email, nome, cognome, azienda, telefono, provincia, tema, flusso, status))
                
                if cur.rowcount == 1:
                    count_inserted += 1
                else:
                    count_updated += 1
            except Exception as e:
                count_skipped += 1

    # Assicuriamo che l'indirizzo di test prioritario sia sempre presente e attivo
    cur.execute("""
    INSERT INTO ghl_contact (
        sub_account_id, sic_id, email, nome, cognome, azienda, tag, flusso, consenso, unsub, status
    ) VALUES (?, 'SIC-TEST-OWNER-001', 'labomobile.lm@gmail.com', 'Mirco', 'Pregnolato', 'Labo Tecnic Studio', 'TEST_VIP', 'FLOW_WELCOME', 1, 0, 'ATTIVO')
    ON CONFLICT(email) DO UPDATE SET status='ATTIVO', consenso=1, unsub=0
    """, (sub_id,))

    conn.commit()
    conn.close()

    total = count_inserted + count_updated
    print(f"[OK] Ingestione completata: {count_inserted} nuovi, {count_updated} aggiornati, {count_skipped} scartati. Totale nel DB: {total}")
    return total

if __name__ == "__main__":
    import_all()

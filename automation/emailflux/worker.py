"""
worker.py — Esecutore dei flussi di email marketing per DEPENDEX.SOCIAL.
Esegue lo step corrente, invia via Hostinger SMTP, registra i log e programma lo step successivo.
"""
import re
import json
import time
import datetime
import urllib.parse
from config import BASE_URL, DRY_RUN, DAILY_CAP, BATCH_SIZE, THROTTLE_SEC
from db import get_connection, get_or_create_sub_account
from transport import send_smtp

def render_content(template_html, subject, contact, send_id):
    nome = (contact["nome"] or "Amico").strip()
    email = contact["email"]
    sic = contact["sic_id"] or ""
    unsub_url = f"{BASE_URL}/privacy.php?u=" + urllib.parse.quote(email)

    replacements = {
        "{nome}": nome,
        "{{NOME}}": nome,
        "{email}": email,
        "{{EMAIL}}": email,
        "{{UNSUB}}": unsub_url,
        "{unsub_url}": unsub_url,
        "{base_url}": BASE_URL
    }

    for k, v in replacements.items():
        template_html = template_html.replace(k, v)
        subject = subject.replace(k, v)

    # Pixel di tracciamento apertura anonimo
    tracking_pixel = f'<img src="{BASE_URL}/api.php?action=track_open&s={send_id}" width="1" height="1" alt="" style="display:none">'
    if "</body>" in template_html.lower():
        template_html = re.sub(r"</body>", tracking_pixel + "</body>", template_html, count=1, flags=re.I)
    else:
        template_html += tracking_pixel

    return subject, template_html, unsub_url

def get_sent_today_count(conn):
    today = datetime.date.today().isoformat()
    cur = conn.cursor()
    row = cur.execute("SELECT count(*) as cnt FROM ghl_send_log WHERE stato='SUCCESS' AND created_at LIKE ?", (today + '%',)).fetchone()
    return row['cnt'] if row else 0

def run_queue(limit=None):
    conn = get_connection()
    sub_id = get_or_create_sub_account(conn)
    cur = conn.cursor()

    now = datetime.datetime.now(datetime.timezone.utc).isoformat()
    sent_today = get_sent_today_count(conn)
    cap_left = max(0, DAILY_CAP - sent_today)

    if cap_left <= 0 and not DRY_RUN:
        print(f"[!] DAILY_CAP ({DAILY_CAP}) raggiunto per oggi. Invii sospesi fino a domani.")
        conn.close()
        return 0

    batch_limit = limit or min(BATCH_SIZE, cap_left if not DRY_RUN else BATCH_SIZE)

    query = """
    SELECT e.id as eid, e.workflow_id, e.contact_id, e.step_corrente,
           w.nome as wf_nome, w.azioni_json,
           c.email, c.nome, c.sic_id, c.consenso, c.unsub
    FROM ghl_workflow_enrollment e
    JOIN ghl_contact c ON c.id = e.contact_id
    JOIN ghl_workflow w ON w.id = e.workflow_id
    WHERE e.stato = 'active'
      AND e.next_at <= ?
      AND c.unsub = 0
      AND c.consenso = 1
      AND c.status = 'ATTIVO'
    ORDER BY e.next_at ASC
    LIMIT ?
    """

    due_enrollments = cur.execute(query, (now, batch_limit)).fetchall()
    print(f"[*] Task in coda pronti: {len(due_enrollments)} · DRY_RUN={DRY_RUN} · Invii rimanenti oggi: {cap_left}")

    success_count = 0
    error_count = 0

    for enr in due_enrollments:
        eid = enr["eid"]
        step_idx = enr["step_corrente"]
        azioni = json.loads(enr["azioni_json"] or "[]")

        if step_idx >= len(azioni):
            cur.execute("UPDATE ghl_workflow_enrollment SET stato='done' WHERE id=?", (eid,))
            conn.commit()
            continue

        step = azioni[step_idx]
        tpl_key = step["template"]
        tpl_row = cur.execute("SELECT id, oggetto, corpo FROM ghl_template WHERE flow_key=?", (tpl_key,)).fetchone()

        if not tpl_row:
            print(f"[!] Template {tpl_key} non trovato. Enrollment #{eid} segnato in errore.")
            cur.execute("UPDATE ghl_workflow_enrollment SET stato='error' WHERE id=?", (eid,))
            conn.commit()
            continue

        subject, html, unsub_url = render_content(tpl_row["corpo"], step.get("subject") or tpl_row["oggetto"], enr, eid)

        if DRY_RUN:
            print(f"[DRY_RUN] Simulato invio a {enr['email']} · Oggetto: {subject}")
            res = {"status": "SUCCESS", "provider": "dry_run", "error": None}
        else:
            print(f"[*] Invio effettivo a {enr['email']} · Step {step_idx + 1}/{len(azioni)}...")
            res = send_smtp(enr["email"], subject, html, list_unsub_url=unsub_url)

        # Logging
        cur.execute("""
        INSERT INTO ghl_send_log (
            sub_account_id, contact_id, email, canale, template_id,
            workflow_id, stato, provider_id, errore
        ) VALUES (?, ?, ?, 'email', ?, ?, ?, ?, ?)
        """, (sub_id, enr["contact_id"], enr["email"], tpl_row["id"], enr["workflow_id"], res["status"], res["provider"], res["error"]))

        if res["status"] == "SUCCESS":
            success_count += 1
            next_step_idx = step_idx + 1
            if next_step_idx < len(azioni):
                next_delay = azioni[next_step_idx].get("delay_gg", 2)
                next_date = datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(days=next_delay)
                cur.execute("UPDATE ghl_workflow_enrollment SET step_corrente=?, next_at=? WHERE id=?", (next_step_idx, next_date.isoformat(), eid))
            else:
                cur.execute("UPDATE ghl_workflow_enrollment SET stato='done' WHERE id=?", (eid,))
        else:
            error_count += 1
            print(f"[ERRORE] Invio fallito a {enr['email']}: {res['error']}")

        conn.commit()
        if not DRY_RUN and THROTTLE_SEC > 0:
            time.sleep(THROTTLE_SEC)

    conn.close()
    print(f"[OK] Batch completato: {success_count} inviati con successo, {error_count} falliti.")
    return success_count

if __name__ == "__main__":
    run_queue()

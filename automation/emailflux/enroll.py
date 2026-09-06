"""
enroll.py — Sincronizzazione Template, Definizione Workflow e Auto-Enrollment
per i contatti di DEPENDEX.SOCIAL.
"""
import json
import datetime
from db import get_connection, get_or_create_sub_account
from templates_catalog import TEMPLATES

WORKFLOWS_DEF = {
    "FLOW_WELCOME": {
        "trigger": "new_contact",
        "azioni": [
            {"template": "FLOW_WELCOME/01_BENVENUTO", "delay_gg": 0, "subject": "Benvenuto in DEPENDEX · C'è una sedia pronta per te, {nome}"},
            {"template": "FLOW_WELCOME/02_METODO",    "delay_gg": 2, "subject": "{nome}, perché la sola forza di volontà è un'illusione"},
        ]
    },
    "FLOW_VALORE": {
        "trigger": "offer_nurture",
        "azioni": [
            {"template": "FLOW_VALORE/01_STARTER_KIT", "delay_gg": 0, "subject": "{nome}, quanto ti costa rimandare ancora di un mese?"}
        ]
    },
    "FLOW_WINBACK": {
        "trigger": "inactive_clubber",
        "azioni": [
            {"template": "FLOW_WINBACK/01_SEMPRE_BENVENUTO", "delay_gg": 0, "subject": "{nome}, non importa se sei caduto. Ti aspettiamo."}
        ]
    },
    "FLOW_TEST": {
        "trigger": "test_vip",
        "azioni": [
            {"template": "FLOW_TEST/01_COLLAUDO", "delay_gg": 0, "subject": "✓ [COLLAUDO OK] Infrastruttura Email Marketing DEPENDEX Operativa"}
        ]
    }
}

def sync_templates_and_workflows():
    conn = get_connection()
    sub_id = get_or_create_sub_account(conn)
    cur = conn.cursor()

    print("[*] Sincronizzazione template email...")
    for key, tpl in TEMPLATES.items():
        cur.execute("""
        INSERT INTO ghl_template (sub_account_id, nome, canale, oggetto, corpo, flow_key)
        VALUES (?, ?, 'email', ?, ?, ?)
        ON CONFLICT(flow_key) DO UPDATE SET
            nome=excluded.nome,
            oggetto=excluded.oggetto,
            corpo=excluded.corpo
        """, (sub_id, tpl["nome"], tpl["oggetto"], tpl["corpo"], key))

    print("[*] Sincronizzazione workflow e sequenze...")
    for wf_nome, defn in WORKFLOWS_DEF.items():
        cur.execute("""
        INSERT INTO ghl_workflow (sub_account_id, nome, trigger_json, condizioni_json, azioni_json, attivo)
        VALUES (?, ?, ?, '{}', ?, 1)
        ON CONFLICT(nome) DO UPDATE SET
            trigger_json=excluded.trigger_json,
            azioni_json=excluded.azioni_json,
            attivo=1
        """, (sub_id, wf_nome, json.dumps({"trigger": defn["trigger"]}), json.dumps(defn["azioni"])))

    conn.commit()

    # Auto-enrollment contatti non ancora iscritti
    print("[*] Assegnazione contatti ai flussi...")
    cur.execute("SELECT id FROM ghl_workflow WHERE nome='FLOW_WELCOME'")
    wf_welcome = cur.fetchone()
    if wf_welcome:
        wf_id = wf_welcome['id']
        now = datetime.datetime.now(datetime.timezone.utc).isoformat()
        
        # Iscrive i contatti attivi che non hanno ancora un enrollment
        cur.execute("""
        INSERT OR IGNORE INTO ghl_workflow_enrollment (workflow_id, contact_id, step_corrente, next_at, stato)
        SELECT ?, id, 0, ?, 'active'
        FROM ghl_contact
        WHERE status='ATTIVO' AND unsub=0 AND id NOT IN (
            SELECT contact_id FROM ghl_workflow_enrollment WHERE workflow_id=?
        )
        """, (wf_id, now, wf_id))
        
        enrolled_count = cur.rowcount
        print(f"[OK] {enrolled_count} contatti iscritti al flusso di benvenuto FLOW_WELCOME.")

    conn.commit()
    conn.close()

if __name__ == "__main__":
    sync_templates_and_workflows()

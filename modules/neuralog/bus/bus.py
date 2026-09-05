#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
COMPANY BRAIN — bus/bus.py

Versione Python dello stesso bus di bus.php: STESSO formato JSONL, stessa
cartella, stessi lock. Un agente in Python e uno in PHP possono lavorare sullo
stesso progetto senza sapere l'uno dell'altro.

Cosa e' cambiato rispetto all'originale da cui deriva:
- niente percorso di Windows scritto nel codice: la radice arriva da
  BRAIN_BUS_ROOT (o CORTEX_ROOT, per compatibilita'), altrimenti ./data/bus;
- niente regole di instradamento di un progetto specifico: le categorie si
  leggono, se servono, da un JSON esterno (bus.routing.json);
- attori generici e configurabili (BRAIN_BUS_ACTORS="A,B,HUMAN,SYSTEM").

Dipendenze: nessuna. Solo libreria standard. Python 3.9+.

Comandi:
    python3 bus.py init
    python3 bus.py send --from A --to B --type TASK "titolo" [--files a,b --priority HIGH --ref id]
    python3 bus.py list | tasks | locks | doctor | dashboard | rotate
    python3 bus.py lock FILE --actor A [--hours 2] | unlock FILE --actor A
    python3 bus.py decision "titolo" --actor A [--detail "..."]
    python3 bus.py brief ATTORE
"""
from __future__ import annotations

import argparse
import contextlib
import datetime as dt
import hashlib
import json
import os
import re
import sys
import tempfile
import time
import uuid
from pathlib import Path
from typing import Any, Dict, Iterable, List, Optional, Sequence

TYPES = {"TASK", "DONE", "QUESTION", "ANSWER", "ALERT", "DECISION", "LOCK", "FORWARD"}
STATES = {"OPEN", "IN_PROGRESS", "DONE", "BLOCKED", "CANCELLED"}
FINAL_STATES = {"DONE", "CANCELLED"}
PRIORITIES = {"LOW", "MEDIUM", "HIGH", "CRITICAL"}
DEFAULT_ACTORS = ["AGENT_A", "AGENT_B", "HUMAN", "SYSTEM"]


def bus_root() -> Path:
    """Radice del bus. Nessun percorso di sistema scritto nel codice."""
    env = os.environ.get("BRAIN_BUS_ROOT") or os.environ.get("CORTEX_ROOT")
    if env:
        return Path(env).expanduser()
    return Path(__file__).resolve().parent.parent / "data" / "bus"


def actors() -> List[str]:
    env = os.environ.get("BRAIN_BUS_ACTORS", "")
    if env.strip():
        return [a.strip().upper() for a in env.split(",") if a.strip()]
    cfg = Path(__file__).resolve().parent.parent / "config" / "brain.config.json"
    if cfg.is_file():
        try:
            data = json.loads(cfg.read_text(encoding="utf-8"))
            lst = data.get("bus", {}).get("actors")
            if isinstance(lst, list) and lst:
                return [str(a).upper() for a in lst]
        except Exception:
            pass
    return list(DEFAULT_ACTORS)


def targets() -> List[str]:
    return list(dict.fromkeys(actors() + ["BOTH"]))


def now_iso() -> str:
    return dt.datetime.now(dt.timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")


def parse_ts(value: str) -> dt.datetime:
    try:
        return dt.datetime.strptime(value, "%Y-%m-%dT%H:%M:%SZ").replace(tzinfo=dt.timezone.utc)
    except Exception:
        return dt.datetime.fromtimestamp(0, dt.timezone.utc)


def norm_path(p: str) -> str:
    return str(p).replace("\\", "/").strip().lower()


def atomic_write(path: Path, text: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with tempfile.NamedTemporaryFile("w", encoding="utf-8", newline="\n", delete=False,
                                     dir=str(path.parent), prefix=path.name + ".", suffix=".tmp") as fh:
        fh.write(text)
        tmp = fh.name
    os.replace(tmp, path)


@contextlib.contextmanager
def file_lock(target: Path, timeout: float = 10.0):
    """Lock di scrittura con scadenza: un lock dimenticato non blocca per sempre."""
    lock_path = Path(str(target) + ".write.lock")
    lock_path.parent.mkdir(parents=True, exist_ok=True)
    deadline = time.time() + timeout
    fd = None
    while True:
        try:
            fd = os.open(str(lock_path), os.O_CREAT | os.O_EXCL | os.O_WRONLY)
            os.write(fd, f"{os.getpid()}|{now_iso()}".encode())
            break
        except FileExistsError:
            try:
                if time.time() - lock_path.stat().st_mtime > 60:
                    lock_path.unlink(missing_ok=True)
                    continue
            except OSError:
                pass
            if time.time() >= deadline:
                raise TimeoutError(f"lock non ottenuto: {lock_path}")
            time.sleep(0.08)
    try:
        yield
    finally:
        if fd is not None:
            os.close(fd)
        with contextlib.suppress(FileNotFoundError):
            lock_path.unlink()


class Bus:
    def __init__(self, root: Optional[Path] = None):
        self.root = Path(root or bus_root())
        self.outbox = self.root / "outbox"
        self.archive = self.root / "_archive"
        self.errors = self.root / "_errors"
        self.locks_file = self.root / "LOCKS.json"
        self.dashboard_file = self.root / "DASHBOARD.md"
        self.decisions_file = self.root / "DECISIONS.md"

    # ------------------------------------------------------------------ init
    def init(self) -> Dict[str, Any]:
        for d in (self.root, self.outbox, self.archive, self.errors):
            d.mkdir(parents=True, exist_ok=True)
        for a in actors():
            f = self.outbox / f"{a.lower()}.jsonl"
            if not f.exists():
                f.touch()
        if not self.locks_file.exists():
            self.write_locks([])
        if not self.decisions_file.exists():
            atomic_write(self.decisions_file, "# Decisioni\n\n")
        return {"ok": True, "root": str(self.root), "actors": actors()}

    # --------------------------------------------------------------- messaggi
    @staticmethod
    def message(sender: str, target: str, msg_type: str, title: str, *, detail: str = "",
                files: Optional[Sequence[str]] = None, priority: str = "MEDIUM",
                state: str = "OPEN", ref: Optional[str] = None) -> Dict[str, Any]:
        return {
            "id": f"m-{dt.datetime.now(dt.timezone.utc).strftime('%Y%m%dT%H%M%S')}-{uuid.uuid4().hex[:8]}",
            "ts": now_iso(),
            "from": sender.upper(),
            "to": target.upper(),
            "type": msg_type.upper(),
            "title": title.strip(),
            "detail": detail,
            "files": [str(f) for f in (files or []) if str(f).strip()],
            "priority": priority.upper(),
            "state": state.upper(),
            "ref": ref,
        }

    @staticmethod
    def validate(m: Any) -> List[str]:
        errs: List[str] = []
        if not isinstance(m, dict):
            return ["il messaggio non e' un oggetto JSON"]
        for k in ("id", "ts", "from", "to", "type", "title"):
            if not str(m.get(k, "")).strip():
                errs.append(f"campo mancante: {k}")
        if m.get("type") and m["type"] not in TYPES:
            errs.append(f"tipo sconosciuto: {m['type']}")
        if m.get("state") and m["state"] not in STATES:
            errs.append(f"stato sconosciuto: {m['state']}")
        if m.get("priority") and m["priority"] not in PRIORITIES:
            errs.append("priorita' sconosciuta")
        if m.get("from") and m["from"] not in actors():
            errs.append(f"mittente sconosciuto: {m['from']}")
        if m.get("to") and m["to"] not in targets():
            errs.append(f"destinatario sconosciuto: {m['to']}")
        if "files" in m and not isinstance(m["files"], list):
            errs.append("files deve essere una lista")
        return errs

    def append(self, m: Dict[str, Any]) -> Dict[str, Any]:
        self.init()
        errs = self.validate(m)
        if errs:
            return {"ok": False, "errors": errs}
        path = self.outbox / f"{m['from'].lower()}.jsonl"
        line = json.dumps(m, ensure_ascii=False)
        with file_lock(path):
            with path.open("a", encoding="utf-8", newline="\n") as fh:
                fh.write(line + "\n")
                fh.flush()
                os.fsync(fh.fileno())
        return {"ok": True, "id": m["id"], "file": str(path)}

    def read(self) -> List[Dict[str, Any]]:
        self.init()
        msgs: List[Dict[str, Any]] = []
        bad: List[Dict[str, Any]] = []
        for f in sorted(self.outbox.glob("*.jsonl")):
            for n, line in enumerate(f.read_text(encoding="utf-8-sig", errors="replace").splitlines(), 1):
                if not line.strip():
                    continue
                try:
                    obj = json.loads(line)
                except Exception as exc:
                    bad.append({"file": f.name, "line": n, "error": str(exc), "raw": line[:300]})
                    continue
                if self.validate(obj):
                    bad.append({"file": f.name, "line": n, "error": "; ".join(self.validate(obj)), "raw": line[:300]})
                    continue
                obj["_file"] = f.name
                msgs.append(obj)
        if bad:
            self.errors.mkdir(parents=True, exist_ok=True)
            with (self.errors / "invalid.jsonl").open("w", encoding="utf-8") as fh:
                for b in bad:
                    fh.write(json.dumps(b, ensure_ascii=False) + "\n")
        msgs.sort(key=lambda m: m["ts"])
        return msgs

    # ------------------------------------------------------------------ task
    def task_state(self, task: Dict[str, Any], msgs: Sequence[Dict[str, Any]]):
        related = sorted([m for m in msgs if m.get("ref") == task["id"]], key=lambda m: m["ts"])
        if not related:
            return task.get("state", "OPEN"), None, 0
        return related[-1]["state"], related[-1], len(related)

    def active_tasks(self, msgs: Sequence[Dict[str, Any]]) -> List[Dict[str, Any]]:
        out = []
        for m in msgs:
            if m["type"] not in {"TASK", "FORWARD"}:
                continue
            state, last, n = self.task_state(m, msgs)
            if state in FINAL_STATES:
                continue
            item = dict(m)
            item["effective_state"] = state
            item["last_event"] = last
            item["replies"] = n
            out.append(item)
        return out

    # ------------------------------------------------------------------ lock
    def read_locks(self) -> List[Dict[str, Any]]:
        if not self.locks_file.exists():
            return []
        try:
            data = json.loads(self.locks_file.read_text(encoding="utf-8-sig"))
        except Exception:
            return []
        return list(data.get("locks", [])) if isinstance(data, dict) else []

    def write_locks(self, locks: Sequence[Dict[str, Any]]) -> None:
        atomic_write(self.locks_file, json.dumps(
            {"version": 1, "updated_at": now_iso(), "locks": list(locks)},
            ensure_ascii=False, indent=2) + "\n")

    @staticmethod
    def lock_expired(lock: Dict[str, Any]) -> bool:
        try:
            return parse_ts(lock["expires_at"]) <= dt.datetime.now(dt.timezone.utc)
        except Exception:
            return True

    def lock(self, actor: str, file_name: str, hours: float = 2.0, ref: Optional[str] = None) -> Dict[str, Any]:
        self.init()
        actor = actor.upper()
        with file_lock(self.locks_file):
            keep = []
            for l in self.read_locks():
                if self.lock_expired(l):
                    continue
                if norm_path(l["file"]) == norm_path(file_name):
                    if str(l["owner"]).upper() != actor:
                        return {"ok": False, "error": f"file gia' bloccato da {l['owner']}", "lock": l}
                    continue
                keep.append(l)
            lock = {
                "id": "lock-" + hashlib.sha256(f"{actor}|{file_name}".encode()).hexdigest()[:16],
                "file": file_name, "owner": actor,
                "acquired_at": now_iso(),
                "expires_at": (dt.datetime.now(dt.timezone.utc) + dt.timedelta(hours=hours)).strftime("%Y-%m-%dT%H:%M:%SZ"),
                "task_ref": ref, "status": "ACTIVE",
            }
            keep.append(lock)
            self.write_locks(keep)
        self.append(self.message(actor, "SYSTEM", "LOCK", f"lock su {file_name}",
                                 files=[file_name], ref=ref, state="IN_PROGRESS"))
        return {"ok": True, "lock": lock}

    def unlock(self, actor: str, file_name: str) -> Dict[str, Any]:
        actor = actor.upper()
        found = False
        with file_lock(self.locks_file):
            keep = []
            for l in self.read_locks():
                if norm_path(l["file"]) == norm_path(file_name) and str(l["owner"]).upper() == actor:
                    found = True
                    continue
                if self.lock_expired(l):
                    continue
                keep.append(l)
            self.write_locks(keep)
        return {"ok": found, "released": found}

    # ------------------------------------------------- conflitti e anomalie
    def conflicts(self, msgs: Sequence[Dict[str, Any]], locks: Sequence[Dict[str, Any]]) -> List[Dict[str, Any]]:
        out = []
        tasks = self.active_tasks(msgs)
        for i, left in enumerate(tasks):
            lf = {norm_path(f): f for f in left["files"] if str(f).strip()}
            for right in tasks[i + 1:]:
                if right["to"] == left["to"]:
                    continue
                for f in right["files"]:
                    k = norm_path(f)
                    if k in lf:
                        out.append({"kind": "TASK_FILE", "file": lf[k], "left": left["id"],
                                    "right": right["id"], "owners": f"{left['to']}/{right['to']}"})
        by_file: Dict[str, Dict[str, Any]] = {}
        for l in locks:
            if l.get("status") != "ACTIVE" or self.lock_expired(l):
                continue
            k = norm_path(l["file"])
            if k in by_file and str(by_file[k]["owner"]).upper() != str(l["owner"]).upper():
                out.append({"kind": "LOCK", "file": l["file"], "left": by_file[k]["id"],
                            "right": l["id"], "owners": f"{by_file[k]['owner']}/{l['owner']}"})
            else:
                by_file[k] = l
        return out

    def orphans(self, msgs: Sequence[Dict[str, Any]], hours: int = 24) -> List[Dict[str, Any]]:
        cut = dt.datetime.now(dt.timezone.utc) - dt.timedelta(hours=hours)
        return [t for t in self.active_tasks(msgs) if parse_ts(t["ts"]) <= cut and t["replies"] == 0]

    def duplicates(self, msgs: Sequence[Dict[str, Any]]) -> List[List[Dict[str, Any]]]:
        groups: Dict[str, List[Dict[str, Any]]] = {}
        for t in self.active_tasks(msgs):
            key = re.sub(r"[^a-z0-9 ]", "", t["title"].lower()).strip()
            key = re.sub(r"\s+", " ", key)
            if key:
                groups.setdefault(key, []).append(t)
        out = []
        for items in groups.values():
            if len(items) < 2:
                continue
            refs = {i.get("ref") for i in items if i.get("ref")}
            if len(refs) == 1:
                continue
            out.append(items)
        return out

    # ------------------------------------------------- dashboard e brief
    def dashboard(self) -> Dict[str, Any]:
        msgs = self.read()
        locks = self.read_locks()
        tasks = self.active_tasks(msgs)
        conf = self.conflicts(msgs, locks)
        orph = self.orphans(msgs)
        dups = self.duplicates(msgs)
        active_locks = [l for l in locks if not self.lock_expired(l)]

        md = [f"# Dashboard del bus\n\n_Generata il {now_iso()} — file derivato, non modificarlo a mano._\n",
              "## Numeri\n",
              f"- messaggi: {len(msgs)}", f"- task attivi: {len(tasks)}",
              f"- lock attivi: {len(active_locks)}", f"- conflitti: {len(conf)}",
              f"- orfani: {len(orph)}", f"- possibili doppioni: {len(dups)}\n",
              "## Task attivi\n"]
        if tasks:
            md.append("| id | da | a | stato | priorita | titolo |")
            md.append("|---|---|---|---|---|---|")
            for t in tasks:
                md.append(f"| {t['id']} | {t['from']} | {t['to']} | {t['effective_state']} | "
                          f"{t['priority']} | {t['title'][:80].replace('|', '/')} |")
        else:
            md.append("_nessuno_")
        if conf:
            md.append("\n## Conflitti\n")
            for c in conf:
                md.append(f"- **{c['kind']}** su `{c['file']}` — {c['left']} vs {c['right']} ({c['owners']})")
        if orph:
            md.append("\n## Task senza risposta\n")
            for o in orph:
                md.append(f"- {o['id']} — {o['title'][:90]} (dal {o['ts']})")
        atomic_write(self.dashboard_file, "\n".join(md) + "\n")
        for a in actors():
            atomic_write(self.root / f"BRIEF_{a}.md", self.brief(a, msgs, locks))
        return {"ok": True, "messages": len(msgs), "tasks": len(tasks), "conflicts": len(conf),
                "orphans": len(orph), "duplicates": len(dups), "dashboard": str(self.dashboard_file)}

    def brief(self, actor: str, msgs: Sequence[Dict[str, Any]], locks: Sequence[Dict[str, Any]]) -> str:
        actor = actor.upper()
        order = {"CRITICAL": 0, "HIGH": 1, "MEDIUM": 2, "LOW": 3}
        mine = [t for t in self.active_tasks(msgs) if t["to"] in (actor, "BOTH")]
        mine.sort(key=lambda t: order.get(t["priority"], 9))
        out = [f"# Brief — {actor}\n", f"_{now_iso()}_\n", f"## Cosa hai in mano ({len(mine)})\n"]
        if not mine:
            out.append("_niente di aperto_")
        for t in mine:
            out.append(f"- **[{t['priority']}] {t['title'][:100]}**  ")
            files = ("  · file: " + ", ".join(f"`{f}`" for f in t["files"][:5])) if t["files"] else ""
            out.append(f"  id `{t['id']}` · da {t['from']} · stato {t['effective_state']}{files}")
        my_locks = [l for l in locks if str(l["owner"]).upper() == actor and not self.lock_expired(l)]
        out.append(f"\n## File che stai tenendo bloccati ({len(my_locks)})\n")
        out.extend([f"- `{l['file']}` fino a {l['expires_at']}" for l in my_locks] or ["_nessuno_"])
        others = [l for l in locks if str(l["owner"]).upper() != actor and not self.lock_expired(l)]
        if others:
            out.append("\n## Non toccare: bloccati da altri\n")
            out.extend([f"- `{l['file']}` — {l['owner']}" for l in others])
        return "\n".join(out) + "\n"

    def decision(self, actor: str, title: str, detail: str = "", ref: Optional[str] = None) -> Dict[str, Any]:
        res = self.append(self.message(actor, "SYSTEM", "DECISION", title, detail=detail, ref=ref, state="DONE"))
        if res.get("ok"):
            with file_lock(self.decisions_file):
                with self.decisions_file.open("a", encoding="utf-8") as fh:
                    fh.write(f"\n## {now_iso()} — {title}\n\n- deciso da: {actor.upper()}\n")
                    if ref:
                        fh.write(f"- riferimento: {ref}\n")
                    if detail:
                        fh.write(f"\n{detail}\n")
        return res

    def rotate(self, max_lines: int = 5000) -> Dict[str, Any]:
        moved = []
        for f in sorted(self.outbox.glob("*.jsonl")):
            lines = f.read_text(encoding="utf-8-sig", errors="replace").splitlines()
            if len(lines) <= max_lines:
                continue
            keep, old = lines[-max_lines:], lines[:-max_lines]
            stamp = dt.datetime.now(dt.timezone.utc).strftime("%Y%m%dT%H%M%S")
            atomic_write(self.archive / f"{f.stem}-{stamp}.jsonl", "\n".join(old) + "\n")
            atomic_write(f, "\n".join(keep) + "\n")
            moved.append({"file": f.name, "archived": len(old)})
        return {"ok": True, "rotated": moved}


def say(value: Any) -> None:
    if isinstance(value, str):
        print(value)
    else:
        print(json.dumps(value, ensure_ascii=False, indent=2))


def main(argv: Optional[Sequence[str]] = None) -> int:
    p = argparse.ArgumentParser(description="Bus degli agenti su file (compatibile con bus.php)")
    p.add_argument("command", choices=["init", "send", "list", "tasks", "lock", "unlock", "locks",
                                       "decision", "dashboard", "brief", "rotate", "doctor"])
    p.add_argument("positional", nargs="*", default=[])
    p.add_argument("--root")
    p.add_argument("--from", dest="sender", default="HUMAN")
    p.add_argument("--to", dest="target", default="SYSTEM")
    p.add_argument("--type", dest="msg_type", default="TASK")
    p.add_argument("--detail", default="")
    p.add_argument("--files", default="")
    p.add_argument("--priority", default="MEDIUM")
    p.add_argument("--state", default="OPEN")
    p.add_argument("--ref", default=None)
    p.add_argument("--actor", default="HUMAN")
    p.add_argument("--hours", type=float, default=2.0)
    p.add_argument("--max", type=int, default=5000)
    # parse_intermixed_args: permette "send --from A --to B TITOLO" senza che
    # argparse si confonda fra opzioni e argomenti liberi.
    a = p.parse_intermixed_args(argv)
    bus = Bus(Path(a.root) if a.root else None)
    title = a.positional[0] if a.positional else ""

    if a.command == "init":
        say(bus.init())
    elif a.command == "send":
        say(bus.append(bus.message(a.sender, a.target, a.msg_type, title or "senza titolo",
                                   detail=a.detail, files=[f for f in a.files.split(",") if f],
                                   priority=a.priority, state=a.state, ref=a.ref)))
    elif a.command == "list":
        say([[m["ts"], m["type"], f"{m['from']}->{m['to']}", m["state"], m["title"]] for m in bus.read()])
    elif a.command == "tasks":
        say([[t["id"], t["to"], t["effective_state"], t["title"]] for t in bus.active_tasks(bus.read())])
    elif a.command == "lock":
        say(bus.lock(a.actor, title, a.hours, a.ref))
    elif a.command == "unlock":
        say(bus.unlock(a.actor, title))
    elif a.command == "locks":
        say(bus.read_locks())
    elif a.command == "decision":
        say(bus.decision(a.actor, title, a.detail, a.ref))
    elif a.command == "dashboard":
        say(bus.dashboard())
    elif a.command == "brief":
        print(bus.brief(title or "HUMAN", bus.read(), bus.read_locks()))
    elif a.command == "rotate":
        say(bus.rotate(a.max))
    elif a.command == "doctor":
        msgs, locks = bus.read(), bus.read_locks()
        say({"root": str(bus.root), "messages": len(msgs), "active_tasks": len(bus.active_tasks(msgs)),
             "locks": len(locks), "conflicts": bus.conflicts(msgs, locks),
             "orphans": len(bus.orphans(msgs)), "duplicates": len(bus.duplicates(msgs))})
    return 0


if __name__ == "__main__":
    sys.exit(main())

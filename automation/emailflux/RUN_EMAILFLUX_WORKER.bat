@echo off
chcp 65001 > nul
echo ============================================================
echo   DEPENDEX.SOCIAL · ESECUZIONE WORKER EMAILFLUX
echo ============================================================
cd /d "%~dp0"
python enroll.py
python worker.py
pause

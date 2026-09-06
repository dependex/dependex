@echo off
chcp 65001 > nul
echo ============================================================
echo   DEPENDEX.SOCIAL · TEST INVIO SMTP HOSTINGER
echo   Destinatario: labomobile.lm@gmail.com
echo ============================================================
cd /d "%~dp0"
python test_smtp_delivery.py labomobile.lm@gmail.com
pause

@echo off
chcp 65001 >nul
title UNIVERSAL EMAIL REVENUE OS — CONTROL PLANE
color 0B

echo =========================================================================
echo  UNIVERSAL EMAIL REVENUE OS — DEPENDEX & OLTRE
echo  Control Plane Marketing Automation (GitHub-Native, Event-Driven)
echo =========================================================================
echo.
echo [1] Verifica Stato Operativo del Sistema (--status)
echo [2] Invia Email di Test Autenticata a labomobile.lm@gmail.com (--test)
echo [3] Esegui Batch Simulato DRY-RUN (--dispatch --dry-run)
echo [4] Esegui Batch LIVE con Rate Limiting (--dispatch)
echo [5] Avvia Test Suite Completa (tests/test_email_os.py)
echo [6] Esci
echo.
set /p opt="Seleziona un'opzione (1-6): "

if "%opt%"=="1" (
    echo.
    python automation\emailflux\run_engine.py --status
    pause
    goto end
)
if "%opt%"=="2" (
    echo.
    python automation\emailflux\run_engine.py --test
    pause
    goto end
)
if "%opt%"=="3" (
    echo.
    python automation\emailflux\run_engine.py --dispatch --dry-run --limit 10
    pause
    goto end
)
if "%opt%"=="4" (
    echo.
    echo ATTENZIONE: Invio reale tramite Hostinger SMTP SSL 465 (info@dependex.support).
    set /p confirm="Confermi l'invio del batch? (s/n): "
    if /i "%confirm%"=="s" (
        python automation\emailflux\run_engine.py --dispatch --limit 10
    ) else (
        echo Invio annullato.
    )
    pause
    goto end
)
if "%opt%"=="5" (
    echo.
    python tests\test_email_os.py
    pause
    goto end
)

:end
echo Fine sessione.

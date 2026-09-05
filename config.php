<?php
declare(strict_types=1);
const APP_NAME = 'DEPENDEX';
const APP_PAYOFF = 'AL CLUB. COL CLUB.';
const APP_EMAIL = 'info@dependex.social';
const DB_PATH = __DIR__ . '/data/acat_community.sqlite';
const SESSION_NAME = 'ACATCOMMUNITYSESSID';
const TRUSTED_DEVICE_COOKIE = 'acat_trusted_device';
const TRUSTED_DEVICE_DAYS = 90;
const CSRF_KEY = '_csrf';
date_default_timezone_set('Europe/Rome');
session_name(SESSION_NAME);
session_start();

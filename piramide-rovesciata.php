<?php
/**
 * DEPENDEX.SOCIAL — REINDIRIZZAMENTO CANONICO
 * Reindirizza verso la pagina ufficiale dell'Organigramma della Rete dei Club.
 */
if (!headers_sent()) {
    header("Location: /organigramma.php", true, 301);
}
require_once __DIR__ . '/organigramma.php';

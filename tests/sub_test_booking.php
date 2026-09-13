<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'event_sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
    'nome' => 'Test',
    'cognome' => 'Volontario',
    'email' => 'test.sub.' . time() . '@dependex.test',
    'phone' => '3471122334',
    'role_type' => 'Volontario',
    'dietary_notes' => 'Vegetariano',
    'num_seats' => 1,
    'notes' => 'Test automatico',
    'privacy_accepted' => '1'
];
require __DIR__ . '/../api-event-booking.php';

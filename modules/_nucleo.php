<?php
declare(strict_types=1);

$root = __DIR__;
while (!file_exists($root . '/bootstrap.php') && dirname($root) !== $root) {
    $root = dirname($root);
}
if (file_exists($root . '/bootstrap.php')) {
    require_once $root . '/bootstrap.php';
}

if (!function_exists('demo_esigi')) {
    function demo_esigi(): void {}
}
if (!function_exists('demo_io')) {
    function demo_io(): array {
        $u = function_exists('current_user') ? current_user() : null;
        return [
            'id' => $u['id'] ?? 1,
            'email' => $u['email'] ?? 'visitatore@dependex.social',
            'nome' => $u['name'] ?? 'Membro Comunità',
            'ruolo' => $u['role'] ?? 'Membro',
            'sic' => 'SIC-COMMUNITY'
        ];
    }
}
if (!function_exists('demo_admin_sessione')) {
    function demo_admin_sessione(): bool {
        return false;
    }
}

<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$_POST['accion'] = 'ingresar';
require __DIR__ . '/admin.php';

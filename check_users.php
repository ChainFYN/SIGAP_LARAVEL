<?php

use App\Models\Pengguna;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$users = Pengguna::with('peran')->get();
foreach ($users as $u) {
    echo 'ID: '.$u->id_pengguna.' | Username: '.($u->username ?? '-').' | NIK: '.($u->nik ?? '-').' | Email: '.$u->email.' | Peran: '.($u->peran->nama_peran ?? '-').PHP_EOL;
}

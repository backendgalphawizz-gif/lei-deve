<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apps = \App\Models\LeiApplication::query()
    ->where('status', 'approved')
    ->whereNotNull('lei_number')
    ->orderBy('id')
    ->get(['id', 'application_code', 'entity_name', 'lei_number', 'expiry_date', 'user_id']);

foreach ($apps as $a) {
    echo sprintf(
        "%d | %s | %s | expires: %s\n",
        $a->id,
        $a->lei_number,
        $a->entity_name,
        $a->expiry_date?->format('Y-m-d') ?? 'null'
    );
}

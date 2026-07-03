<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = (int) ($argv[1] ?? 90);
$expiry = $argv[2] ?? '2025-06-01';

$application = \App\Models\LeiApplication::findOrFail($id);
$application->update(['expiry_date' => $expiry]);

$subscriptions = app(\App\Services\SubscriptionService::class);
$registry = app(\App\Services\PublicRegistrySearchService::class);
$meta = $registry->recordMeta($application->fresh());

echo "Updated application #{$application->id}\n";
echo "Entity: {$application->entity_name}\n";
echo "LEI: {$application->lei_number}\n";
echo "Expiry: {$application->expiry_date->format('Y-m-d')}\n";
echo "Status: {$meta['status_label']} ({$meta['status']})\n";
echo "Can renew: ".($meta['can_renew'] ? 'yes' : 'no')."\n";
echo "Renew URL: ".($meta['renew_url'] ?? 'n/a')."\n";

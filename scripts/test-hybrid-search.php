<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$registry = app(\App\Services\PublicRegistrySearchService::class);

echo "=== Suggest: apple ===\n";
foreach ($registry->suggest('apple', 'company', 5) as $item) {
    echo ($item['source_label'] ?? '?').' | '.$item['lei_number'].' | '.$item['entity_name']."\n";
}

echo "\n=== Search: ajay ===\n";
$results = $registry->search('ajay', 'all', 10);
echo 'Total: '.$results->total()."\n";
foreach ($results as $r) {
    echo ($r['source_label'] ?? '?').' | '.$r['lei_number'].' | '.$r['entity_name'].' | '.$r['status_label']."\n";
}

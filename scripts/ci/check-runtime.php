<?php
// A developer's ignored vendor/ directory must not hide a stale lockfile.
$composer = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);
$targetVersion = ltrim($composer['require']['laravel/framework'] ?? '13.35.0', '^~v');

$lock = json_decode(file_get_contents(__DIR__.'/../../composer.lock'), true);
foreach ($lock['packages'] as $package) {
    if ($package['name'] === 'laravel/framework') {
        $lockedVersion = ltrim($package['version'], 'v');
        if ($lockedVersion !== $targetVersion) {
            fwrite(STDERR, "Dependency mismatch: the application targets Laravel {$targetVersion} / PHP 8.4, but composer.lock contains ".$package['version'].". Synchronize composer.json and composer.lock with the application before deployment.\n");
            exit(1);
        }
        exit(0);
    }
}
fwrite(STDERR, "laravel/framework is missing from composer.lock.\n");
exit(1);

<?php
// A developer's ignored vendor/ directory must not hide a stale lockfile.
$lock = json_decode(file_get_contents(__DIR__.'/../../composer.lock'), true);
foreach ($lock['packages'] as $package) {
    if ($package['name'] === 'laravel/framework') {
        if (ltrim($package['version'], 'v') !== '13.24.0') {
            fwrite(STDERR, "Dependency mismatch: the application targets Laravel 13.24.0 / PHP 8.4, but composer.lock contains ".$package['version'].". Synchronize composer.json and composer.lock with the application before deployment.\n");
            exit(1);
        }
        exit(0);
    }
}
fwrite(STDERR, "laravel/framework is missing from composer.lock.\n");
exit(1);

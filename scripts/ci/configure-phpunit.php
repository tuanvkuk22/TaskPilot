<?php
// Generate a CI-only configuration; keep local forced SQLite isolation intact.
$database = getenv('CI_DATABASE');
if (!in_array($database, ['mysql', 'sqlite'], true)) {
    throw new RuntimeException('CI_DATABASE must be mysql or sqlite.');
}
$xml = simplexml_load_file(__DIR__.'/../../phpunit.xml');
foreach ($xml->php->env as $env) {
    if ((string) $env['name'] === 'DB_CONNECTION') {
        $env['value'] = $database;
    }
    if ((string) $env['name'] === 'DB_DATABASE') {
        $env['value'] = $database === 'mysql' ? 'taskpilot_ci' : ':memory:';
    }
}
$xml->asXML(__DIR__.'/../../phpunit.ci.xml');

<?php

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

function parseAgnosticDsn(string $dsn): ?array
{
    $regex = '#^(?P<adapter>[^\\:]+)\\://(?:(?P<user>[^\\:@]+)(?:\\:(?P<pass>[^@]*))?@)?'
        . '(?P<host>[^\\:@/]+)(?:\\:(?P<port>[1-9]\\d*))?/(?P<name>[^\?]+)(?:\?(?P<query>.*))?$#';

    if (preg_match($regex, trim($dsn), $parsedOptions)) {
        $additionalOpts = [];
        if (isset($parsedOptions['query'])) {
            parse_str($parsedOptions['query'], $additionalOpts);
        }
        $validOptions = ['adapter', 'user', 'pass', 'host', 'port', 'name'];
        $parsedOptions = array_filter(array_intersect_key($parsedOptions, array_flip($validOptions)));
        return array_merge($additionalOpts, $parsedOptions);
    }

    return null;
}

function getAppConfig(): ?array
{
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__ . '/.env');

    return parseAgnosticDsn(
        getenv('DATABASE')
    );
}

function getTestConfig(): ?array
{
    $dotenv = new Dotenv();
    $dotenv->overload(__DIR__ . '/.env.test');

    return parseAgnosticDsn(
        getenv('DATABASE')
    );
}

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'app',
            'app' => getAppConfig(),
            'test' => getTestConfig(),
        ],
        'version_order' => 'execution',
        'templates' => [
            'file' => __DIR__ . '/db/MigrationClass.template',
        ],
    ];

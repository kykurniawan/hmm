<?php

namespace Kykurniawan\Hmm;

class Constants
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    const SUPPORTED_METHODS = [self::METHOD_GET, self::METHOD_POST];

    const CONF_BASE_URL = 'base_url';
    const CONF_VIEW_PATH = 'view_path';
    const CONF_PUBLIC_PATH = 'public_path';
    const CONF_DATABASE_NAME = 'database_name';
    const CONF_DATABASE_HOST = 'database_host';
    const CONF_DATABASE_PORT = 'database_port';
    const CONF_DATABASE_USERNAME = 'database_username';
    const CONF_DATABASE_PASSWORD = 'database_password';
    const CONF_MIGRATION_PATH = 'migration_path';
}

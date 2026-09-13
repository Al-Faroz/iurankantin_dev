<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\DatabaseHandler;

class Session extends BaseConfig
{
    /** @var class-string<BaseHandler> */
    public string $driver = DatabaseHandler::class;
    public string $cookieName = 'iuran_session';
    public int $expiration = 7200;
    public string $savePath = 'ci_sessions';
    public bool $matchIP = false;
    public int $timeToUpdate = 300;
    public bool $regenerateDestroy = false;
    public ?string $DBGroup = 'default';
    public int $lockRetryInterval = 100_000;
    public int $lockMaxRetries = 300;
}

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        $testKey = 'base64:' . base64_encode(str_repeat('t', 32));
        putenv('APP_KEY=' . $testKey);
        $_ENV['APP_KEY'] = $testKey;
        $_SERVER['APP_KEY'] = $testKey;

        putenv('DB_CONNECTION=sqlite');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_CONNECTION'] = 'sqlite';

        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_DATABASE'] = ':memory:';

        putenv('CACHE_STORE=array');
        $_ENV['CACHE_STORE'] = 'array';
        $_SERVER['CACHE_STORE'] = 'array';

        putenv('SESSION_DRIVER=array');
        $_ENV['SESSION_DRIVER'] = 'array';
        $_SERVER['SESSION_DRIVER'] = 'array';

        putenv('MAIL_MAILER=array');
        $_ENV['MAIL_MAILER'] = 'array';
        $_SERVER['MAIL_MAILER'] = 'array';

        return parent::createApplication();
    }
}

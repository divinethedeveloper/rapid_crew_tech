<?php

namespace Middleware;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Level;
use Monolog\Logger as LogMan;

class Logger extends Middleware
{
    private $telegram_handler;

    public function __construct()
    {
        //telegram handler
        $token = '6336558756:AAHcMOCWyyr2Ki17em72I93pDqWA6277W90';
        $chat_id = '6311875714';
        
        $this->telegram_handler = new TelegramBotHandler($token, $chat_id, Level::Error);
    }

    public function DBLogger()
    {
        $logger = new LogMan('database');
        
        //file handler
        $handler = new StreamHandler(__DIR__.'/../logs/db_logs', Level::Debug);

        $logger->pushHandler($this->telegram_handler);
        $logger->pushHandler($handler);

        $logger->pushProcessor(new \Monolog\Processor\WebProcessor());
        $logger->pushProcessor(new \Monolog\Processor\GitProcessor());
        $logger->pushProcessor(new \Monolog\Processor\HostnameProcessor());


        $formatter = new JsonFormatter();

        $handler->setFormatter($formatter);
        $this->telegram_handler->setFormatter($formatter);

        return $logger;
    }

    public function generalLogger()
    {
        $logger = new LogMan('general');
        
        //file handler
        $handler = new StreamHandler(__DIR__.'/../logs/general_logs', Level::Debug);

        $logger->pushHandler($this->telegram_handler);
        $logger->pushHandler($handler);

        $logger->pushProcessor(new \Monolog\Processor\WebProcessor());
        $logger->pushProcessor(new \Monolog\Processor\GitProcessor());
        $logger->pushProcessor(new \Monolog\Processor\HostnameProcessor());


        $formatter = new JsonFormatter();

        $handler->setFormatter($formatter);
        $this->telegram_handler->setFormatter($formatter);

        return $logger;
    }
}

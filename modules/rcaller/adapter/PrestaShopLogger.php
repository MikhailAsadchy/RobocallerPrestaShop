<?php

namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\Logger;
use rcaller\lib\constants\RCallerLoggerLevel;
use Tools;

class PrestaShopLogger implements Logger
{
    public function log($severity, $message)
    {
        if ($severity === RCallerLoggerLevel::ERROR) {
            \PrestaShopLogger::addLog($message, 3);
            Tools::error_log($message);
        }
    }
}
<?php

namespace rcaller\adapter;
use rcaller\lib\ioc\RCallerDependencyContainer;

class PrestaShopAdaptedIOC
{
    /**
     * @var RCallerDependencyContainer
     */
    private static $ioc;

    public static function getIOC()
    {
        if (self::$ioc == null) {
            self::$ioc = new RCallerDependencyContainer(new PrestaShopEventService(), new PrestaShopLogger(), new PrestaShopOptionRepository(), new PrestaShopChannelNameProvider(), new PrestaShopOrderEntryFieldResolver());
        }
        return self::$ioc;
    }
}

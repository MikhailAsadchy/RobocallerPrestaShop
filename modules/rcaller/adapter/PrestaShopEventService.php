<?php

namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\EventService;

class PrestaShopEventService implements EventService
{
    public function subscribePlaceOrderEvent($rcallerClient, $logger)
    {
        // subscription should be triggered from the Module class
    }

    public function unsubscribePlaceOrderEvent()
    {
        // unsubscription should be triggered from the Module class
    }
}

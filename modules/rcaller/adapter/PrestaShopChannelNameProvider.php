<?php
namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\ChannelNameProvider;

class PrestaShopChannelNameProvider implements ChannelNameProvider
{
    public function getChannelName()
    {
        return "PrestaShop";
    }
}

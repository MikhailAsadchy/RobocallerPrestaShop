<?php

namespace rcaller\adapter;
use rcaller\lib\adapterInterfaces\OrderEntryFieldResolver;

class PrestaShopOrderEntryFieldResolver implements OrderEntryFieldResolver
{
    public function getName($item)
    {
        return $item["product_name"];
    }

    public function getQuantity($item)
    {
        return intval($item["product_quantity"]);
    }

    public function getUnit($item)
    {
        return "шт";
    }
}

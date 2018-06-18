<?php

namespace rcaller\adapter;
use Configuration;
use rcaller\lib\adapterInterfaces\OptionRepository;

class PrestaShopOptionRepository implements OptionRepository
{
    public function addOrUpdateOption($name, $value)
    {
        return Configuration::updateValue($name, $value);
    }

    public function removeOption($name)
    {
        return Configuration::deleteByName($name);
    }

    public function getOption($name)
    {
        return Configuration::get($name);
    }
}

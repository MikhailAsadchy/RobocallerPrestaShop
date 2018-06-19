<?php

use rcaller\adapter\PrestaShopAdaptedIOC;
use rcaller\adapter\RCallerAdapterImport;
use rcaller\lib\constants\RCallerConstants;
use rcaller\lib\RCallerImport;

include_once _PS_MODULE_DIR_ . "rcaller/RCallerConstants.php";
include_once _PS_MODULE_DIR_ . "rcaller/lib/RCallerImport.php";
include_once _PS_MODULE_DIR_ . "rcaller/adapter/RCallerAdapterImport.php";
RCallerImport::importRCallerLib();
RCallerAdapterImport::importRCallerLib();

if (!defined('_PS_VERSION_')) {
    exit;
}


class rcaller extends Module
{
    const PLACE_ORDER_HOOK_NAME = "actionObjectOrderHistoryAddAfter";

    public function __construct()
    {
        $this->name = 'rcaller';
        $this->displayName = 'RCaller client';
        $this->description = 'RCaller client description';
        $this->description_full = 'RCaller client full description';
        $this->additional_description = 'RCaller client additional description';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'RCaller';
        $this->controllers = array();
        $this->warning = $this->isModuleNeedsToBeConfigured();
        parent::__construct();
    }

    public function install()
    {
        $pluginManager = PrestaShopAdaptedIOC::getIOC()->getPluginManager();
        $pluginManager->addOptions();

        return parent::install()
            && $this->subscribePlaceOrderEvent();
    }

    /**
     * Handles place order event
     * @param $args
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionObjectOrderHistoryAddAfter($args)
    {
        $orderId = $args["object"]->id_order;
        $order = new Order($orderId);
        $address = $this->resolveAddress($order);

        $externalOrderId = $order->id;
        $totals = $order->getOrdersTotalPaid();
        $entries = $order->getProducts();
        $addressLine = $address->address1;
        $phone = $address->phone;
        $customerName = $this->getCustomerName($address);
        $currency = (new Currency($order->id_currency))->iso_code;

        $ioc = PrestaShopAdaptedIOC::getIOC();
        $rCallerClient = $ioc->getRCallerClient();
        $rCallerClient->sendOrderToRCaller($externalOrderId, $totals, $entries, $addressLine, $phone, $customerName, $currency);
    }

    public function uninstall()
    {
        $this->unregisterHook(self::PLACE_ORDER_HOOK_NAME);

        $ioc = PrestaShopAdaptedIOC::getIOC();
        $pluginManager = $ioc->getPluginManager();
        $pluginManager->removeOptions();

        return parent::uninstall();
    }

    /**
     * @return mixed - renders Settings page
     */
    public function getContent()
    {
        return PrestaShopAdaptedIOC::getIOC()->getRCallerSettingsPageRenderer()->getDefaultView();
    }

    /**
     * @param $order
     * @return Address
     */
    private function resolveAddress($order)
    {
        $address = null;
        if ($order->id_address_delivery != null) {
            $address = new Address($order->id_address_delivery);
        } else if ($order->id_address_invoice) {
            $address = new Address($order->id_address_invoice);
        } else {
            $ioc = PrestaShopAdaptedIOC::getIOC();
            $logger = $ioc->getLogger();
            $logger->log("error", "Can not resolve address from order with id=" . $order->id);
        }

        return $address;
    }

    private function getCustomerName($address)
    {
        return $address->firstname . " " . $address->lastname;
    }

    /**
     * @return bool
     */
    private function isModuleNeedsToBeConfigured()
    {
        $credentialsManager = PrestaShopAdaptedIOC::getIOC()->getCredentialsManager();

        $username = $credentialsManager->getUserName();
        $password = $credentialsManager->getPassword();

        return $username === RCallerConstants::OPTION_PLACE_HOLDER || $password === RCallerConstants::OPTION_PLACE_HOLDER;
    }

    /**
     * @return bool
     */
    private function subscribePlaceOrderEvent()
    {
        return $this->registerHook(self::PLACE_ORDER_HOOK_NAME);
    }


}

<?php

include_once _PS_MODULE_DIR_ . "rcaller/RCallerConstants.php";
include_once _PS_MODULE_DIR_ . "rcaller/RCallerSettingsPageRenderer.php";
include_once _PS_MODULE_DIR_ . "rcaller/client/RCallerSender.php";

if (!defined('_PS_VERSION_')) {
    exit;
}


class rcaller extends Module
{

    const CONFIG_PLACE_HOLDER = "changeMe";

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
        return parent::install()
            && $this->registerHook(self::PLACE_ORDER_HOOK_NAME)
            && Configuration::updateValue(RCallerConstants::USERNAME_CONFIG_KEY, self::CONFIG_PLACE_HOLDER)
            && Configuration::updateValue(RCallerConstants::PASSWORD_CONFIG_KEY, self::CONFIG_PLACE_HOLDER);
    }

    public function hookActionObjectOrderHistoryAddAfter($args)
    {
        $orderId = $args["object"]->id_order;

        $order = new Order($orderId);

        $address = $this->resolveAddress($order);

        $currency = new Currency($order->id_currency);

        $customerName = $this->getCustomerName($address);
        $entriesAsString = $this->getEntriesAsString($order);
        $data = array(
            'price' => $order->getOrdersTotalPaid(),
            'entries' => $entriesAsString,
            'customerAddress' => $address->address1,
            'customerPhone' => $address->phone,
            'customerName' => $customerName,
            'priceCurrency' => $currency->iso_code,
            'channel' => "PrestaShop");

        $userName = Configuration::get(RCallerConstants::USERNAME_CONFIG_KEY);
        $password = Configuration::get(RCallerConstants::PASSWORD_CONFIG_KEY);

        RCallerSender::sendOrderToRCallerInternal($data, $userName, $password);
    }

    public function uninstall()
    {
        $this->unregisterHook(self::PLACE_ORDER_HOOK_NAME);
        Configuration::deleteByName(RCallerConstants::USERNAME_CONFIG_KEY);
        Configuration::deleteByName(RCallerConstants::PASSWORD_CONFIG_KEY);

        return parent::uninstall();
    }

    public function getContent()
    {
        $renderer = new RCallerSettingsPageRenderer();
        return $renderer->render_settings_page();
    }

    /**
     * @param $order
     * @return Address
     * @throws Exception
     */
    private function resolveAddress($order)
    {
        if ($order->id_address_delivery != null) {

            $address = new Address($order->id_address_delivery);
        } else if ($order->id_address_invoice) {
            $address = new Address($order->id_address_invoice);

        } else {
            throw new Exception("Can not resolve address from order with id=" + $order->id);
        }

        return $address;
    }

    private function getCustomerName($address)
    {
        return $address->firstname . " " . $address->lastname;
    }

    private function getEntriesAsString($order)
    {
        $entriesAsStrings = [];
        foreach ($order->getProducts() as $item) {
            $name = $item["product_name"];
            $quantity = intval($item["product_quantity"]);
            $unit = "шт";
            $entryString = $name . " " . $quantity . " " . $unit . ".";
            array_push($entriesAsStrings, $entryString);
        }
        return join(" | ", $entriesAsStrings);
    }

    /**
     * @return bool
     */
    private function isModuleNeedsToBeConfigured()
    {
        $username = Configuration::get(RCallerConstants::USERNAME_CONFIG_KEY);
        $password = Configuration::get(RCallerConstants::PASSWORD_CONFIG_KEY);

        return $username === self::CONFIG_PLACE_HOLDER || $password === self::CONFIG_PLACE_HOLDER;
    }

}

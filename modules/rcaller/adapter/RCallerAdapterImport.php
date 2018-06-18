<?php

namespace rcaller\adapter;
use rcaller\lib\util\StrictImporter;

class RCallerAdapterImport
{
    public static function importRCallerLib()
    {
        $files = array();

        $currentFileLocation = dirname(__FILE__);
        array_push($files, $currentFileLocation . "/PrestaShopChannelNameProvider.php");
        array_push($files, $currentFileLocation . "/PrestaShopEventService.php");
        array_push($files, $currentFileLocation . "/PrestaShopLogger.php");
        array_push($files, $currentFileLocation . "/PrestaShopOptionRepository.php");
        array_push($files, $currentFileLocation . "/PrestaShopOrderEntryFieldResolver.php");
        array_push($files, $currentFileLocation . "/PrestaShopAdaptedIOC.php");

        StrictImporter::importFiles($files);
    }
}

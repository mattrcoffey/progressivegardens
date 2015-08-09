<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 6/16/15
 * Time: 10:36 PM
 */

        define('MAGENTO', realpath(dirname(__FILE__)));
        require_once MAGENTO . '/app/Mage.php';

        umask(0);
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $count = 0;

	$file = fopen('./var/import/family_images.csv', 'r');
	while (($line = fgetcsv($file)) !== FALSE) { $count++;
        //$line is an array of the csv elements

        $filename = 'media/import'.$line[1];

        if (!file_exists($filename)) {
            echo $line[0].' => ' .$line[1].'<br />';
        }

    }



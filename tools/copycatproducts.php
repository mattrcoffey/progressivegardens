<?php
/**
 * Created by PhpStorm.
 * User: lauracoffey
 * Date: 7/6/15
 * Time: 11:00 PM
 */

require_once ( "../app/Mage.php" );
umask(0);

// Initialize Magento
Mage::app();
$from = 43;
$to = 5;
$category = Mage::getModel('catalog/category');
$category->load($from); // Category id you want to copy
$collection = $category->getProductCollection();
$collection->addAttributeToSelect('*');
foreach ($collection as $product) {
    $product->getId();// Now get category ids and add a specific category to them and save?
    $categories = $product->getCategoryIds();
    if(!in_array($to,$categories)) {
        array_push($categories, $to); // Category id you want to add
        $product->setCategoryIds($categories);
        //$product->save();
        echo $product->getSku() . ' added to '.$to.' <br />';
    }

}
?>
<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 6/23/15
 * Time: 1:10 AM
 */

require_once '../app/Mage.php';
Mage::app('default');

$collection  =  Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('type_id','configurable');

foreach($collection as $product)
{
    $categoryIds = [];
    //get assoc. products
    //$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
    $associatedProducts = Mage::getModel('catalog/product_type_configurable')
        ->getUsedProducts(null,$product);
    foreach($associatedProducts as $_product) {
        $_product->setStoreId(1);
        $_product->setVisibility(3);
        $_product->getResource()->saveAttribute($_product, 'visibility');
        echo $_product->getSku() .' hidden from catalog <br />';
    }
}
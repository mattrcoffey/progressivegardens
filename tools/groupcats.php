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
    $_Product = Mage::getModel("catalog/product")->load( $associatedProducts[0]["entity_id"]);
    $categoryIds = $_Product->getCategoryIds();//array of product categories

    array_push($categoryIds,'2');
    $product->setCategoryIds($categoryIds);
    //$product->save();

    echo  'Set '.$product->getSku() .' =  '.implode($categoryIds, ',');
}
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
$collection->addAttributeToFilter('sku','29p1h');
foreach($collection as $product)
{
    $categoryIds = [];
    //get assoc. products
    //$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
    $associatedProducts = Mage::getModel('catalog/product_type_configurable')
        ->getUsedProducts(null,$product);
    $kid = Mage::getModel("catalog/product")->load( $associatedProducts[0]["entity_id"]);

    $image = Mage::getBaseDir('media') . DS . 'catalog/product' . $kid->getImage();
    echo $image;
    $mediaAttribute = array (
        'image',
        'thumbnail',
        'small_image'
    );
    try {
        $product->addImageToMediaGallery($image, array('image','small_image','thumbnail'), false, false);
    }
    catch (Exception $e) { echo $e;}

    echo $product->getId();
    //echo $prod->getName();
    $product->save();

    //echo  'Set '.$product->getSku() .' =  '.implode($categoryIds, ',');
}
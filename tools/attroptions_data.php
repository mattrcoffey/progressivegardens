<?php
$store_id = 1;
require_once '../app/Mage.php';
Mage::app('default');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

function addAttributeOption($arg_attribute, $arg_value) {
    $attribute_model        = Mage::getModel('eav/entity_attribute');
    $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
 
    $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
    $attribute              = $attribute_model->load($attribute_code);
 
    $attribute_table        = $attribute_options_model->setAttribute($attribute);
    $options                = $attribute_options_model->getAllOptions(false);
 
    $value['option'] = array($arg_value,$arg_value);
    $result = array('value' => $value);
    $attribute->setData('option',$result);
    $attribute->save();
 
    return getAttributeOptionValue($arg_attribute, $arg_value);
}

function getAttributeOptionValue($arg_attribute, $arg_value) {
    $attribute_model        = Mage::getModel('eav/entity_attribute');
    $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
 
    $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
    $attribute              = $attribute_model->load($attribute_code);
 
    $attribute_table        = $attribute_options_model->setAttribute($attribute);
    $options                = $attribute_options_model->getAllOptions(false);
 
    foreach($options as $option) {
        if ($option['label'] == $arg_value) {
            return $option['value'];
        }
    }
 
    return false;
}

function addCategory($sku, $catID) {
	static $cnt = 0;
	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
	if($product) {
		$existingcategories = $product->getCategoryIds();
		if(in_array($catID, $existingcategories)) {
			echo 'product '. $sku .' was already in the category with the ID of '.$catID.'<br />';
		} else {
			$together = array_push($existingcategories,$catID);
			$product->setCategoryIds($existingcategories);
		    $product->save();
			echo 'product '. $sku .' was added to cat ID - '.$catID.'<br />';
		}
	} else {
		$cnt++;
		echo '<font color="red">'.$cnt.' - no product exists for sku - '.$sku.'</font><br />';
		
	}
	
	//var_dump($existingcategories);
}

if($_GET["action"] == 'addOption') {
	$attr = $_GET["attr"];
	$option = $_GET["q"];
	
	$newOption = getAttributeOptionValue($attr, $option);
	//get options is not in list
	if($newOption == '') {
		$newOption = addAttributeOption($attr, $option);
		echo 'option '. $option .' added <br />';
	} else {
		echo 'option '. $option .' is already in the list <br />';
	}
	
	
}

if($_GET["action"] == 'changeAttrSetSku') {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$attr = $_GET["attr"];
	$option = $_GET["q"];
	
	$newOption = getAttributeOptionValue($attr, $option);
	$sql ="UPDATE `catalog_product_entity` SET `attribute_set_id`={$attr} WHERE `sku` = \"{$option}\" ";
	
	// now $write is an instance of Zend_Db_Adapter_Abstract
	$readresult=$write->query($sql);
	
	$sql ="SELECT `attribute_set_id` as attr FROM `catalog_product_entity` WHERE `sku` = \"{$option}\" ";
	
	// now $write is an instance of Zend_Db_Adapter_Abstract
	$readresult=$write->query($sql);
	$r = $readresult->fetch();

	if($r[attr] == $attr) {
		echo $option .' attribute set changed to '.$attr.' <br />';
	} else {
		echo $option .' attribute set not changed <br />';
	}
	
	
}

if($_GET["action"] == 'changeAttrSetCat') {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$attr = $_GET["attr"];
	$option = $_GET["q"];
	
	$newOption = getAttributeOptionValue($attr, $option);
	$sql ="UPDATE `catalog_product_entity` as a LEFT JOIN `catalog_category_product` as b ON a.entity_id = b.product_id SET `attribute_set_id`={$attr} WHERE b.`category_id` = \"{$option}\" ";
	
	// now $write is an instance of Zend_Db_Adapter_Abstract
	$readresult=$write->query($sql);
	
	$sql ="SELECT DISTINCT `attribute_set_id` as attr FROM `catalog_product_entity` as a LEFT JOIN `catalog_category_product` as b ON a.entity_id = b.product_id WHERE b.`category_id` = \"{$option}\" ";
	
	// now $write is an instance of Zend_Db_Adapter_Abstract
	$readresult=$write->query($sql);
	$r = $readresult->fetch();

	if($r[attr] == $attr) {
		echo 'Attribute set changed to '.$attr.' for all products with Category ID =' .$option .' <br />';
	} else {
		echo 'All products with Category ID =' .$option .' attribute set not changed <br />';
	}
	
	
}

if($_GET["action"] == 'related') {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$attr = $_GET["attr"];
	$option = $_GET["q"];
	
	$products = Mage::getModel('catalog/product')
    ->getCollection()
    ->addFieldToFilter('attribute_set_id', $attr);
    
    
	foreach($products as $p){
		$relatedData = array();
		foreach ($p->getRelatedLinkCollection() as $link) {
		    $relatedData[$link->getLinkedProductId()]= array('position' => $link->getPosition());
		}
		$arr[$option]["position"] = 0;
		$relatedData[$option] = array('position' => 0);
		echo 'Added related product id: '.$option.' to sku: '.$p->getSku() .'<br />';
		//$p->setRelatedLinkData($relatedData);
		//$p->save();
	}
	
}

if($_GET["action"] == 'relatedbysku') {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$resource   = Mage :: getSingleton( 'core/resource' );

	$productId = $_GET["sku"];
	$linkProduct = $_GET["q"];
	$type = $_GET["type"];
	$linkTable=$resource->getTableName('catalog/product_link');

	$p = Mage::getModel('catalog/product')->load($productId);
	if($p) {
		/* too slow
		$relatedData = array();
		foreach ($p->getRelatedLinkCollection() as $link) {
		    $relatedData[$link->getLinkedProductId()]= array('position' => $link->getPosition());
		}
		$arr[$option]["position"] = 0;
		$relatedData[$option] = array('position' => 0);
		//print_r($relatedData);
		$p->setRelatedLinkData($relatedData);
		$p->save();
		*/
		//check if already related product
		$sql ="SELECT * FROM $linkTable WHERE `product_id` = '$productId' AND linked_product_id = '$linkProduct' AND link_type_id='$type'";

		$readresult=$write->query($sql);
		$r = $readresult->fetch();

		//if not already a related product
		if(!$r) {
			$write->query("INSERT into $linkTable SET
	                            product_id='$productId',
	                            linked_product_id='$linkProduct',
	                            link_type_id='$type'");
			echo 'Added related product id: '.$linkProduct.' to sku: '.$p->getSku() .' ('.$p->getId().')<br />';
		} else {
			echo 'Related product id: '.$linkProduct.' is already linked to sku: '.$p->getSku() .' ('.$p->getId().')<br />';
		}
	}

}

if($_GET["action"] == 'update') {
	$wheel= $_GET["wheel"];
	$width = $_GET["width"];
	$aspect = $_GET["aspect"];
	$id = $_GET["id"];
	$product = Mage::getModel('catalog/product')->load($id);
	
	
	$newWheel = '';
	$newWidth = '';
	$newAspect = '';
	
	if($wheel != '') {
		//add wheel option if new
		$newWheel = getAttributeOptionValue('tire_rim_diameter', $wheel);
		//get options is not in list
		if($newWheel == '') {
			$newWheel = addAttributeOption('tire_rim_diameter', $wheel);
		} 
	}
	
	if($width != '') {
		//add width option if new
		$newWidth = getAttributeOptionValue('size_section_width', $width);
		//get options is not in list
		if($newWidth == '') {
			$newWidth = addAttributeOption('size_section_width', $width);
		} 
	}
	
	if($aspect != '') {
		//add aspect option if new
		$newAspect = getAttributeOptionValue('size_sidewall_aspect_ratio', $aspect);
		//get options is not in list
		if($newAspect == '') {
			$newAspect = addAttributeOption('size_sidewall_aspect_ratio', $aspect);
		} 
	}
	
	
	//add option to product
	
	$product->setTireRimDiameter($newWheel)
		->setSizeSectionWidth($newWidth)
		->setSizeSidewallAspectRatio($newAspect)
		->save();
	
	echo 'X';
}

if($_GET["action"] == 'addCat') {
	$catID = $_GET["catid"];
	$option = $_GET["q"];
	
	$r = addCategory($option, $catID);
	//get options is not in list
	echo $r;
	
	
}

if($_GET["action"] == 'chgSku') {
	$sku = $_GET["sku"];
	$newSku = str_replace('HC-', 'HD-', $sku);
	
	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
	if($product) {
		$product->setSku($newSku);
	    $product->save();
		echo 'sku '. $sku .' was changed to '.$newSku.'<br />';
	} else {
		echo 'no product with sku - '. $sku .' can be found.<br />';
	}
	
	
}

if($_GET["action"] == 'getskusattrset') {
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	$attr = $_GET["attr"];
	$option = $_GET["q"];
	
	$products = Mage::getModel('catalog/product')
    ->getCollection()
    ->addFieldToFilter('attribute_set_id', $attr);
    
    
	foreach($products as $p){
		$t .= $p->getId() .',';
	}
	echo substr($t, 0, -1);
	
}


if($_GET["action"] == 'setETP') {
	$sku = $_GET["sku"];
	$etp = $_GET['etp'];
	
	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
	if($product) {
		$product->setEtpEligible($etp);
	    $product->save();
		echo 'sku '. $sku .' etp eligiblity was set to '.$etp.'<br />';
	} else {
		echo 'no product with sku - '. $sku .' can be found.<br />';
	}
	
	
}

if($_GET["action"] == 'flatratebysku') {
    $write = Mage::getSingleton('core/resource')->getConnection('core_write');
    $resource   = Mage :: getSingleton( 'core/resource' );

    $sku = $_GET["sku"];
    $rate = $_GET["q"];

    $product = Mage::getModel('catalog/product')->load($sku);
    if($product && $product->getTypeId() == 'simple' ) {
        $product->setShippingRate($rate);
        $product->save();
        echo 'id '. $sku .' flat shipping rate set to '.$rate.'<br />';
    } else {
        echo 'no simple product with id - '. $sku .' can be found.<br />';
    }

}

<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 Mage_Adminhtml_Controller_Action
*/
class Webgility_Ecc_WcpinstallController extends Mage_Core_Controller_Front_Action {  	  
	
	
	
	public function installbtnAction()
	{
			
			$email = Mage::app()->getRequest()->getParam('Email')?Mage::app()->getRequest()->getParam('Email'):"";
			$Token = Mage::app()->getRequest()->getParam('Token');
			$StoreModuleURL = Mage::app()->getRequest()->getParam('StoreModuleURL');
			$type = Mage::app()->getRequest()->getParam('type');
			$storeid = Mage::app()->getRequest()->getParam('StoreId')?Mage::app()->getRequest()->getParam('StoreId'):"";
			
			$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
			$SetIds=$eavSetId->query("SELECT * FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE));
			$row = $SetIds->fetch();
			if($row)
			{
				$SetIds=$eavSetId->query("update `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` set wcstoremodule = '".$StoreModuleURL."', `email`='".$email."' ,`token` ='".$Token."' , `status` =".$type." where store_id = '".$storeid."'");
			}else
			{
				$eavSetId->query("insert into ".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."(`email`,`token`,`store_id`,`wcstoremodule`,`status`,`created_time`,`update_time` ) values ('".$email."','".$Token."','".$storeid."','".$StoreModuleURL."','".$type."','".time()."','".time()."')");
			}
			Mage::helper('ecc')->wcTrackStage1();
			$result_array= array('status'=>$type);
			echo json_encode($result_array);
	}
	
	
	
}
	



?>
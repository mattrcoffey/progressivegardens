<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/


class Webgility_Ecc_Model_EccApi
{
		
	    public function savepac($product_api,$api_url,$table_name) 
		{
				$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
				$SetIds=$eavSetId->query("SELECT * FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` ");
				$row = $SetIds->fetch();
				if($row['id']=="")
				{
					$eavSetId->query("insert into ".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."(`Token`,`wcstoremodule`,`status` ) values ('".$_REQUEST['pac']."','',1)");
					$arr['StatusMessage'] =	'Successfully Installed.';
					$arr['StatusCode'] =	'success';
				
				}else {
					$eavSetId->query("update ".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)." SET `Token` = '".$_REQUEST['pac']."' where `status`= 1");
					$arr['StatusMessage'] =	'Successfully Installed.';
					$arr['StatusCode'] =	'success';
				}
				
				echo $_GET['callback']."(".json_encode($arr).");";  // 09

	}
	
}
?>
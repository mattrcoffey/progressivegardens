<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Helper_Data extends Mage_Core_Helper_Abstract
{

 
    /**
     * Internal parameters for validation
     */
    protected $_connect_config_table           = 'connect_config';
    protected $_connect_qb_orders_table        = 'connect_qb_orders';
	protected $_connect_env        			   = '';
    const CONNECT_ENV   			= '';

	const STORE_MODULE_VERSION  = '403';
	const CONNECT_CONFIG_TABLE      = 'connect_config';
    const CONNECT_QB_ORDERS_TABLE   = 'connect_qb_orders';
    
    const REWARDS_POINT_NAME  = 'RewardsPoints';
    const SET_CAPTURE_CASE  = false;
    const SET_SPECIAL_PRICE  = false;
    const SET_SHORT_DESC  = false;
    const DISPLAY_DISCOUNT_DESC  = true;
    const SET_REORDER_POINT  = false;
    const GET_ACTIVE_CARRIER  = false;
    const CART_NAME_UPGRADE  = 'magento';
    	
	const MESSAGE_NA = 'Not yet posted.'; 
	const MESSAGE_SUCCESS_POSTED_DETAILS = 'Order is successfully posted to QuickBooks #TRANSACTION_TYPE# No. #TRANSACTIONNO# is generated.';
	const MESSAGE_SUCCESS_POSTED = 'Order posted to QB. #INVOICE# # #TRANSACTION# generated.';
	const MESSAGE_ERROR_POSTED = '<font color="#FF0000" >QB Sync Failed. For error details go to the Order details page.</font>';
	const MESSAGE_QUEUED = 'Order is queued for posting.';
	const MESSAGE_QUEUED_DETAILS = 'Order is now queued for posting Click #REFRESH# to get the updated status.';
	const MESSAGE_ERROR_AUTH  = 'You cannot post orders to QuickBooks because your eCC Cloud trial or subscription period ended, or you canceled your subscription, or there was a billing problem. Get your eCC Cloud account activated to start posting orders again.';
	
	function getUrl($methods)
	{
		
		$api_methods = array ('GetTransactionStatus'=>'OrderStatus','PostOrderQB'=>'Order','AddStage'=>'Staging');		
		switch ($this->_connect_env) {
			case 'dev':
				$api_url =  'https://ecctest.webgility.com/API/api/'.$api_methods[$methods];
				break;
			case 'staging':
				$api_url =  'https://eccstaging.webgility.com/API/api/'.$api_methods[$methods];
				break;
			default:
				$api_url =  'https://ecc.webgility.com/API/api/'.$api_methods[$methods];
				break;
		}
		return $api_url;	
	}
	
	function callApi($url,$parameters)
	{

		$ch = curl_init();
		$data = http_build_query($parameters);
		
		$wcPac = $this->wcGetPac($parameters['OrderNo']);
		if(!$wcPac)
		{
			echo 'No token found';
			exit();
		}
		//echo $url.'?'.$data;
		curl_setopt($ch, CURLOPT_URL,$url.'?'.$data);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER,  array(
													'content-length:'.strlen($data),													
													'Authorization:Basic '.$wcPac	
													));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
		if(isset($server_output) && strlen($server_output)>0)
		{
			return $server_output;
			exit();			
		}else
		{
				
			return 'Error';
		}
	}
	
	function wcGetPac($IncrementId)
	{
		$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
		if(isset($IncrementId)&& $IncrementId!='')
		{
			$order = Mage::getModel('sales/order')->load($IncrementId, 'increment_id');
   			$order_store = $order->getStoreId();
			$SetIds=$eavSetId->query("SELECT token FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` where status = 1 ");
		}else
		{
			$SetIds=$eavSetId->query("SELECT token FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` ");
		}
		$row = $SetIds->fetch();
		
		if($row['token'])
		{
			return $row['token'];
		}else
		{
			return false;
		}					
	}
		function wcGetOrderStatusAtStore($IncrementId)
	{
		
		$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
		$SetIds=$eavSetId->query("SELECT qb_status FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_QB_ORDERS_TABLE)."` where orderid = ".$IncrementId."  ");
	//	$SetIds=$eavSetId->query("SELECT token FROM `".$this->$_connect_config_table ."` where `orderid` ='".$v['OrderNo']."'");
		$row = $SetIds->fetch();
		
		if($row['qb_status'])
		{
			return $row['qb_status'];
		}else
		{
			return false;
		}					
	}
	function wcGetWidgetStatus()
	{
		$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
		$SetIds=$eavSetId->query("SELECT token FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` where status = 1 ");
	//	$SetIds=$eavSetId->query("SELECT token FROM `".$this->$_connect_config_table ."` where `orderid` ='".$v['OrderNo']."'");
		$row = $SetIds->fetch();
		if($row['token'])
		{
			return $row['token'];
		}else
		{
			return false;
		}					
	}
	public function getMessageByOrderForDetails($incrementid)
	{
		
		
		$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
		$SetIds=$eavSetId->query("SELECT * FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_QB_ORDERS_TABLE)."` where orderid = ".$incrementid);
		$row = $SetIds->fetch();
		$row['TransactionMsg']= $row['TransactionMsg']? $row['TransactionMsg']:Webgility_Ecc_Helper_Data::MESSAGE_NA;
		if(strtolower($row['qb_status'])=='posted')
		{
			$str = str_replace('#TRANSACTION_TYPE#',$row['transaction_type'],Webgility_Ecc_Helper_Data::MESSAGE_SUCCESS_POSTED_DETAILS);
			$str = str_replace('#TRANSACTIONNO#',$row['qb_transactionNumber'],$str);
			return $str; 
		}elseif(strtolower($row['qb_status'])=='queued')
		{
		
			$api_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."index.php/ecc/wcp/posttoqb"; 
			$_order_id = Webgility_Ecc_Block_Sales_Order_View::getOrder()->getIncrementId();
			$str = Webgility_Ecc_Helper_Data::MESSAGE_QUEUED_DETAILS;
			$str = str_replace('#REFRESH#','<a href="#" onclick="update_portal_details(\''.$_order_id.'\',\''.$api_path .'\');" >refresh</a>',Webgility_Ecc_Helper_Data::MESSAGE_QUEUED_DETAILS);
		//	$str = $str. $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')->setClass('scalable')->setLabel('Refresh')->setOnClick("update_portal('".$value."','".$base_path."')")->toHtml();
			
			return $str; 
		}		
		$str = "<div id='qbstatus_".$incrementid."'>".$row['TransactionMsg']."</div>";
		return $str;		
	}
	
		function wcTrackStage1()
	{
		$params['stagingName']='Enableconnect';
		$params['stagingStatus']=1;
		$params['provider']='button';
		$params['stagingDetails']='The user has enabled the magento widget on his store admin page & is ready to post the orders from store';
		//$params = http_build_query($params);
		//$params =  $this->getParams(1,$data,$postedId);
		$url =  Mage::helper('ecc')->getUrl('AddStage');		 
		$response =  Mage::helper('ecc')->callApi($url,$params);
		
	} 
		function wcTrackStage3()
	{
		$params['stagingName']='DisableConnect';
		$params['stagingStatus']=3;
		$params['provider']='button';
		$params['stagingDetails']='The user was earlier an active magento widget user but has now disconnected widget.';
		//$params = http_build_query($params);
		//$params =  $this->getParams(1,$data,$postedId);
		$url =  Mage::helper('ecc')->getUrl('AddStage');
		$response =  Mage::helper('ecc')->callApi($url,$params);
	}
	
}
?>
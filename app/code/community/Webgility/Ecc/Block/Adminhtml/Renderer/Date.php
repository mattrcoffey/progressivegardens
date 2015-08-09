<?php

class Webgility_Ecc_Block_Adminhtml_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function __construct()
		{
			parent::__construct();
			$this->_formScripts[] = "function update_portal(){alert('hello');} ";
			
		}
		
	public function render(Varien_Object $row)
    {
		 $widget_status = Webgility_Ecc_Helper_Data::wcGetWidgetStatus();
		 if(!$widget_status)
		 {
		 	return '';		 
		 }
		 		$successmessage = Webgility_Ecc_Helper_Data::MESSAGE_SUCCESS_POSTED;
		$errormessage   = Webgility_Ecc_Helper_Data::MESSAGE_ERROR_POSTED;
			
		$value = $this->_getValue($row);
		$base_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."ecc/wcp/posttoqb"; 
        			
		//die();
		$img_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'downloader/skin/images/qbsync-long.png';
		$order_url =  Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=>$row->entity_id));
		
		$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
		$SetIds=$eavSetId->query("SELECT * FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_QB_ORDERS_TABLE)."` where orderid ='".$value."' ");
		
		$row = $SetIds->fetch();
		
		if(strtolower($row['qb_status'])=='queued')
		{
			$img = Webgility_Ecc_Helper_Data::MESSAGE_QUEUED;
			$img = $img. $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')->setClass('scalable')->setLabel('Refresh')->setOnClick("update_portal('".$value."','".$base_path."')")->toHtml();
			
		}elseif($row['qb_transactionNumber']!='' && strtolower($row['qb_status'])!='error')
		{
			$successmessage = str_replace('#INVOICE#',$row["transaction_type"],$successmessage);
			$successmessage = str_replace('#TRANSACTION#',$row["qb_transactionNumber"],$successmessage);			
			$img = $successmessage;
		}elseif($row['qb_status']=='Error')
		{
			$img = $errormessage;
			$img = $img.$this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')->setClass('error')->setLabel('Try Again')->setOnClick("update_portal('".$value."','".$base_path."')")->toHtml();	
		}
		else		
		{
			$img = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')->setClass('scalable')->setLabel('QB Sync')->setOnClick("update_portal('".$value."','".$base_path."')")->toHtml();		
		}
		
		$data = $img;
		return $data;
    }
}

?> 

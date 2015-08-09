<?php 

    class Webgility_Ecc_Block_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
    {
        protected function _construct()
        {
          
		   $this->setTemplate('ecc/sales/order/view/info.phtml');
        }     
    }
    
?>
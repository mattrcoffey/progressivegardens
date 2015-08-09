<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_AdminController extends Mage_Adminhtml_Controller_Action{        
    
	public function indexAction() {
			$this->loadLayout();
			//Mage::app()->getLayout()->getBlock('head')->addJs('webgility/ecc/magento-connect.js');
			$this->renderLayout();	
	}
	
	public function eccaboutAction()
	{
		$this->loadLayout();			
		$this->_addLeft($this->getLayout()
		->createBlock('core/text')
		->setText('&nbsp;'));
		
		$block = $this->getLayout()
		->createBlock('core/text')
		->setText(Mage::getSingleton('ecc/EccWgBaseResponse')->message());           
		$this->_addContent($block);
		$this->renderLayout();
				
	}	

/*	public function eccconfigAction()
	{
		$product_api = 'button';
		$table_name = 'connect_config';
	
		$this->loadLayout();			
		$this->_addLeft($this->getLayout()
		->createBlock('core/text')
		->setText('&nbsp;'));
		
		$block = $this->getLayout()
		->createBlock('core/text')
		->setText(Mage::getSingleton('ecc/EccConfig')->message($product_api,$table_name));           
		$this->_addContent($block);
		$this->renderLayout();
				
	}
		*/
}
?>
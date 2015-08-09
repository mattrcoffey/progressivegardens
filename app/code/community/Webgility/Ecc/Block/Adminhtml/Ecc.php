<?php
     
    class Webgility_Ecc_Block_Adminhtml_Ecc extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller = 'adminhtml_ecc';
            $this->_blockGroup = 'ecc';
            $this->_headerText = Mage::helper('ecc')->__('Token Manager');
			
            $this->_addButtonLabel = Mage::helper('ecc')->__('Add Token');
            parent::__construct();
			//$this->removeButton('add');
        }
    }
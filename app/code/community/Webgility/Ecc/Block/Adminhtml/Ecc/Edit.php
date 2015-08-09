<?php
     
    class Webgility_Ecc_Block_Adminhtml_Ecc_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
    {
        public function __construct()
        {
            parent::__construct();
                   
            $this->_objectId = 'id';
            $this->_blockGroup = 'ecc';
            $this->_controller = 'adminhtml_ecc';
     
            $this->_updateButton('save', 'label', Mage::helper('ecc')->__('Save Configuration'));
            $this->_updateButton('delete', 'label', Mage::helper('ecc')->__('Delete Configuration'));
        }
     
        public function getHeaderText()
        {
            if( Mage::registry('ecc_data') && Mage::registry('ecc_data')->getId() ) {
                return Mage::helper('ecc')->__("Edit Configuration '%s'", $this->htmlEscape(Mage::registry('ecc_data')->getTitle()));
            } else {
                return Mage::helper('ecc')->__('Add Configuration');
            }
        }
    }
<?php
     
    class Webgility_Ecc_Block_Adminhtml_Ecc_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
    {
     
        public function __construct()
        {
            parent::__construct();
            $this->setId('ecc_tabs');
            $this->setDestElementId('edit_form');
            $this->setTitle(Mage::helper('ecc')->__('New Configuration'));
        }
     
        protected function _beforeToHtml()
        {
            $this->addTab('form_section', array(
                'label'     => Mage::helper('ecc')->__('Config Information'),
                'title'     => Mage::helper('ecc')->__('Config Information'),
                'content'   => $this->getLayout()->createBlock('ecc/adminhtml_ecc_edit_tab_form')->toHtml(),
            ));
           
            return parent::_beforeToHtml();
        }
    }
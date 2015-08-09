<?php
     
    class Webgility_Ecc_Block_Adminhtml_Ecc_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
    {
        protected function _prepareForm()
        {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset('ecc_form', array('legend'=>Mage::helper('ecc')->__('Configuration')));
           
            $fieldset->addField('store_id', 'text', array(
                'label'     => Mage::helper('ecc')->__('Store Id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'store_id',
            ));
			$fieldset->addField('email', 'text', array(
                'label'     => Mage::helper('ecc')->__('email'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'email',
            ));
			$fieldset->addField('token', 'text', array(
                'label'     => Mage::helper('ecc')->__('Token'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'token',
            ));
			  $fieldset->addField('wcstoremodule', 'text', array(
                'label'     => Mage::helper('ecc')->__('Store Module'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'wcstoremodule',
            ));
      
            $fieldset->addField('status', 'select', array(
                'label'     => Mage::helper('ecc')->__('Status'),
                'name'      => 'status',
                'values'    => array(
                    array(
                        'value'     => 1,
                        'label'     => Mage::helper('ecc')->__('Active'),
                    ),
     
                    array(
                        'value'     => 0,
                        'label'     => Mage::helper('ecc')->__('Inactive'),
                    ),
                ),
            ));
           
            /*$fieldset->addField('content', 'editor', array(
                'name'      => 'content',
                'label'     => Mage::helper('ecc')->__('Content'),
                'title'     => Mage::helper('ecc')->__('Content'),
                'style'     => 'width:98%; height:400px;',
                'wysiwyg'   => false,
                'required'  => true,
            ));
           */
            if ( Mage::getSingleton('adminhtml/session')->getEccData() )
            {
                $form->setValues(Mage::getSingleton('adminhtml/session')->getEccData());
                Mage::getSingleton('adminhtml/session')->setEccData(null);
            } elseif ( Mage::registry('ecc_data') ) {
                $form->setValues(Mage::registry('ecc_data')->getData());
            }
            return parent::_prepareForm();
        }
    }
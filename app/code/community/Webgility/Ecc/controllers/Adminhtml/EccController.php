<?php
     
	class Webgility_Ecc_Adminhtml_EccController extends Mage_Adminhtml_Controller_Action
    {
     
        protected function _initAction()
        {
		     	$this->loadLayout()
                ->_setActiveMenu('ecc/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            return $this;
        }   
       
        public function indexAction() {
		
       		
			$this->_initAction();       
         	$this->_addContent($this->getLayout()->createBlock('ecc/adminhtml_ecc'));
            
			$this->renderLayout();
			
        }
     
        public function editAction()
        {
          	$eccId     = $this->getRequest()->getParam('id');
            $eccModel  = Mage::getModel('ecc/ecc')->load($eccId);
     
            if ($eccModel->getId() || $eccId == 0) {
     
                Mage::register('ecc_data', $eccModel);
     
                $this->loadLayout();
                $this->_setActiveMenu('ecc/items');
               
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
               
                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
               
                $this->_addContent($this->getLayout()->createBlock('ecc/adminhtml_ecc_edit'))
                     ->_addLeft($this->getLayout()->createBlock('ecc/adminhtml_ecc_edit_tabs'));
                   
                $this->renderLayout();
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecc')->__('Item does not exist'));
                $this->_redirect('*/*/');
            }
        }
       
        public function newAction()
        {
		
            $this->_forward('edit');
        }
       
        public function saveAction()
        {
            if ( $this->getRequest()->getPost() ) {
                try {
                    $postData = $this->getRequest()->getPost();
                    $eccModel = Mage::getModel('ecc/ecc');
                   $currentTimestamp = now(); 
                    $eccModel->setId($this->getRequest()->getParam('id'))
                        ->setToken($postData['token'])
						->setEmail($postData['email'])
                        ->setStoreId($postData['store_id'])
						->setWcstoremodule($postData['wcstoremodule'])
                        ->setUpdateTime($currentTimestamp)
						->setCreatedTime($currentTimestamp)
						->setStatus($postData['status'])
                        ->save();
                   
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Configuration successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setEccData(false);
     				if(isset($postData['status']) && $postData['status']==1)
					{
						
						Mage::helper('ecc')->wcTrackStage1();
					}else
					{
						
						Mage::helper('ecc')->wcTrackStage3();
					}
					
                    $this->_redirect('*/*/');
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setEccData($this->getRequest()->getPost());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }
            $this->_redirect('*/*/');
        }
       
        public function deleteAction()
        {
            if( $this->getRequest()->getParam('id') > 0 ) {
                try {
                    $eccModel = Mage::getModel('ecc/ecc');
                   
                    $eccModel->setId($this->getRequest()->getParam('id'))
                        ->delete();
                       
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Configuration was successfully deleted'));
                    $this->_redirect('*/*/');
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                }
            }
            $this->_redirect('*/*/');
        }
        /**
         * Product grid for AJAX request.
         * Sort and filter result for example.
         */
        public function gridAction()
        {
            $this->loadLayout();
            $this->getResponse()->setBody(
                   $this->getLayout()->createBlock('ecc/adminhtml_ecc_grid')->toHtml()
            );
        }
    }
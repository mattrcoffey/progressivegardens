<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webgility_Ecc_Block_Sales_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
	    $this->_controller = 'sales_order';
        $this->_headerText = Mage::helper('sales')->__('Orders');
       	$api_path = trim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."ecc/wcp/managebtn"); 
		$btntext = 'Enable QB Sync';
		$btnfnc = 'update_store(\'1\',\'\',\''.$api_path.' \');';
		$class  = 'disabled';
		//$class  = 'enable';
		if(trim(Mage::helper('ecc')->wcGetWidgetStatus())!='')
		{
			$btntext = 'Disable QB Sync';
			$btnfnc = 'update_store(\'0\',\'\',\''.$api_path.' \');';
			//$class  = 'disabled';
			$class  = 'enable';
		}
		
		$this->_addButton('testbutton', array(
            'label'     => Mage::helper('Sales')->__($btntext),
            'onclick'   => $btnfnc,
            'class'     => $class,
		), 10, 100, 'header', 'header');
		
		$this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');	   
        
		parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_removeButton('add');
        }
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }

}

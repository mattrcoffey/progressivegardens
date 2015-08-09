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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webgility_Ecc_Block_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {
    public function  __construct() {
        parent::__construct();
		$this->createButton();
		}
	
	public function createButton()
	{
			$widget_status = Webgility_Ecc_Helper_Data::wcGetWidgetStatus();
			if(!$widget_status)
			{
				return false;
			}
			$api_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."ecc/wcp/posttoqb"; 
			$_order_id = $this->getOrder()->getIncrementId();
			$class = 'enable';
			if(Mage::helper('ecc')->wcGetOrderStatusAtStore($_order_id)=='Posted')
			{
				$class = 'disabled';
			}
			
			$this->_addButton('button_id', array(
            'label'     => Mage::helper('ecc')->__('QB Sync'),
            'onclick'   => 'update_portal_details('.$_order_id.',\''.$api_path.'\')',
            'class'     => $class,
			//'class'     => 'disabled'
        	), 0, 100, 'header', 'header');
	}	
}
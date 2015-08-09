<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 Mage_Adminhtml_Controller_Action
*/
class Webgility_Ecc_WcpController extends Mage_Adminhtml_Controller_Action {  	  
	
	public function multiplepostotqbAction($api_url)
	{
		$postedIds = Mage::app()->getRequest()->getParam('order_ids');
		//$postedIds = explode(',',$postedIds);
		if(is_array($postedIds))
		foreach($postedIds as $k=>$v)
		{
			$order = Mage::getModel('sales/order'); $order->load($v); 
			$orderIncrementId = $order->getIncrementId(); 
			$type = 'return';
			
		
			$response = $this->posttoqbAction($api_url,$orderIncrementId,$type);
			
			$response = json_decode($response,true);
			
			if (strpos(strtolower($response['StatusCode']),'error') !== false) {
				//Mage::getSingleton('adminhtml/session')->addError($response['message']."123");
				Mage::getSingleton('core/session')->addError(Webgility_Ecc_Helper_Data::MESSAGE_ERROR_AUTH);
				 break;
				
				/*echo "<script>alert('".$response['message']."');
				//window.location.href = '".Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/", array())."'
				</script>";
				*/
			}
			
			
				
			
			
		}
			

		$this->_redirect("adminhtml/sales_order/");
		return $this;
				
		
	}
	
	public function posttoqbAction($api_url,$id,$type)
	{
		
		$postedId = $id ? $id :Mage::app()->getRequest()->getParam('id');	
		$method = 'PostOrderQB';
		$data =  $this->getOrders($postedId);
		$params =  $this->getParams($postedId,$data);
		$url =   Mage::helper('ecc')->getUrl($method);
		
		
		$response =   Mage::helper('ecc')->callApi($url,$params);
		$response = json_decode($response,true);
		
		$code = strtolower($response['StatusCode']);
		
		if (strpos($code,'error') !== false) {
		//$reponce['error']= true;
			$reponce['StatusCode']= $response['StatusCode'];
			$reponce['message']= $response['StatusMessage'];
			//Mage::getSingleton('core/session')->addError($response['StatusMessage']);
			//Commented on request of vivek to remove inline message.
			//Mage::getSingleton('adminhtml/session')->addError($response['StatusMessage']);
			
			if($type=='return')
			{
				return json_encode($reponce);
				exit();
			}else
			{
				echo json_encode($reponce);
				exit();
			}
			
		}
	
			
		if($response === null || $response['Message']!='') {
				$order_array[0]['OrderID'] = $postedId;
				$order_array[0]['Status'] = 'OK';
				
				//$order_array[0]['WgMsg'] = isset($response['Message'])?$response['Message']:'There is some error please try it later.';
				$order_array[0]['StatusMessage'] = isset($response['Message'])?$response['Message']:'There is some error please try it later.';
				$result_array['message'] =Webgility_Ecc_Helper_Data::MESSAGE_ERROR_AUTH;
				
				$result_array['Message']= $response['Message'];
				$result_array['StatusCode']= 'ERROR_AUTH';
				$result_array['Result']= $order_array;
				
			if($type=='return')
			{
				return json_encode($result_array);
				exit();
			}else
			{
				echo json_encode($result_array);
				exit();
			}
			
			
		}elseif(strpos(strtolower($response['StatusCode']),'error')!==false)
		{
			
			$order_array[0]['OrderID'] = $postedId;
			$order_array[0]['Status'] = $response['StatusCode'];
			//$order_array[0]['WgMsg'] = $response['StatusMessage'];
			$order_array[0]['StatusMessage'] = $response['StatusMessage'];
			unset($response['Result']);
			$result_array['StatusCode']= 'OK';
			$result_array['Result']= $order_array;
			if($type=='return')
			{
				return json_encode($result_array);
				exit();
			}else
			{
				echo json_encode($result_array);
				exit();
			}
			
		}
		
		$ordercnt =1;
		foreach ($response['Result'] as $k=>$v)
		{

			$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
			$SetIds=$eavSetId->query("SELECT * FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_QB_ORDERS_TABLE)."` where `orderid` ='".$v['OrderNo']."'");
			
			
			$row = $SetIds->fetch();
			
			if(!$row)
			{
				 $query = "INSERT INTO ".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_QB_ORDERS_TABLE)." (`orderid`,`profile_id`,`qb_status`,`qb_posted`,`qb_posted_date`,`Transaction_type`,`qb_TransactionNumber`,`TransactionMsg`)
				 VALUES (?,?,?,?,?,?,?,?)";
				 
				
				if($eavSetId->query($query,array($v['OrderNo'],$v['ProfileID'],$v['QBStatus'],'Yes',time(),$v['QBTxnType'],$v['QBTxnNo'],$v['Message'])))
				{
					$msg =  "Order successfully posted";				
				}
			
			}else
			{
			
				 $query = "UPDATE ".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_QB_ORDERS_TABLE)." SET  `Transaction_type` = '".$v['QBTxnType']."',`qb_TransactionNumber` = '".$v['QBTxnNo']."', `qb_status` = '".$v['QBStatus']."', `qb_posted_date` = '".time()."' where `orderid` = '".$v['OrderNo']."'";
				 
				if($eavSetId->query($query))
				{
					$msg = "Successfully posted.";				
				}else
				{
					$msg = "There is some error plese tryit later.";
				}
				
			} 		
			if($v['StatusCode']==1)
			{
				$msg = $v['Message'];
			}
			$order_array[$ordercnt]['OrderID'] = $v['OrderNo'];
			$order_array[$ordercnt]['Status'] = $v['QBStatus'];
			//$order_array[$ordercnt]['WgMsg'] = $msg;
			$order_array[$ordercnt]['Message'] = $v['Message'];
			$ordercnt++;
				
		}
			
			$result_array['StatusCode']='1';
			$result_array['StatusMessage']= 'OK';
			$result_array['Result']= $order_array;
			if($type=='return')
			{
				return json_encode($result_array);
			}else
			{
				echo json_encode($result_array);
			}
			
	}
	public function managebtnAction()
	{
	
			$type = Mage::app()->getRequest()->getParam('type');	
			$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
			$SetIds=$eavSetId->query("update `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` set `status` =".$type."");
			$result_array= array('status'=>$type);
			echo json_encode($result_array);
	}
	
	public function installbtnAction()
	{
			
			$email = Mage::app()->getRequest()->getParam('Email')?Mage::app()->getRequest()->getParam('Email'):"";
			$Token = Mage::app()->getRequest()->getParam('Token');
			$StoreModuleURL = Mage::app()->getRequest()->getParam('StoreModuleURL');
			$type = Mage::app()->getRequest()->getParam('type');
			$storeid = Mage::app()->getRequest()->getParam('StoreId')?Mage::app()->getRequest()->getParam('StoreId'):"";
			
			$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
			
			$SetIds=$eavSetId->query("SELECT * FROM `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE));
			$row = $SetIds->fetch();
			if($row)
			{
			
				$SetIds=$eavSetId->query("update `".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."` set `token` ='".$Token."' , `status` =".$type."");
			}else
			{
			
				$eavSetId->query("insert into ".Mage::getSingleton('core/resource')->getTableName(Webgility_Ecc_Helper_Data::CONNECT_CONFIG_TABLE)."(`email`,`token`,`store_id`,`wcstoremodule`,`status`,`created_time`,`update_time` ) values ('".$email."','".$Token."','".$storeid."','".$StoreModuleURL."','".$type."','".time()."','".time()."')");
			}
			
			
			$result_array= array('status'=>$type);
			echo json_encode($result_array);
	}
	
	function getParams($orderno,$data)
	{
	
		$parameters['profileID'] = 1;
		$parameters['OrderNo'] = $orderno;
		//$parameters['StoreID'] = $order_store = $order = Mage::getModel('sales/order')->loadByIncrementId($parameters['OrderNo'])->getStoreId();;
		$parameters['orders'] = $data;		
		return $parameters;

	}
	function getOrders($id)
	{	
 		global $display_discount_desc,$get_Active_Carriers,$RewardsPoints_Name,$set_Short_Description,$set_field_Q_CIM_and_Q_Authorization;
     			
		//$Orders = new Orders();
		$Orders = Mage::getModel('ecc/Orders');
 		$orderlist='';	
		$orderlist[]= $id;
		if($do_not_download_configurable_product_as_line_item && $do_not_download_bundle_product_as_line_item && $download_option_as_item )
	    {
		 $do_not_download_configurable_product_as_line_item=false;
	   	 $do_not_download_bundle_product_as_line_item=false;
	    }
		if($do_not_download_configurable_product_as_line_item && $do_not_download_bundle_product_as_line_item)
	    {
		 $download_option_as_item=true;
		 $do_not_download_configurable_product_as_line_item=false;
	   	 $do_not_download_bundle_product_as_line_item=false;
	    }
		
		if($do_not_download_configurable_product_as_line_item || $do_not_download_bundle_product_as_line_item)
	    {
		 $download_option_as_item=true;
	    }
		if(!$start_order_no==0)
		{
			$my_orders = Mage::getModel('sales/order')->loadByIncrementId($start_order_no);
			$my_orders1 = $my_orders->toArray();
			$start_order_no = isset($my_orders1['entity_id'])?$my_orders1['entity_id'] : "";
			if(!isset($start_order_no) || $start_order_no=='')
			{
				$start_order_no=0;
			}
		}
		
		#$start_order_no=3;
		//$storeId=$this->getDefaultStore($storeid);
		
                ##TO GET STORE ID BY ORDER
                $order_detail = Mage::getModel('sales/order')->loadByIncrementId($id)->toArray();
                $storeId = $order_detail['store_id'];
                
		if(!isset($datefrom) or empty($datefrom)) $datefrom=date('m-d-Y');
		if(!isset($dateto) or empty($dateto)) $dateto=date('m-d-Y');		
		/*$status=$this->CheckUser($username,$password);
		
		if($status!="0")
		{		
			return $status;
		}
		*/
	
		
		$_countorders = $_orders = $this->_GetOrders($datefrom,$start_order_no,$ecc_excl_list,$storeId,$order_per_response,$by_updated_date,$orderlist,$LastModifiedDate);
		$countorders_array = $_countorders->toArray();
		$country = array();
		$country_data = Mage::getResourceModel('directory/country_collection')->load()->toOptionArray();
		foreach($country_data as $ck=>$cv)
		{
				if($cv['value']!='')
				$country[$cv['value']] = trim($cv['label']);
		}
		unset($country_data);
		if(array_key_exists('items',$countorders_array))
			$countorders_array = $countorders_array['items'];
		if(count($countorders_array)>0)
		{
			$orders_remained = count($countorders_array);
		}else{
			$orders_remained = 0;
		}
		$orders_array=$_orders->toArray();
		$no_orders = false;
		if($orders_remained<1)
		{
			$no_orders = true;
		}
		$Orders->setStatusCode($no_orders?"9999":"0");
		#$Orders->setStatusMessage($no_orders?"No Orders returned":"Total Orders:".$_orders->getSize());
	
		$Orders->setStatusMessage($no_orders?"No Orders returned":"Total Orders:".$_orders->getSize());
		$Orders->setTotalRecordFound($_orders->getSize()?$_orders->getSize():"0");
		$Orders->setTotalRecordSent(count($countorders_array)?count($countorders_array):"0");

		if ($no_orders)
		{
			return $this->response($Orders->getOrders());
			exit();
		}
			$obj = new Mage_Sales_Model_Order();
			$ord = 0;
			foreach ($_orders as $_order)
			{
				//$Order = new Order();
				$Order = Mage::getModel('ecc/Order');
				$shipments = $_order->getShipmentsCollection();
				$shippedOn='';
				foreach ($shipments as $shipment)
				{
					$increment_id = $shipment->getIncrementId();
					$shippedOn = $shipment->getCreated_at();
					$shippedOn =$this->convertdateformate($shippedOn);
				}
				$orders=$_order->toArray();				
				
				if(!$_order->getGiftMessage())
				{
					$_order->setGiftMessage( Mage::helper('giftmessage/message')->getGiftMessage($_order->getGiftMessageId()));
				}				
				$giftMessage = $_order->getGiftMessage()->toArray();
				
				$_payment=$_order->getPayment();
				$payment=$_payment->toArray();
				# Latest code modififed date for  all country
				
				/*$fdate = date("m-d-Y | h:i:s A",strtotime(Mage::app()->getLocale()->date($orders["created_at"], Varien_Date::DATETIME_INTERNAL_FORMAT)));
				$fdate = explode("|",$fdate);
				$dateCreateOrder= trim($fdate[0]);
				$timeCreateOrder= trim($fdate[1]);
				*/
				
				#changed on request of nilesh sir
				
				if(strtotime(Mage::app()->getLocale()->date($orders["created_at"],  Varien_Date::DATETIME_INTERNAL_FORMAT)))
				{
				# Latest code modififed date for all country
				$fdate = date("m-d-Y | h:i:s A",strtotime(Mage::app()->getLocale()->date($orders["created_at"],  Varien_Date::DATETIME_INTERNAL_FORMAT)));
				$fdate = explode("|",$fdate);
				$dateCreateOrder= trim($fdate[0]);
				$timeCreateOrder= trim($fdate[1]);
				#changed on request of nilesh sir
				}else{
				#Code is custamize for this customer
				$dateObj=Mage::app()->getLocale()->date($orders["created_at"]);
				$dateStrToTime=$dateObj->getTimestamp();
				$fdate = date("m-d-Y | h:i:s A",$dateStrToTime);
				$fdate = explode("|",$fdate);
				$dateCreateOrder= trim($fdate[0]);
				$timeCreateOrder= trim($fdate[1]);
				}
				
				
				if(!array_key_exists('billing_firstname',$orders) && !array_key_exists('billing_lastname',$orders) )
				{
					$billingAddressArray = $_order->getBillingAddress()->toArray();
					$orders["billing_firstname"]=	$billingAddressArray["firstname"];
					$orders["billing_lastname"]	=	$billingAddressArray["lastname"];
					$orders["billing_company"]	=	$billingAddressArray["company"];
					$orders["billing_street"]	=	$billingAddressArray["street"];
					$orders["billing_city"]		=	$billingAddressArray["city"];
					$orders["billing_region"]	=	$billingAddressArray["region"];
					$orders["billing_postcode"]	=	$billingAddressArray["postcode"];
					$orders["billing_country"]	=	$billingAddressArray["country_id"];
					$orders["customer_email"]	=	isset($billingAddressArray["customer_email"])?$billingAddressArray["customer_email"]:$orders["customer_email"];
					$orders["billing_telephone"]=	$billingAddressArray["telephone"];
				}
				//$Order = new Order();
########################################### custamization QRD-801-69772 to get Referral Source ###########################################################

				$attributeInfoRS = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('referral_source')->getFirstItem();
				$attributeInfoRS = $attributeInfoRS->getData();	
				if(isset($attributeInfoRS)&&!empty($attributeInfoRS))
				{
					$orderAttributes = Mage::getModel('amorderattr/attribute')->load($orders['entity_id'], 'order_id');
					$OAarray=$orderAttributes->toArray();		
					if(isset($OAarray)&&!empty($OAarray))										
					  {
						$list = array();
						$collection = Mage::getModel('eav/entity_attribute')->getCollection();
						$collection->addFieldToFilter('is_visible_on_front', 1);
						$collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
						$collection->getSelect()->order('checkout_step');
						$attributes = $collection->load();
						if ($attributes->getSize())
						{
							foreach ($attributes as $attribute)
							{
								$currentStore = $storeId;
								$storeIds = explode(',', $attribute->getData('store_ids'));
								if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
								{
									continue;
								}
								$value = '';
								switch ($attribute->getFrontendInput())
								{
									case 'select':
										$options = $attribute->getSource()->getAllOptions(true, true);
										foreach ($options as $option)
										{
											if ($option['value'] == $orderAttributes->getData($attribute->getAttributeCode()))
											{
												$value = $option['label'];
												break;
											}
										}
										break;
									default:
										$value = $orderAttributes->getData($attribute->getAttributeCode());
										break;
								}
								$list[$attribute->getFrontendLabel()] = str_replace('$', '\$', $value);
							}
						}
						$setSalesRep=$list['Referral Source'];	
					}//end if
				
			    }else{
			 	    $setSalesRep='';
			    }
	

	
########################################### custamization ###########################################################			  

				$Order->setOrderId($orders['increment_id']);
				$Order->setTitle('');
				$Order->setFirstName($orders["billing_firstname"]);
				$Order->setLastName($orders["billing_lastname"]);
				$Order->setDate($dateCreateOrder);
				$Order->setTime($timeCreateOrder);
				//$Order->setLastModifiedDate($this->_dateformat_wg($orders["updated_at"]));
				
				$Order->setStoreID($orders['store_id']);
				$Order->setStoreName('');
				$Order->setCurrency($orders['order_currency_code']);
				$Order->setWeight_Symbol('lbs');
				$Order->setWeight_Symbol_Grams('453.6');
				$Order->setCustomerId($orders['customer_id']);
				
				if($shippedOn=='' || empty($shippedOn))
				{
				$shippedOn=$dateCreateOrder;				
				}	
				 
				$orderStatus = $this->_getorderstatuses($storeId);
				if(array_key_exists($orders['status'],$orderStatus ))
					$Order->setStatus($orderStatus[$orders['status']]);
				else
					$Order->setStatus($orders['status']);

				if($payment['method']=='purchaseorder')
				{
					$orders['customer_note'] = $orders['customer_note'] ." Purchase Order Number: ".$payment['po_number'];
				}
				//$customer_comment = "";
				/*if($_order->getBiebersdorfCustomerordercomment())
				{
					$customer_comment = $_order->getBiebersdorfCustomerordercomment();
				}*/
				
				
				
				/*foreach ($_order->getStatusHistoryCollection(true) as $_comment)
				{
					if($_comment->getComment())
					{
						$customer_comment =$_comment->getComment();
					}
				}
				*/
				
				/*$Order->setNotes(isset($orders['customer_note'])?$orders['customer_note']:"");
				$giftMessage['message'] = isset($giftMessage['message'])?$giftMessage['message']:"";
				$Order->setComment($customer_comment.$giftMessage['message']);*/
				
				
				
				
				
				$order_comment='';
				/*if($_order->getBiebersdorfCustomerordercomment())
				{
					echo"<li>".$order_comment = $_order->getBiebersdorfCustomerordercomment();
				}*/
				
				
				foreach ($_order->getStatusHistoryCollection(true) as $_comment)
				{
					if($_comment->getComment())
					{
						$cust_comment = $_comment->getComment();
					}
				}
				
				foreach ($_order->getStatusHistoryCollection(true) as $_comment)
				{
					if($_comment->getComment())
					{
						$order_comment = $_comment->getComment();
						break;
					}
				}
			//	echo"<li>".$order_comment;
			//	echo"<li>".$cust_comment;
				
			//	die("ds");
				$Order->setNotes(isset($order_comment)?$order_comment:"");
				$giftMessage['message'] = isset($giftMessage['message'])?$giftMessage['message']:"";
				$Order->setComment($cust_comment);
				
				
													
				$Order->setFax('');
				#assign order info to order object
				//$Order->setOrderInfo($OrderInfo->getOrderInfo());


/***************************************************************************************************
Custamization for XPU-623-53661 Start: We create a config variable to manage this.
****************************************************************************************************/
if($set_field_Q_CIM_and_Q_Authorization)
{
				$po_number_str=$payment['po_number'];
				$po_number=explode("-",$po_number_str);
				if(!empty($po_number['0']))
				{
				$q_cim=$po_number['0'];
				}
				
				if($q_cim!="" || $payment['last_trans_id']!="")
				{
					// code for custom fields  
						$WG_OtherInfo = new WG_OtherInfo();
						$WG_Other = new WG_Other();
						$other_field= array('Q_CIM'=>$q_cim);
						foreach($other_field as $key=>$value)
						{
			
							$WG_OtherInfo->setFieldName($key);
							$WG_OtherInfo->setFieldValue(html_entity_decode($value));
								
			
							$WG_Other->setCustomFeilds($WG_OtherInfo->getOtherinfo());
									
						}		
							
						$Order->setOrderOtherInfo($WG_Other->getOther());	
					//code for custom fields 
			     }	
}
/***************************************************************************************************
Custamization for XPU-623-53661 Ends.
****************************************************************************************************/
				$item_array = $this->getorderitems($orders["entity_id"],$orders["increment_id"],$download_option_as_item);
				$item_array = $item_array['items'];
				$onlineInfo = array();
				
				if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
				{
					unset($orderConfigItems);
					$orderConfigItems = array();
				}
				
				if($do_not_download_bundle_product_as_line_item==true && $download_option_as_item==true)
				{
					unset($orderBundalItems);
					$orderBundalItems = array();
				}
				
				
				
				unset($parent_bundle_for_order);
				$itemI = 0;
				foreach($item_array as $iInfo)
				{
						
						if(is_object($iInfo['product']))
						$onlineInfo =  $iInfo['product']->toArray();

					if(intval($iInfo["qty_ordered"])>0 && is_numeric($iInfo["price"]))
					{
						unset($productoptions);
						$productoptions = array();
					
						if(isset($iInfo['product_options']))
						$productoptions = unserialize($iInfo['product_options']);
						
						if(isset($productoptions['options']) && is_array($productoptions['options']))
						{
							if($productoptions['options'])
							{
								if(is_array($productoptions['options']) && !empty($productoptions['options']))
								{
									if(is_array($productoptions['attributes_info']))
									{
										$productoptions['attributes_info']     =    array_merge($productoptions['attributes_info'],$productoptions['options']);
									}else{
										$productoptions['attributes_info']     =    $productoptions['options'];
									}
								}
								unset($productoptions['options']);
							}
						}
						if(!empty($productoptions['bundle_options']) && is_array($productoptions['bundle_options']))
						{

							if(array_key_exists('attributes_info', $productoptions))
							{
								$productoptions['attributes_info'] = array_merge($productoptions['attributes_info'],$productoptions['bundle_options']);
														
							}else{
								$productoptions['attributes_info'] = $productoptions['bundle_options'];
							}							
							unset($productoptions['bundle_options']);
						}						
						if(isset($iInfo['product']))
						{
							$product = $iInfo;
							$product['type_id'] = $iInfo['product_type'];
							$product_base = $iInfo['product']->toArray();
							$product['tax_class_id'] = $product_base['tax_class_id'];
						}else{
							$product = $iInfo;
							$product['type_id'] = $iInfo['product_type'];
							//$product['tax_class_id'] = 'no';
							$currentProduct = Mage::getModel("catalog/product")->load($iInfo['product_id']);
						 	$product_base = $currentProduct->toArray();
							$product['tax_class_id'] = $product_base['tax_class_id'];
							$productoptions['simple_sku'] = $iInfo['sku'];
						}
						
						if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
						{
							if(in_array($iInfo['parent_item_id'],$orderConfigItems))		
		  					{
						  		continue;
		  					}
						}
						
						
							if($do_not_download_bundle_product_as_line_item==true && $download_option_as_item==true)
						{
							if(in_array($iInfo['parent_item_id'],$orderBundalItems))		
		  					{
						  		continue;
		  					}
						}
						
						if($product['type_id']=='bundle')
						{
							#$download_option_as_item =false;
							#PriceType == 0  means Dynamic price product
							if($download_option_as_item  == true && $iInfo['product']->getPriceType()==0)
							{
								$parent_bundle_for_order[$iInfo['item_id']] =$iInfo['product_type'] ;
							}
						}
						if($parent_bundle_for_order[$iInfo['parent_item_id']]=='bundle')
						{
							$iInfo["price"] = 0;
						}
						//$Item = new Item();						
						$Item = Mage::getModel('ecc/Item');					
						
						if($product['type_id']!='configurable')
						{
							if($do_not_download_bundle_product_as_line_item==true && $download_option_as_item==true)
							{
						    	$orderBundalItems[] = $iInfo['item_id'];
						    }
							//$responseArray['Orders'][$ord]['Items'][$itemI]['ItemCode'] = htmlentities($product['sku'],ENT_QUOTES);
							$Item->setItemCode($product['sku']);
						}else{
						    
							if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
							{
						    	$orderConfigItems[] = $iInfo['item_id'];
						    }
							$Item->setItemCode($productoptions['simple_sku']);
							//$responseArray['Orders'][$ord]['Items'][$itemI]['ItemCode'] = htmlentities($productoptions['simple_sku'],ENT_QUOTES);
						}
						
						$Item->setItemDescription($product['name']);
						
						if($set_Short_Description)
						{
						$Item->setItemShortDescr(empty($onlineInfo['short_description'])?substr($product['short_description'],0,2000):substr($onlineInfo['short_description'],0,2000));
						}else{
						
					    $Item->setItemShortDescr(empty($onlineInfo['description'])?substr($product['description'],0,2000):substr($onlineInfo['description'],0,2000));
						}
						$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
												->setCodeFilter('ecc')
												->getFirstItem();
						$attributeInfo = $attributeInfo->getData();	
										
						if(isset($attributeInfo) && !empty($attributeInfo))
						$attributeValue = Mage::getModel('catalog/product')
                            ->load($iInfo["product_id"])->getAttributeText('ecc');
						
						if(isset($attributeValue) && $attributeValue=='Yes' && $iInfo["weight"]>0 )
						{
							$iInfo["qty_ordered"] = $iInfo["qty_ordered"]*$iInfo["weight"];
							$iInfo["price"] = $iInfo["price"]/$iInfo["qty_ordered"];
							$iInfo["weight"] = $iInfo["weight"]/$iInfo["qty_ordered"];
						}
						$Item->setItemID($iInfo['item_id']);
						$Item->setQuantity($iInfo["qty_ordered"]);
						$Item->setShippedQuantity($iInfo["qty_shipped"]);
						
					
						$Item->setUnitPrice($iInfo["price"]);
						$Item->setCostPrice($onlineInfo["cost"]);
						$Item->setWeight($iInfo["weight"]);
						$Item->setFreeShipping("N");
						$Item->setDiscounted("N");
						$Item->setshippingFreight("0.00");
						$Item->setWeight_Symbol("lbs");
						$Item->setWeight_Symbol_Grams("453.6");

						if($product['tax_class_id']<=0 || $product['tax_class_id']="")
						{
							$Item->setTaxExempt("Y");

						}else{
							$Item->setTaxExempt("N");
						}
						$iInfo['onetime_charges']="0.00";
						$Item->setOneTimeCharge(number_format($iInfo['onetime_charges'],2,'.',''));
						$Item->setItemTaxAmount("");
						//$responseArray['ItemOptions'] = array();
						if(array_key_exists("attributes_info",$productoptions))
						{
							$optionI = 0;
							foreach($productoptions['attributes_info'] as $item_option12)
							{
								//$Itemoption = new Itemoption();
								$Itemoption = Mage::getModel('ecc/Itemoption');
								if(is_array($item_option12['value']))
								{
								    $item_option1234='';
									foreach($item_option12['value'] as $item_option123)
									{
									
									$item_option1234 = " ".$item_option123['qty']." x ".$item_option123['title']." $".$item_option123['price'];
									$Itemoption->setOptionValue($item_option1234);
									$Itemoption->setOptionName($item_option12['label']);
									$Itemoption->setOptionPrice($item_option123['price']);
									
									$Item->setItemOptions($Itemoption->getItemoption());									
									
									}
									//$responseArray['ItemOptions'][$optionI]['Name'] = htmlentities($item_option12['label']);
									//$responseArray['ItemOptions'][$optionI]['Value'] = htmlentities($item_option1234);
									unset($item_option1234);
								}else{
									$Itemoption->setOptionValue($item_option12['value']);
									$Itemoption->setOptionName($item_option12['label']);
									$Item->setItemOptions($Itemoption->getItemoption());
									//$responseArray['ItemOptions'][$optionI]['Name'] = htmlentities($item_option12['label']);
									//$responseArray['ItemOptions'][$optionI]['Value'] = htmlentities($item_option12['value']);
								}								
								$optionI++;
							}
						}
						#custamization for client date: 08 may 2012
						if($iInfo['nonreturnable']=="Yes" && isset($iInfo['nonreturnable']))
						{
									//$Itemoption = new Itemoption();
									$Itemoption = Mage::getModel('ecc/Itemoption');
									$Itemoption->setOptionValue("Non-returnable");
									$Itemoption->setOptionName("Clearance");
									$Item->setItemOptions($Itemoption->getItemoption());
						
						}
						
						
						//$Item->setItemOptions($responseArray['ItemOptions']);
						//unset($responseArray['ItemOptions']);						
					}
					$itemI++;
					$Order->setOrderItems($Item->getItem());
				}


				$discountadd =true;
				#Discount Coupon as line item
				$orders["discount_amount"] = $orders["discount_amount"]?$orders["discount_amount"]:$orders["base_discount_amount"];
				
				if(($orders['coupon_code']!='' || $orders['discount_description']!='') && $discount_as_line_item==true)
				{
					$discountadd =false;
					$orders["discount_amount"] = $orders["discount_amount"]?$orders["discount_amount"]:$orders["base_discount_amount"];
					
					if($display_discount_desc){ $DESCR1 = $orders['discount_description'];  }else{ $DESCR1 = $orders['coupon_code']; }
					//$DESCR1 = $orders['coupon_code']?$orders['coupon_code']:$orders['discount_description'];
					$itemI++;					
					$Item = new Item();
					$Item->setItemCode("Discount Coupon");
					$Item->setItemDescription(substr($DESCR1,0,50));
					$Item->setItemShortDescr("Coupon code ".htmlentities(substr($DESCR1,0,50),ENT_QUOTES));
					$Item->setQuantity(intval(1));
					$discount_amount=$orders["discount_amount"];
					if($discount_amount< 0)
					{			
					$Item->setUnitPrice($orders["discount_amount"]);
					}else{
					$Item->setUnitPrice("-".$orders["discount_amount"]);
					}
					$Item->setWeight('');
					$Item->setFreeShipping("N");					
					$Item->setshippingFreight("0.00");
					$Item->setWeight_Symbol("lbs");
					$Item->setWeight_Symbol_Grams("453.6");
					$Item->setDiscounted("Y");
					$Order->setOrderItems($Item->getItem());
				}
				#Reward Points as line item
				if($orders["reward_points_balance"])
				{
					$itemI++;					
					$Item = new Item();
					$Item->setItemCode($RewardsPoints_Name);
					$Item->setItemDescription($orders["reward_points_balance"].'reward points');
					$Item->setItemShortDescr($orders["reward_points_balance"].'reward points');
					$Item->setQuantity(intval(1));
					$Item->setUnitPrice("-".$orders["base_reward_currency_amount"]);
					$Item->setWeight('');
					$Item->setFreeShipping("N");					
					$Item->setshippingFreight("0.00");
					$Item->setWeight_Symbol("lbs");
					$Item->setWeight_Symbol_Grams("453.6");
					$Item->setDiscounted("Y");
					$Order->setOrderItems($Item->getItem());

				}
				
				if($orders["customer_credit_amount"]>0)
				{
					$itemI++;					
					$Item = new Item();
					$Item->setItemCode("InternalCredit");
					$Item->setItemDescription('Internal Credit');
					$Item->setItemShortDescr('Internal Credit');
					$Item->setQuantity(intval(1));
					$Item->setUnitPrice("-".$orders["customer_credit_amount"]);
					$Item->setWeight('');
					$Item->setFreeShipping("N");					
					$Item->setshippingFreight("0.00");
					$Item->setWeight_Symbol("lbs");
					$Item->setWeight_Symbol_Grams("453.6");
					$Item->setDiscounted("Y");
					$Order->setOrderItems($Item->getItem());

				}


				if($orders["gift_cards"])
				{
					$gift_cards = unserialize($orders["gift_cards"]);
					foreach($gift_cards as $gift_card)
					{
						$itemI++;
						$Item = new Item();
						$Item->setItemCode("GiftCard");
						$Item->setItemDescription(substr("GiftCard #.".$gift_card['c'],0,50));
						$Item->setItemShortDescr(substr($gift_card['c'],0,50));
						$Item->setQuantity(intval(1));
						$Item->setUnitPrice("-".$gift_card['a']);
						$Item->setWeight('');
						$Item->setFreeShipping("N");					
						$Item->setshippingFreight("0.00");
						$Item->setWeight_Symbol("lbs");
						$Item->setWeight_Symbol_Grams("453.6");
						$Item->setDiscounted("Y");
						$Order->setOrderItems($Item->getItem());

					}
				}
				if($orders["giftcert_code"])
				{
			
						$Item = new Item();
						$Item->setItemCode("Gift Certificate" );
						$Item->setItemDescription($orders["giftcert_code"]);
						$Item->setItemShortDescr("Gift Certificate");
						$Item->setQuantity(intval(1));
						$Item->setUnitPrice("-".$orders['giftcert_amount']);
						$Item->setWeight('');
						$Item->setFreeShipping("N");
						$Item->setshippingFreight("0.00");
						$Item->setWeight_Symbol("lbs");
						$Item->setWeight_Symbol_Grams("453.6");
						$Item->setDiscounted("Y");
						$Order->setOrderItems($Item->getItem());
				
					
				}
				
				
								if($orders["gw_price"]!="0.0" && $orders["gw_price"]>"0.0")
				{
			
						$Item = new Item();
						$Item->setItemCode("Gift Wrapping for Order");
						$Item->setItemDescription("Gift Wrapping for Order");
						$Item->setItemShortDescr("Gift Wrapping for Order");
						$Item->setQuantity(intval(1));
						$Item->setUnitPrice($orders['gw_price']);
						$Item->setWeight('');
						$Item->setFreeShipping("N");
						$Item->setshippingFreight("0.00");
						$Item->setWeight_Symbol("lbs");
						$Item->setWeight_Symbol_Grams("453.6");
						$Item->setDiscounted("Y");
						$Order->setOrderItems($Item->getItem());
				
					
				}
				
				
				if($orders["gw_items_price"]!="0.0" && $orders["gw_items_price"]>"0.0")
				{
			
						$Item = new Item();
						$Item->setItemCode("Gift Wrapping for Items");
						$Item->setItemDescription("Gift Wrapping for Items");
						$Item->setItemShortDescr("Gift Wrapping for Items");
						$Item->setQuantity(intval(1));
						$Item->setUnitPrice($orders['gw_items_price']);
						$Item->setWeight('');
						$Item->setFreeShipping("N");
						$Item->setshippingFreight("0.00");
						$Item->setWeight_Symbol("lbs");
						$Item->setWeight_Symbol_Grams("453.6");
						$Item->setDiscounted("Y");
						$Order->setOrderItems($Item->getItem());
				
					
				}
				
				
				/////////////////////////////////////
				//   billing info
				/////////////////////////////////////
				//$Bill = new Bill();
				//$CreditCard = new CreditCard();
				$Bill = Mage::getModel('ecc/Bill');
				$CreditCard = Mage::getModel('ecc/CreditCard');
				
				$PayStatus = "Cleared";
				if ($payment['cc_type']!="")
				{
					if($ccdetails!=='DONOTSEND')
					{				
					$CreditCard->setCreditCardType($this->getCcTypeName($payment['cc_type']));
					if (isset($payment['amount_paid']))
					{
					$CreditCard->setCreditCardCharge($payment['amount_paid']);
					
					}else{
					$CreditCard->setCreditCardCharge('0.00');
					
					}
					if (isset($payment['cc_exp_month'])&&isset($payment['cc_exp_year'])){
					$CreditCard->setExpirationDate(sprintf('%02d',$payment['cc_exp_month']).substr($payment['cc_exp_year'],-2,2));
					}else{
					$CreditCard->setExpirationDate("");
					}
					
					$CreditCardName = $payment['cc_owner']?($payment['cc_owner']):"";					
					$CreditCard->setCreditCardName($CreditCardName);
					$payment['cc_number_enc'] = Mage::helper('core')->decrypt($payment['cc_number_enc']);
					$CreditCardNumber = $payment['cc_number_enc']?$payment['cc_number_enc']:$payment['cc_last4'];					
					$CreditCard->setCreditCardNumber(utf8_encode($CreditCardNumber));
					
					if(!empty($orders['quote_id']))
					{
					$getQuote=Mage::getModel('sales/quote_payment')->getCollection()->setQuoteFilter($orders['quote_id']);
					$getQuote_val=$getQuote->toArray();
					
					$cc_cid = Mage::helper('core')->decrypt($getQuote_val['items']['0']['cc_cid_enc']);   
					$CreditCard->setCVV2($cc_cid);
					}
					else
					{
					$CreditCard->setCVV2('');
					}
					
					
					$CreditCard->setAdvanceInfo('');										
					$transcationId ="";
					$transcationId = (isset($payment['cc_trans_id'])?($payment['cc_trans_id']):"");
					$transcationId  = $transcationId ? $transcationId : $payment['last_trans_id'];
					}					
					$CreditCard->setTransactionId($transcationId);															
					$CreditCard->getCreditCard();					
					$Bill->setCreditCardInfo($CreditCard->getCreditCard());					
				}else{
					$transcationId ="";
					$additional_information_authorize_cards=$payment['additional_information']['authorize_cards'];
					if(is_array($additional_information_authorize_cards))
					foreach($additional_information_authorize_cards as $key =>$value)
					{
						$payment['last_trans_id'] = $value['last_trans_id'];
						$payment['cc_type']= $value['cc_type'];
						$payment['cc_exp_month'] = $value['cc_exp_month'];
						$payment['cc_exp_year'] = $value['cc_exp_year'];
						$payment['cc_last4'] = $value['cc_last4'];
					}
				  if($ccdetails!=='DONOTSEND')
				  {			
					$CreditCard->setCreditCardType($this->getCcTypeName($payment['cc_type']));
					$CreditCard->setCreditCardCharge($payment['amount_paid']);
					$CreditCard->setExpirationDate(sprintf('%02d',$payment['cc_exp_month']).substr($payment['cc_exp_year'],-2,2));
					$CreditCard->setCreditCardName($CreditCardName);
					$CreditCardNumber = $payment['cc_number_enc']?$payment['cc_number_enc']:$payment['cc_last4'];	
					$CreditCard->setCreditCardNumber(utf8_encode($CreditCardNumber));
					if(!empty($orders['quote_id']))
					{
						$getQuote=Mage::getModel('sales/quote_payment')->getCollection()->setQuoteFilter($orders['quote_id']);
					   $getQuote_val=$getQuote->toArray();
					   
					   $cc_cid = Mage::helper('core')->decrypt($getQuote_val['items']['0']['cc_cid_enc']);   
					   $CreditCard->setCVV2($cc_cid);
					}
					else
					{
						$CreditCard->setCVV2('');
					}
					$CreditCard->setAdvanceInfo('');	
					}					
$transcationId  = $transcationId ? $transcationId : $payment['last_trans_id'];
					$CreditCard->setTransactionId($transcationId);
					$CreditCard->getCreditCard();                   
					$Bill->setCreditCardInfo($CreditCard->getCreditCard());       
                    }
					
				if (isset($payment['amount_ordered'])&&isset($payment['amount_paid']))
				{
					if (($payment['amount_paid']==$payment['amount_ordered']))
						$PayStatus = "Pending";
				}
				# for version 1.4.1.0
				$Bill->setPayMethod($this->getPaymentlabel($payment['method']));
				$Bill->setTitle("");
				$Bill->setFirstName($orders["billing_firstname"]);
				$Bill->setLastName($orders["billing_lastname"]);

				if (!empty($orders["billing_company"]))
				{
					$Bill->setCompanyName($orders["billing_company"]);				
				}else{
					$Bill->setCompanyName("");				
				}
				
				$orders["billing_street"] = explode("\n",$orders["billing_street"]);
				$Bill->setAddress1($orders["billing_street"][0]);				
				$Bill->setAddress2(isset($orders["billing_street"][1])?$orders["billing_street"][1]:"");				
				$Bill->setCity($orders["billing_city"]);				
				$Bill->setState($orders["billing_region"]);				
				$Bill->setZip($orders["billing_postcode"]);				
				$Bill->setCountry(trim($country[$orders["billing_country"]]));				
				$Bill->setEmail($orders["customer_email"]);				
				$Bill->setPhone($orders["billing_telephone"]);				
				$Bill->setPONumber($payment['po_number']);					
				
				$customer = Mage::getModel('customer/customer')->load($orders["customer_id"]);
				$customerGroupId = $customer->getGroupId();
				$group = Mage::getModel('customer/group')->load($customerGroupId);
				$group_nam=$group->getCode();
				
				//$Bill->setGroupName($group_nam);
				
				$Order->setOrderBillInfo($Bill->getBill());
				
				/////////////////////////////////////
				//   CreditCard info
				/////////////////////////////////////
				//$Ship =new Ship();
				$Ship = Mage::getModel('ecc/Ship');
				
				/*  // Old code
				$ship_career = explode("-",$orders["shipping_description"],2);
				$Ship->setShipMethod($ship_career[1]);
				$Ship->setCarrier($ship_career[0]);
				$Ship->setTrackingNumber($shipTrack);*/

				#new code for shiping as per auto sync functionility Start
				$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($_order)->load();
			
				foreach ($shipmentCollection as $shipment){
				
				  foreach($shipment->getAllTracks() as $ship_data)
					{
				    $Req_ship_detail_arry=$ship_data->toArray();
					$ShipMethod=$Req_ship_detail_arry['title'];
					$carrier_code=$Req_ship_detail_arry['carrier_code'];
					$shipTrack1=$Req_ship_detail_arry['track_number'];
				
				
					} 
					
				}
				
						
				if($get_Active_Carriers)
				{
					$carrierInstances = Mage::getSingleton('shipping/config')->getActiveCarriers($storeid);
				}else{
				$carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers($storeId);
				}


				
				$carriers['custom'] = Mage::helper('sales')->__('Custom Value');
				foreach ($carrierInstances as $code => $carrier) {
					if ($carrier->isTrackingAvailable()) {
						$carriers[$code] = $carrier->getConfigData('title');
					}
				}
				$c_code='';	
				foreach($carriers as $c_key=>$c_val)
				{
					if($carrier_code==$c_key)
					{
						$Carrier=$c_val;
						break;
					}
				}
				unset($carrier_code);
				$Carrier=strtolower($Carrier);
				$ship_career = explode("-",$orders["shipping_description"],2);
				$Ship->setShipMethod(empty($ShipMethod)?$ship_career[1]:$ShipMethod);
				$Ship->setCarrier(empty($Carrier)?$ship_career[0]:$Carrier);
				$Ship->setTrackingNumber(!empty($shipTrack1)?$shipTrack1:'');
				#End
				
			    unset($shipTrack);
				$Ship->setTitle("");
				
				if(!array_key_exists('shipping_firstname',$orders) && !array_key_exists('shipping_lastname',$orders) )
				{
					$shippingAddressArray = $_order->getShippingAddress();
					if(is_array($shippingAddressArray))
					$shippingAddressArray = $shippingAddressArray->toArray();
					$orders["shipping_firstname"]=$shippingAddressArray["firstname"];
					$orders["shipping_lastname"]=$shippingAddressArray["lastname"];
					$orders["shipping_company"]=$shippingAddressArray["company"];
					$orders["shipping_street"]=$shippingAddressArray["street"];
					$orders["shipping_city"]=$shippingAddressArray["city"];
					$orders["shipping_region"]=$shippingAddressArray["region"];
					$orders["shipping_postcode"]=$shippingAddressArray["postcode"];
					$orders["shipping_country"]=$shippingAddressArray["country_id"];
					$orders["customer_email"]=$shippingAddressArray["customer_email"]?$shippingAddressArray["customer_email"]:$orders["customer_email"];
					$orders["shipping_telephone"]=$shippingAddressArray["telephone"];
				}
				$Ship->setFirstName($orders["shipping_firstname"]);
				$Ship->setLastName($orders["shipping_lastname"]);
				if (!empty($orders["shipping_company"]))
				{
					$Ship->setCompanyName($orders["shipping_company"]);
				}else{
					$Ship->setCompanyName("");
				}
				
				$orders["shipping_street"] = explode("\n",$orders["shipping_street"]);
								
				$Ship->setAddress1($orders["shipping_street"][0]);
				$Ship->setAddress2(isset($orders["shipping_street"][1])?$orders["shipping_street"][1]:"");
				$Ship->setCity($orders["shipping_city"]);
				$Ship->setState($orders["shipping_region"]);
				$Ship->setZip($orders["shipping_postcode"]);
				$Ship->setCountry(trim($country[$orders["shipping_country"]]));
				$Ship->setEmail($orders["customer_email"]);
				$Ship->setPhone($orders["shipping_telephone"]);
				
				$Order->setOrderShipInfo($Ship->getShip());
				#$Orders->setOrders($Order->getOrder());
				
				
				
				//$charges =new Charges();
				$charges = Mage::getModel('ecc/Charges');
				$charges->setDiscount($discountadd?abs($orders["discount_amount"]):'');
				//$charges->setStoreCredit($orders["customer_balance_amount"]);
				$charges->setStoreCredit($orders["customer_balance_amount"]?$orders["customer_balance_amount"]:0.00);
				$charges->setTax($orders["tax_amount"]);
				$charges->setShipping($orders["shipping_amount"]);
				$charges->setTotal( $orders["grand_total"]);
				$charges->setSubTotal();
				$Order->setOrderChargeInfo($charges->getCharges());
				
				$Order->setShippedOn($shippedOn);
				
			
				
				$Order->setShippedVia(empty($Carrier)?$ship_career[0]:$Carrier);
					 unset($Carrier,$shipTrack1,$ShipMethod);
				//$Order->setSalesRep($setSalesRep);
				$Orders->setOrders($Order->getOrder());
				$ord++;
			}
			
			#echo $this->response($Orders->getOrders());
			
//	print_r($this->response($Orders->getOrders()));

		return $this->response($Orders->getOrders());
	
	

	}
	
	public  function _GetOrders($datefrom,$start_order_no=0,$order_status_list='',$storeId=1,$no_of_orders=20,$by_updated_date='',$orderlist,$LastModifiedDate)
	{
		if(strtolower($order_status_list)=='all' || strtolower($order_status_list)=="'all'")
		{
		
			$order_status = array();
			$orderStatus1 = $this->_getorderstatuses($storeId);
			foreach ($orderStatus1 as $sk=>$sv)
			{
				$order_status[]= $sk;
			}
		}else{
			$order_status_list = str_replace("'","",$order_status_list);
			$order_status_list = explode(",",$order_status_list);
			$order_status = $this->_orderStatustofetch($order_status_list,$storeId);
		}
			if($LastModifiedDate)
			{
				
			
				$datefrom2 = explode(" ",$LastModifiedDate);
				$datetime1 = explode("-",$datefrom2[0]);			
				$LastModifiedDate = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
				$LastModifiedDate .=" ".$datefrom2[1]; 

			}else
			{
				$datetime1 = explode("-",$datefrom);			
				$datefrom = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
				$datefrom .=" 00:00:00"; 
			}
			if(!$orderlist && $LastModifiedDate)
			{
				
				$this->_orders = Mage::getResourceModel('sales/order_collection')
				->addAttributeToSelect('*')
				->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
				->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
				->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
				->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
				->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
				->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
				->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
				->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
				->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
				->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
				->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left')
				->addAttributeToFilter('updated_at', array('gt' => $LastModifiedDate,'datetime' => true))
				->addAttributeToFilter('store_id', $storeId)
			//	->addAttributeToFilter('entity_id', array('gt' => $start_order_no))
				->addAttributeToFilter('status', array('in' => $order_status))
				->addAttributeToSort('updated_at', 'asc')
				->setPageSize($no_of_orders)
				->load();	
			}elseif(!$orderlist && $datefrom)
			{
				
				$this->_orders = Mage::getResourceModel('sales/order_collection')
				->addAttributeToSelect('*')
				->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
				->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
				->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
				->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
				->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
				->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
				->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
				->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
				->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
				->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
				->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left')
				->addAttributeToFilter('created_at', array('from' => $datefrom,'datetime' => true))
				->addAttributeToFilter('store_id', $storeId)
				->addAttributeToFilter('entity_id', array('gt' => $start_order_no))
				->addAttributeToFilter('status', array('in' => $order_status))
				->addAttributeToSort('entity_id', 'asc')
				->setPageSize($no_of_orders)
				->load();
			
			}else
			
			{
				$this->_orders = Mage::getResourceModel('sales/order_collection')
				->addAttributeToSelect('*')
				->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
				->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
				->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
				->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
				->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
				->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
				->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
				->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
				->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
				->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
				->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
				->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left')		
				->addAttributeToFilter('store_id', $storeId)
				->addAttributeToFilter('increment_id', array('in' => $orderlist))		
				->addAttributeToSort('entity_id', 'asc')		
				->load();		
			
			}	
					
			return $this->_orders;
			
		
	}
	public function _orderStatustofetch($order_status_list,$storeId)
	{
	
		$orderStatus = $this->_getorderstatuses($storeId);
		$order_status = array();
		foreach ($orderStatus as $sk=>$sv)
		{
			if(in_array(trim($sv),$order_status_list))
			{
				$order_status[] =$sk;
			}
		}
		return $order_status;
	}
		public function _getorderstatuses($storeId=1)
	{
		$statuses = Mage::getSingleton('sales/order_config')->getStatuses();
		return $statuses;
	}
	public function convertdateformate($shippedOn)
	{
	
		$shippedOnAry=explode(" ",$shippedOn);
		$shippedOnDate=explode("-",$shippedOnAry['0']);
		$shippedOn=$shippedOnDate['1']."-".$shippedOnDate['2']."-".$shippedOnDate['0'];
		return $shippedOn;
	}
	public function _dateformat_wg($date)
	{
		if(strtotime(Mage::app()->getLocale()->date($date,  Varien_Date::DATETIME_INTERNAL_FORMAT)))
				{
				# Latest code modififed date for all country
				$fdate = date("m-d-Y H:i:s",strtotime($date));
				
				}else{
				#Code is custamize for this customer
				$dateObj=Mage::app()->getLocale()->date($date);
				$dateStrToTime=$dateObj->getTimestamp();
				$fdate = date("m-d-Y H:i:s",$dateStrToTime);
				}
				return $fdate;
				
	}
	public function getorderitems($Id,$incrementID,$download_option_as_item)
	{
		#global $download_option_as_item;
		#config option
		#$download_option_as_item =false;
		if($download_option_as_item==true)
		{
			$collection =Mage::getModel('sales/order_item')->getCollection()
			->setOrderFilter($Id)
			->setOrder('item_id','asc');
		}else{
			$collection =Mage::getModel('sales/order_item')->getCollection()
			->setOrderFilter($Id)
			->addFieldToFilter('parent_item_id', array('null' => true))
			->setOrder('item_id','asc');
		}
		$products = array();
		foreach ($collection as $item)
		{
				$products[] = $item->getProductId();
				$products[] = $item->toArray();
		}
		$productsCollection = Mage::getModel('catalog/product')
									->getCollection()
									->addAttributeToSelect('*')
									->addIdFilter($products)
									->load();
		foreach ($collection as $item)
		{
			$item->setProduct($productsCollection->getItemById($item->getProductId()));
		}
		$collection = $collection->toArray();
		$productsCollection = $productsCollection->toArray();
		
		return $collection;
	}
	public function getCcTypeName($ccType)
	{
		return isset($this->types[$ccType]) ? $this->types[$ccType] : false;
	}	
	public function getPaymentlabel($paymethod='')
    {
        $method = "";
        foreach ($this->_getPaymentMethods() as $paymentCode=>$paymentModel)
		{
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			if($paymentCode==$paymethod)
			{
				return $paymentTitle;
				break;
			}
        }
		return $method;
    }
	
		
	public function _getPaymentMethods($store=null)
	{
		$method = Mage::getSingleton('payment/config')->getActiveMethods();
		if(is_array($method))
		{
			return $method;
		}
	}
	
	
	function response($responseArray) {
		//return $str = json_encode($responseArray);
		foreach($responseArray as $key=>$value)
		{
			if(is_array($value))
			{
				foreach($value as $k=>$v)
				{
					if(is_array($v))
					{
						foreach($v as $arrk=>$arrv)
						{
							if(is_array($arrv))
								{
									foreach($arrv as $lastk=>$lastv)
									{
										if(is_array($lastv))
										{
											foreach($lastv as $finalk=>$finalv)
											{
												if(is_array($finalv))
												{
													foreach($finalv as $finallastk=>$finallastv)
													{
														if(is_array($finallastv))
														{
															foreach($finallastv as $finalkey=>$finalval)
															{
																if(is_array($finalval))
																{
																	$finalval=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $finalval));  
																}
																else
																{
																	$finalval=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $finalval));  
																}
																$finallastv[$finalkey]=$finalval;
															}
															
														}
														else
														{
															$finallastv=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $finallastv)); 
														}
														$finalv[$finallastk]=$finallastv;
													}
													
												}
												else
												{
													$finalv=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $finalv)); 
												}
												$lastv[$finalk]=$finalv;
											}
											
										}
										else
										{
											$lastv=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $lastv)); 
	
										}
										$arrv[$lastk]=$lastv;
									}					
								}
								else
								{
									$arrv=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $arrv));
									
								}
							$v[$arrk]=$arrv;
						}					
					}
					else
					{
					
						$v=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $v)); 
					}
					$value[$k]=$v;
				}					
			}
			else
			{
				$value=utf8_encode(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value)); 
			}
			$responseArray[$key]=$value;
		}

		//print_r($responseArray);
		//die();
		$str = json_encode($responseArray);		
		 if(isset($_POST["iscompress"]) && $_POST["iscompress"]=='false')
        {
            return $str;
        }
		$cipher_alg = MCRYPT_RIJNDAEL_128;
		$key = "d994e5503a58e025";
		$hexiv="d994e5503a58e02525a8fc5eef45223e";
		$comp_string = base64_encode(gzdeflate($str,9));
		$enc_string = mcrypt_encrypt($cipher_alg, $key,$comp_string , MCRYPT_MODE_CBC, trim($this->hexToString(trim($hexiv))));
		return base64_encode($enc_string);
	}
	function stringToHex($str) {
		$hex="";
		$zeros = "";
		$len = 2 * strlen($str);
		for ($i = 0; $i < strlen($str); $i++){
			$val = dechex(ord($str{$i}));
			if( strlen($val)< 2 ) $val="0".$val;
			$hex.=$val;
		}
		for ($i = 0; $i < $len - strlen($hex); $i++){
			$zeros .= '0';
		}
		return $hex.$zeros;
	}
	function hexToString($hex) {
		$str="";
		for($i=0; $i<strlen($hex); $i=$i+2 ) {
			$temp = hexdec(substr($hex, $i, 2));
			if (!$temp) continue;
			$str .= chr($temp);
		}
		return $str;
	}
}
	



?>
<?php
/*� Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/


class Webgility_Ecc_Model_Orders
{

	private $responseArray = array();
	private $Orders = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode?$StatusCode:0;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] = $StatusMessage?$StatusMessage:'';
	}
	public function setTotalRecordFound($TotalRecordFound)
	{
		$this->responseArray['TotalRecordFound'] = $TotalRecordFound?$TotalRecordFound:0;
	}
	public function setTotalRecordSent($TotalRecordSent)
	{
		$this->responseArray['TotalRecordSent'] = $TotalRecordSent?$TotalRecordSent:0;
	}

	public function setOrders($Order)
	{
		$this->Orders[] = $Order?$Order:'';
	}

	public function getOrders()
	{
		$this->responseArray['Orders'] = $this->Orders;
		return $this->responseArray;
	}

}
?>
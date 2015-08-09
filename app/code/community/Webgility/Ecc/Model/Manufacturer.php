<?php
/*� Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/


class Webgility_Ecc_Model_Manufacturer
{
	private $Manufacturer = array();

	public function setManufacturerID($ManufacturerID)
	{
		$this->Manufacturer['ManufacturerID'] = $ManufacturerID ? $ManufacturerID :'';
	}

	public function setManufacturerName($ManufacturerName)
	{
		$this->Manufacturer['ManufacturerName'] = $ManufacturerName ? $ManufacturerName :'';
	}

	public function getManufacturer()
	{
		return $this->Manufacturer;
	}
}
?>
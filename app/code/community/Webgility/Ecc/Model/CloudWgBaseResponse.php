<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_CloudWgBaseResponse
{
	
	private $responseArray = array();
	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}
	public function getBaseResponce()
	{
		return $this->responseArray;
	}
	
	public function message()
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;
		$ModuleVersion = (array)$modulesArray['Webgility_Ecc'];
	$str = '<div><font color="green">Webgility Store Module extension installed successfully.</font></div><div>&nbsp;</div>';
		$str .= '<div>WEBGILITY ACCOUNT<br><br>To use this extension, you must have a Webgility Account. You can sign up for an account below or log in with your existing account.<br><a href="http://www.webgility.com/ecc-cloud/pricing-ecc-cloud.php" target="_blank">Sign Up</a><br>
				<a href="http://portal.webgility.com/login.php" target="_blank">Log In</a>.<br>';
		$str .= 'STORE MODULE ADDRESS<br><br>';
		$str .= "You will need to copy and paste your Webgility Store Module address into your Webgility software during the Add a Store process.<br>";
		$str .= " <div>";
		$str .= '<div> <a target="_blank" href="'.$this->eccUrl().'">'.$this->eccUrl().'</a></div><div>&nbsp;</div>';
		//$str .= '<div><br>Copy this key to the eCC Desktop software when connecting to the store : <strong>'.(string)Mage::getConfig()->getNode('global/crypt/key') .'</strong></div><div>&nbsp;</div>'; 
		$str .= '<div>Copyright &copy; 2013 Webgility Inc</div>'; 
		return $str;		
	
	} 
	public function eccUrl()
	{
		return $str1 = str_replace('index.php/','webgility/webgility-magento.php',Mage::getBaseUrl());
		
	} 
}
?>
<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_ApiController extends Mage_Core_Controller_Front_Action {  

	  

	public function savepacAction($api_url)
	{
		$product_api = 'button';
		$table_name = 'connect_config';
		Mage::getSingleton('ecc/EccApi')->savepac($product_api,$api_url,$table_name);
	}   

}
?>
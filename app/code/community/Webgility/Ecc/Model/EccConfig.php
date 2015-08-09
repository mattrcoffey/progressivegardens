<?php
/*© Copyright 2013 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_EccConfig
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
	
	public function message($product_api,$table_name)
	{
		
		$arrParams = Mage::app()->getRequest()->getParam('type');
		//echo $system_config_url= Mage::getUrl("adminhtml/ecc/admin/eccabout/");
		$about_url= Mage::helper('adminhtml')->getUrl("/admin/eccabout");
		
		$base_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$user = Mage::getSingleton('admin/session');
		$userId = $user->getUser()->getUserId();
		$userEmail_web = $user->getUser()->getEmail();
		$userFirstname_web = $user->getUser()->getFirstname();
		$userLastname_web = $user->getUser()->getLastname();
		$userUsername = $user->getUser()->getUsername();
		$userPassword = $user->getUser()->getPassword();
		$phone = Mage::getStoreConfig('general/store_information/phone'); 
		$address = Mage::getStoreConfig('general/store_information/address'); 
		$store_name = Mage::getStoreConfig('general/store_information/name');
		
		
		$eavSetId = Mage::getSingleton('core/resource')->getConnection('core_write');
		$SetIds=$eavSetId->query("SELECT * FROM `".$table_name."` where status= 1");
		
		$row = $SetIds->fetch();
		$button_text = '<input type="button" value="Create Account" onClick="create_account();" id="crt_account" />';
		
		if(!isset($arrParams) || $arrParams!='new')
		{
			if(isset($row) && $row[Token]!='')
			{
				$userEmail_web = $row[email];
				$userPassword = $row[password];
				$userEmail_web = $row[email];
				//$button_text = "";	
				//$button_text = '<a href = "'.Mage::helper("adminhtml")->getUrl("/admin/eccconfig/",array('type' => "new")).'">Already configured an account want to create new?</a>';
			}
		}
		
		$str = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
				<script>
				var $jq = jQuery.noConflict();
				function login()
				{
					
					//jq("#LoadingImage").show();
					document.getElementById("LoadingImage").style.display="block";
					document.getElementById("err_msg_1").innerHTML = "";
					var pac = document.getElementById("pac").value;
					
					if(pac=="")
					{
						document.getElementById("LoadingImage").style.display="none";
						document.getElementById("err_msg_1").innerHTML = "<b>Please enter webgility token.</b>";

					}
					else
					{
						 var data = "pac="+ encodeURIComponent(pac) +"&type=savepac"; 
							$jq.ajax({
							 url:"'.$base_path.'ecc/api/savepac",
							 data:data,
							 dataType: "jsonp", // Notice! JSONP <-- P (lowercase)
							 success:function(json){
							document.getElementById("alrdy_exist").disabled = false;
							 if(json.StatusCode == "0")
							 {
							 	document.getElementById("LoadingImage").style.display="none";
								
								document.getElementById("plugin").innerHTML="<b>Successfully saved.</b>";
								setTimeout(function(){
											window.location.href = "'.$about_url.'";
								},3000);
								
								 // do stuff with json (in this case an array)
								//document.getElementById("save_button").style.display="none";
								//document.getElementById("fedex_account").style.display="block";
							}
							else
							{
								
								document.getElementById("LoadingImage").style.display="none";
							
								document.getElementById("err_msg_1").innerHTML="<b>"+json.StatusMessage+"</b>";
								
								//alert(json.StatusMessage);
							}
							 },
							 error:function(){
							 document.getElementById("LoadingImage").style.display="none";
							 document.getElementById("alrdy_exist").disabled = false;
								 alert("Error");
							 },
						});
					}
				}
				
</script>
			

<div class="main-col-inner">
	<div id="messages"></div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#999999" style="border-bottom:#999999 solid 1px; border-right:#999999 solid 1px; border-top:#999999 solid 1px; border-left:#999999 solid 1px;">
			<tr>
				<td colspan="2" valign="middle" bgcolor="#6F8992"><span style="color:#FFFFFF; padding-left:8px; font-size:1.05em;"><b>Button configuration.</b></span></td>
			</tr>
			<tr>
				<td width="20px;"></td>
				<td>
					<div id="show_webgility" '.$style1.'>
	
						
			<form action="" method="post">
				<table width="50%" border="0" cellspacing="0" cellpadding="0">
 

 	<td colspan="2">
		</br>
		<div id="show_already" >
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td height="30" style="padding-left:30px;">Please enter webgility token. * </td>
				<td><input type="text" name="pac" id="pac" style="width: 274px;height: 30px;" value="'.$row["Token"].'" /></td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td><input type="button" value="Connect" onClick="login();" id="alrdy_exist"/><div id="plugin"></div><div id="LoadingImage" style="display: none; padding-top:15px;">
			<img src="'.$base_path.'/downloader/skin/images/ajax-loader-tr.gif">
			</div></td>
			  </tr>
 			 <tr><td>&nbsp;</td><td><div id="err_msg_1" style="color:red;"></div></td></tr>
			  <tr><td colspan="2" style="padding-left:30px;"></br> If not having webgility token login to ecc cloud. <a target="_blank" href = "https://ecc.webgility.com/">Click here</a></td></tr>
			   <tr>
				<td  height="30" style="padding-left:30px;">* Required fields </td>
				<td ></td>
			  </tr>
			</table>
			</div>
		</td>
	</tr>
</table>

			</form>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>


</td>
</tr>
</table>
</div>
</td>
</tr>
</table>

</td>
</tr>
</table>';
		return $str;		
	
	} 
public function eccUrl()
{
	return $str1 = str_replace('index.php/','webgility/webgility-magento.php',Mage::getBaseUrl());
} 
	
}
?>
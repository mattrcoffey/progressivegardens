var name_status_div = 'qbstatus_';
var wcQbStatusId = 'qbstatus_';
var wcQbStatusId2 = 'qbstatus_';
var wcQbbtnId = 'qbbtn_';

var wcMsgnotyetposted = 'Not yet posted';
var wcMsgqueued= 'Order is queued for posting.';
var wcMsgqueuedDetails = 'Order is now queued for posting Click #REFRESH# to get the updated status.'
var wcMsgError = '<font color="#FF0000"> QB Sync failed for error details go to the order details page.</font>';
var wcMsgErrorDetails  ='<font color="#FF0000">QB Sync failed.One or more of the products in the order were not found in QuickBooks.You can either map the products in QuickBooks or Change your QB Sync settings in eCC Cloud Account to enable auto-create of missing products. </font>';
var wcMsgErrorAuth  = 'You cannot post orders to QuickBooks because your eCC Cloud trial or subscription period ended, or you canceled your subscription, or there was a billing problem. Get your eCC Cloud account activated to start posting orders again.';

var MESSAGE_SUCCESS_POSTED_DETAILS = 'Order is successfully posted to QuickBooks #TRANSACTION_TYPE# No. #TRANSACTIONNO# is generated.';

function update_portal(id,base_path)
	{
		var data = "id="+id; 
			new Ajax.Request(base_path, {
			parameters: data,
			onSuccess: function(transport) 
			{
				try {
					if (transport.responseText.isJSON()) 
					{
						
						var response = transport.responseText.evalJSON();
						if(response.StatusCode == 'ERROR_ORDER_4' || response.StatusCode == 'ERROR_QB_5' || response.StatusCode == 'ERROR_AUTH')
						{
							//alert(wcMsgErrorAuth);
							alert(response.message);
						}if (response.error) 
						{
							alert(response.message);
							//window.location.reload();
						}else
						{
							window.location.reload();	
						}
						
						if(response.ajaxExpired && response.ajaxRedirect) {
							alert(response.ajaxRedirect);
							setLocation(response.ajaxRedirect);
						}
					}
				}
				catch (e) {
					alert(e);
				
				}
			}
		  }
		);
		
	}
	function update_portal_details(id,base_path)
	{
		
		
		var data = "id="+id; 
			new Ajax.Request(base_path, {
			parameters: data,
			onSuccess: function(transport) 
			{
				try {

					//alert(transport.responseText);
					if (transport.responseText.isJSON()) 
					{
						var objJSON = response = transport.responseText.evalJSON();
						//alert(response.Result.StatusMessage);
						
						if(response.StatusCode == 'ERROR_ORDER_4' || response.StatusCode == 'ERROR_QB_5' || response.StatusCode == 'ERROR_AUTH')
						{
								alert(wcMsgErrorAuth);
								return false;
						}else if (response.error) 
						{
							alert(response.message);
							//window.location.reload();
						}else
						{
							window.location.reload();	
						}
						if(response.ajaxExpired && response.ajaxRedirect) {
							setLocation(response.ajaxRedirect);
						}
					}
				}
				catch (e) {
					alert(e);
				
				}
			}
		  }
		);
		
	}
	
	function update_store(type,storeid,base_path)
	{
		var data = "type="+type+"&storeid="+storeid;
			new Ajax.Request(base_path.trim(), {
			parameters: data,
			onSuccess: function(transport) 
			{
				try {
						
					if (transport.responseText.isJSON()) 
					{
						var response = transport.responseText.evalJSON()
						if (response.error) 
						{
							alert(response.message);
							//window.location.reload();
						}else
						{
							window.location.reload();	
						}
						
						if(response.ajaxExpired && response.ajaxRedirect) {
							alert(response.ajaxRedirect);
						//	setLocation(response.ajaxRedirect);
						}
					}
				}
				catch (e) {
					alert(e);
				
				}
			}
		  }
		);
		
	}
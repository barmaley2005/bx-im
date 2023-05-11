BX.ready(function(){

	BX.bind(BX("generate_coupon"), "click", function(){
		
		
		var data = new Object();
		data["ACTION"] = 'ADD_COUPON';
		BX.ajax({
			url: '/bitrix/components/logictim/bonus.history/ajax.php',
			method: 'POST',
			data: data,
			dataType: 'json',
			onsuccess: function(result) {
				if(result.COUPON && result.COUPON != '')
				{
					BX.adjust(BX('partnet_coupon'), {text: result.COUPON});
				}
				//console.log(result);
			}
		});
		
	});
	
	BX.bind(BX("enter_coupon"), "click", function(event){
		
		event.preventDefault();
		
		//alert();
		
		var data = new Object();
		data["ACTION"] = 'ENTER_COUPON';
		data["COUPON_CODE"] = BX('enter_coupon_code').value;
		BX.ajax({
			url: '/bitrix/components/logictim/bonus.history/templates/.default_custom/ajax.php',
			method: 'POST',
			data: data,
			dataType: 'json',
			onsuccess: function(result) {
				console.log(result);
				if(result.COUPON && result.COUPON != '')
				{
					BX.adjust(BX('partnet_coupon'), {text: result.COUPON});
				}
				if(result.ERROR_TEXT && result.ERROR_TEXT != '')
				{
					BX.adjust(BX('coupon_error'), {text: result.ERROR_TEXT});
				}
				//console.log(result);
			}
		});
		
	}); 
	
	

});



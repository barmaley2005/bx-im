BX.ready(function(){
	if(BX('bx-panel') && lt_bonus_warning)
	{
		var message = lt_bonus_warning;
		
		var alertBlock = BX.create('DIV', {props: {id: 'lt_panel_alert', className: 'lt_panel_alert'}, html: message});
		
		var bxPanel = BX('bx-panel');
		if(BX.hasClass(BX('bx-panel'), 'adm-header'))
			var adminPanel = true;
		else
			var adminPanel = false;
		
		if(adminPanel == false)
			BX('bx-panel').prepend(alertBlock);
		else
			BX('bx-panel').insertAdjacentElement('beforebegin', alertBlock);
	}
});
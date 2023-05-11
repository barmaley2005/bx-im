<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>

<? if($arParams["AJAX"] == 'Y'):?>

	<script type="text/javascript">
		BX.ready(function(){
			var arBonusItems_<?=$arParams["RAND"]?> = <?=CUtil::PhpToJSObject($arResult, false, true)?>;
			var arBonus_<?=$arParams["RAND"]?> = null;
			BX.ajax({
				url: '<?=$componentPath?>/ajax.php',
				method: 'POST',
				data: arBonusItems_<?=$arParams["RAND"]?>,
				dataType: 'json',
				onsuccess: function(result) {
					arBonus_<?=$arParams["RAND"]?> = result;
					for(id in result.ITEMS)
					{
						var item = result.ITEMS[id];
						if($("#lb_ajax_"+id) && item.VIEW_BONUS > 0)
				 			$("#lb_ajax_"+id).text('+ '+item.ADD_BONUS+' '+result.TEXT.TEXT_BONUS_FOR_ITEM);
						if($(".lb_ajax_"+id) && item.VIEW_BONUS > 0)
				 			$(".lb_ajax_"+id).text('+ '+item.ADD_BONUS+' '+result.TEXT.TEXT_BONUS_FOR_ITEM);
					}
				}
			});
			
			BX.addCustomEvent('onAsproSkuSetPrice', function(eventdata){
				var productBlock = eventdata.product[0];
				if($(productBlock).find(".lb_bonus").attr('data-item') > 0)
				{
					var bonusBlock = $(productBlock).find(".lb_bonus");
					var offer_id = eventdata.offer.ID;
					if(arBonus_<?=$arParams["RAND"]?> != null && arBonus_<?=$arParams["RAND"]?>['ITEMS'][offer_id] && $(bonusBlock))
					{
						var offer_item = arBonus_<?=$arParams["RAND"]?>['ITEMS'][offer_id];
						if(offer_item.VIEW_BONUS > 0)
							$(bonusBlock).text('+ '+offer_item.VIEW_BONUS+' '+'<?=COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>');
						else
							$(bonusBlock).text('');
					}
				}
			})
		});
	</script>
    
<? else:?>

	<script>
		BX.ready(function(){
            var arBonus_<?=$arParams["RAND"]?> = <?=CUtil::PhpToJSObject($arResult["ITEMS_BONUS"], false, true)?>;
			
			for(id in arBonus_<?=$arParams["RAND"]?>) {
                var item = arBonus_<?=$arParams["RAND"]?>[id];
                
				 if($("#lb_ajax_"+id) && item.VIEW_BONUS > 0)
				 	$("#lb_ajax_"+id).text('+ '+item.VIEW_BONUS+' '+'<?=COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>');
				 if($(".lb_ajax_"+id) && item.VIEW_BONUS > 0)	
					$(".lb_ajax_"+id).text('+ '+item.VIEW_BONUS+' '+'<?=COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>');
            }
			
			BX.addCustomEvent('onAsproSkuSetPrice', function(eventdata){
				var productBlock = eventdata.product[0];
				if($(productBlock).find(".lb_bonus").attr('data-item') > 0)
				{
					var bonusBlock = $(productBlock).find(".lb_bonus");
					var offer_id = eventdata.offer.ID;
					if(arBonus_<?=$arParams["RAND"]?>[offer_id] && $(bonusBlock))
					{
						var offer_item = arBonus_<?=$arParams["RAND"]?>[offer_id];
						if(offer_item.VIEW_BONUS > 0)
							$(bonusBlock).text('+ '+offer_item.VIEW_BONUS+' '+'<?=COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>');
						else
							$(bonusBlock).text('');
					}
				}
			})
			
        });
    </script>
    
<? endif;?>
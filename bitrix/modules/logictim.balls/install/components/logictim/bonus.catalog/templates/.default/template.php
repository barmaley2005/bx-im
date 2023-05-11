<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);?>


<? if($arParams["AJAX"] == 'Y'):?>

	<script type="text/javascript">
		BX.ready(function(){
			var arBonus_<?=$arParams["RAND"]?> = <?=CUtil::PhpToJSObject($arResult, false, true)?>;
			BX.ajax({
				url: '<?=$componentPath?>/ajax.php',
				method: 'POST',
				data: arBonus_<?=$arParams["RAND"]?>,
				dataType: 'json',
				onsuccess: function(result) {
					console.log(result);
					for(id in result.ITEMS)
					{
						var item = result.ITEMS[id];
						if(BX('lb_ajax_'+id) && item.ADD_BONUS > 0)
							BX.adjust(BX('lb_ajax_'+id), {text: '+'+item.ADD_BONUS+' '+result.TEXT.TEXT_BONUS_FOR_ITEM});
					}
				}
			});
		});
	</script>
    
<? else:?>

	<script>
        BX.ready(function(){
            var arBonus_<?=$arParams["RAND"]?> = <?=CUtil::PhpToJSObject($arResult["ITEMS_BONUS"], false, true)?>;
            for(id in arBonus_<?=$arParams["RAND"]?>) {
                var item = arBonus_<?=$arParams["RAND"]?>[id];
                //console.log(item);
                if(BX('lb_ajax_'+id) && item.VIEW_BONUS > 0)
                    BX.adjust(BX('lb_ajax_'+id), {text: '+'+item.VIEW_BONUS+' '+'<?=COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '')?>'});
            }
        
        });
    </script>
    

<? endif;?>


<? //echo '<pre>'; print_r($arResult); echo '</pre>';?>
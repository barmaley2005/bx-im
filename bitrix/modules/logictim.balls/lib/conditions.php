<?
namespace Logictim\Balls;

class Conditions
{
	public static function SaveConditions($requestConditions)
	{
		$arIblocks = array();
		foreach($requestConditions as $arCondition):
			if(strpos($arCondition["controlId"], 'CondIBProp') !== false)
			{
				$arExp = explode(':', $arCondition["controlId"]);
				$arIblocks[] = $arExp[1];
			}
		endforeach;
		
		$arIBProps = array();
		foreach($arIblocks as $iblockId):
			$dbIbProps = \CIBlock::GetProperties($iblockId);
			while($dbProp = $dbIbProps->Fetch())
			{
				$arIBProps[$dbProp["ID"]] = $dbProp;
			}
		endforeach;
		
		$arConditions = array();		
		$arLevels = array(0=>0, 1=>0, 2=>0);
		foreach($requestConditions as $key => $arCond):
			$arKey = explode('__', $key);
			$level = count($arKey)-1;
			
			if($level < $lastLevel)
			{
				foreach($arLevels as $keyL => $ValL):
					if($keyL > $level)
						$arLevels[$keyL] = 0;
				endforeach;
			}
			
			
			$id = $arLevels[$level];
			
			$arBlock = array('id'=>$id, 'controlId'=>$arCond['controlId'], 'values'=>array());
			foreach($arCond as $keyVal => $val):
				if($keyVal == 'controlId')
					continue;
					
				if(is_array($val))
				{
					$arVal = $val;
					$val = array();
					foreach($arVal as $valAr):
						if($valAr != '')
							$val[] = $valAr;
					endforeach;
					$val = array_unique($val);
				}
				
				if($keyVal == 'value')
				{
					if(strpos($arCond['controlId'], 'CondIBProp') !== false)
					{
						$arExp = explode(':', $arCondition["controlId"]);
						$propertyId = $arExp[2];
						if($arIBProps[$propertyId]["PROPERTY_TYPE"] == 'N')
						{
							$val =str_replace(',', '.', $val);
							$val =(float)$val;
							$val =(string)$val;
						}
					}
					
					if($arCond['controlId'] == 'price' || $arCond['controlId'] == 'cartSum'  || $arCond['controlId'] == 'orderSum')
					{
						$val =str_replace(',', '.', $val);
						$val =(float)$val;
						$val =(string)$val;
					}
				}
				elseif(is_array($keyVal))
				{
					if($keyVal['controlId'] == 'bonus' || $keyVal['controlId'] == 'ordersSum')
					{
						$val =str_replace(',', '.', $val);
						$val =(float)$val;
						$val =(string)$val;
					}
				}
				
	
				$arBlock['values'][$keyVal] = $val;
			endforeach;
			
			if(is_array($arBlock['values']['value']) && empty($arBlock['values']['value']) && $level > 0)
				continue;
				
			if(!isset($arBlock['children']))
				$arBlock['children'] = array();
			
			if($level == 0)
				$arConditions = array('id'=>$id, 'controlId'=>$arCond['controlId'], 'children'=>array());
			elseif($level == 1)
				$arConditions['children'][$id] = $arBlock;
			elseif($level == 2)
				$arConditions['children'][$arLevels[$level-1]-1]['children'][$id] = $arBlock;
			
			
			$lastLevel = $level;
			$arLevels[$level] = $arLevels[$level]+1;
		endforeach;
		
		return $arConditions;
		
		//$arConditions = \Bitrix\Main\Web\Json::encode($arConditions);
	}
	
	public static function SetLabelsProfile($arProfileConditions)
	{
		if(empty($arProfileConditions["children"]))
			return $arProfileConditions;
		
		$usersId = array();	 $arUsers = array();
		foreach($arProfileConditions["children"] as $condition):
			if($condition["controlId"] == 'MainUserId' && !empty($condition["values"]["value"]) || $condition["controlId"] == 'PartnerUserId' && !empty($condition["values"]["value"]))
				$usersId[] = array_merge($usersId, $condition["values"]["value"]);
		endforeach;
		
		if(!empty($usersId))
		{
			$DBUser = \CUser::GetList(($by="ID"),($order="desc"),array("ID" => $usersId), array());
			while($arUser = $DBUser->Fetch())
			{
				$userName = trim(($arUser["LAST_NAME"] != '' ? $arUser["LAST_NAME"] : '').($arUser["NAME"] != '' ? ' '.$arUser["NAME"] : ''));
				$userLogin = $arUser["LOGIN"];
				$arUsers[$arUser["ID"]]['LABEL'] = ($userName == '' ? $userLogin : $userName);
			}
		}
		foreach($arProfileConditions["children"] as $keyCondition => $condition):
			if($condition["controlId"] == 'MainUserId' && !empty($condition["values"]["value"]) || $condition["controlId"] == 'PartnerUserId' && !empty($condition["values"]["value"]))
			{
				foreach($condition["values"]["value"] as $val):
					$arProfileConditions["children"][$keyCondition]["labels"]["value"][] = $arUsers[$val]["LABEL"];
				endforeach;
			}
		endforeach;
		
		return $arProfileConditions;
	}
	
	public static function SetLabelsProduct($arProductConditions)
	{
		if(empty($arProductConditions["children"]))
			return $arProductConditions;
			
		$sectionsId = array();
		$elementsId = array();
		$arProps = array();
		$arPropsValIds = array();
		foreach($arProductConditions["children"] as $conGroup):
			if(empty($conGroup["children"]))
				continue;
			foreach($conGroup["children"] as $cond):
				if($cond["controlId"] == 'product_categoty' && $cond["values"]["value"] > 0)
					$sectionsId[] = $cond["values"]["value"];
				if($cond["controlId"] == 'product' && !empty($cond["values"]["value"]))
					$elementsId = array_merge($elementsId, $cond["values"]["value"]);
				
				//Props
				$pos = strpos($cond["controlId"], 'CondIBProp');
				if($pos !== false)
				{
					$arPropParams = explode(':', $cond["controlId"]);
					$propIblockId = $arPropParams[1];
					$propId = $arPropParams[2];
					if(!isset($arProps[$propId]))
					{
						$arPropInfo = \CIBlockProperty::GetByID($propId, $propIblockId);
						$arPropInfo = $arPropInfo->GetNext();
						if($arPropInfo['PROPERTY_TYPE'] == 'E')
							$arProps[$propId] = $arPropInfo;
					}
					
					if(isset($arProps[$propId]) && $arProps[$propId]['PROPERTY_TYPE'] == 'E')
						$arPropsValIds[] = $cond["values"]['value'];
					
					
				}
			endforeach;
		endforeach;
		
		$arPropsValIds = array_unique($arPropsValIds);
		$sectionsId = array_unique($sectionsId);
		$elementsId = array_unique($elementsId);
		
		if(!empty($sectionsId)):
			$dbSections = \CIBlockSection::GetList(array('id' => 'asc'), array('ID'=>$sectionsId), false, array("ID", "NAME"));
			$arSections = array();
			while($arSection = $dbSections->fetch())
			{
				$arSections[$arSection["ID"]] = $arSection;
			}
		endif;
		if(!empty($elementsId)):
			$DBproducts = \CIBlockElement::GetList(array("ID"=>"ASC"), array("ID" => $elementsId), false, false, array("ID", "NAME", "IBLOCK_ID"));
			$arElements = array();
			while($el = $DBproducts->GetNextElement())
			{
				$arProd = $el->GetFields();
				$arElements[$arProd["ID"]] = $arProd;
			}
		endif;
		
		if(!empty($arPropsValIds))
		{
			$DBpropElements = \CIBlockElement::GetList(array("ID"=>"ASC"), array("ID" => $arPropsValIds), false, false, array("ID", "NAME", "IBLOCK_ID"));
			$arPropElements = array();
			while($elp = $DBpropElements->GetNextElement())
			{
				$arPropEl = $elp->GetFields();
				$arPropElements[$arPropEl["ID"]] = $arPropEl;
			}
		}
		
		
		foreach($arProductConditions["children"] as $keyGroup => $conGroup):
			if(empty($conGroup["children"]))
				continue;
			foreach($conGroup["children"] as $keyKond => $cond):
				if($cond["controlId"] == 'product_categoty' && $cond["values"]["value"] > 0)
					$arProductConditions["children"][$keyGroup]["children"][$keyKond]["labels"] = array("value" => $arSections[$cond["values"]["value"]]["NAME"]);
				if($cond["controlId"] == 'product' && !empty($cond["values"]["value"]))
				{
					foreach($cond["values"]["value"] as $val):
						$arProductConditions["children"][$keyGroup]["children"][$keyKond]["labels"]["value"][] = $arElements[$val]["NAME"];
					endforeach;
				}
				
				$pos = strpos($cond["controlId"], 'CondIBProp');
				if($pos !== false)
				{
					$arPropParams = explode(':', $cond["controlId"]);
					$propIblockId = $arPropParams[1];
					$propId = $arPropParams[2];
					if(isset($arProps[$propId]) && !empty($cond["values"]["value"]))
					{
						$arProductConditions["children"][$keyGroup]["children"][$keyKond]["labels"] = array('value' => $arPropElements[$cond['values']['value']]["NAME"].' ['.$cond["values"]["value"].']');
					}
					
				}
					
			endforeach;
		endforeach;
		return $arProductConditions;
	}
}



?>
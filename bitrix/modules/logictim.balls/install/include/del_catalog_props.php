<?
//delette propertys from ibloks
$catalogsId = array();

if(CModule::IncludeModule("iblock"))
{
	$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "CODE"=>'LOGICTIM_BONUS_BALLS'));
	while ($prop_fields = $properties->GetNext())
	{
		CIBlockProperty::Delete($prop_fields["ID"]);
	}
	$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "CODE"=>'LOGICTIM_BONUS_NO'));
	while ($prop_fields = $properties->GetNext())
	{
		CIBlockProperty::Delete($prop_fields["ID"]);
	}
	$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "CODE"=>'LOGICTIM_BONUS_NO_PAY'));
	while ($prop_fields = $properties->GetNext())
	{
		CIBlockProperty::Delete($prop_fields["ID"]);
	}
}
?>
<?php

namespace DevBx\Forms\WebForm;

class WOFormUserFieldCollection extends WOCollection
{
    public function __construct()
    {
        parent::__construct(WOFormUserField::class);
    }

    /**
     * @param $id
     * @return WOFormUserField[]
     */
    public function getUserFieldsBySystemId($id): array
    {
        $result = [];

        foreach ($this as $userField)
        {
            /* @var WOFormUserField $userField */

            if ($userField->systemId == $id)
                $result[] = $userField;
        }

        return $result;
    }

    /**
     * @param $name
     * @return WOFormUserField|false|null
     */
    public function getUserFieldByName($name)
    {
        foreach ($this as $userField)
        {
            /* @var WOFormUserField $userField */

            if ($userField->userFieldName == $name)
                return $userField;
        }

        return null;
    }
}
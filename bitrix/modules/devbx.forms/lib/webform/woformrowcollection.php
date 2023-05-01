<?php

namespace DevBx\Forms\WebForm;

use DevBx\Forms\WebForm\Fields;
use DevBx\Forms\WebForm\Types\ConditionType;

class WOFormRowCollection extends WOCollection
{
    public function __construct()
    {
        parent::__construct(WOFormRow::class);
    }

    /**
     * @return Fields\Base[]
     */
    public function getVisibleItems(): array
    {
        $result = [];

        foreach ($this as $row)
        {
            /* @var WOFormRow $row */

            $items = $row->items;

            foreach ($items as $formField)
            {
                /* @var WOFormField $formField */

                $obField = $formField->getEntity();

                $showRule = $obField->showRule;
                if ($showRule instanceof ConditionType && !$showRule->checkCondition($obField->getForm()))
                    continue;

                if ($obField instanceof Fields\SectionField)
                {
                    array_push($result, ...$obField->layout->rows->getVisibleItems());
                }

                $result[] = $obField;
            }
        }

        return $result;
    }

}
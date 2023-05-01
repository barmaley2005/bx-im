<?php

namespace DevBx\Forms\WebForm;

class WOFormFieldCollection extends WOCollection
{
    public function __construct()
    {
        parent::__construct(WOFormField::class);
    }
}
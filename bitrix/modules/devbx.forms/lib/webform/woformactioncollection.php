<?php

namespace DevBx\Forms\WebForm;

class WOFormActionCollection extends WOCollection
{
    public function __construct()
    {
        parent::__construct(WOFormAction::class);
    }
}
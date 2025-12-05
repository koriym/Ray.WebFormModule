<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Ray\WebFormModule\Annotation\FormValidation;

class FakeInvalidController2
{
    /** @var mixed */
    private $form = null;

    #[FormValidation]
    public function createAction(): void
    {
    }
}

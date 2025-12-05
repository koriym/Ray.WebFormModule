<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Ray\WebFormModule\Annotation\FormValidation;

class FakeInvalidInstanceController
{
    /** @var mixed */
    private $form;

    #[FormValidation]
    public function createAction(): void
    {
    }
}

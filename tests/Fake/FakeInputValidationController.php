<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\InputValidation;

class FakeInputValidationController
{
    protected FormInterface $form;

    #[Inject]
    public function setForm(#[Named('contact_form')] FormInterface $form): void
    {
        $this->form = $form;
    }

    #[InputValidation]
    public function createAction(string $name): void
    {
    }
}

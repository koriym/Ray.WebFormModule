<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Ray\WebFormModule\Annotation\FormValidation;

class FakeInvalidController3
{
    public FormInterface $form;

    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    #[FormValidation(onFailure: 'missing_method')]
    public function createAction(string $name): void
    {
    }
}

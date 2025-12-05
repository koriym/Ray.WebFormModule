<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\WebFormModule\Annotation\FormValidation;

class FakeController
{
    protected FormInterface $form;

    #[Inject]
    public function setForm(#[Named('contact_form')] FormInterface $form): void
    {
        $this->form = $form;
    }

    #[FormValidation]
    public function createAction(string $name): string
    {
        return '201';
    }

    public function createActionValidationFailed(): string
    {
        return '400';
    }
}

<?php

namespace Ray\FormModule;

use Aura\Session\Phpfunc;
use Ray\Di\AbstractModule;

class FakeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(Phpfunc::class)->to(FakePhpFunc::class);
        $this->install(new FormModule());
        $this->bind(FormInterface::class)->annotatedWith('contact_form')->to(FakeForm::class);
    }
}

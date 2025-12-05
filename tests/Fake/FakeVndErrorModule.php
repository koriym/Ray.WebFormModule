<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Ray\Di\AbstractModule;

class FakeVndErrorModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new FormVndErrorModule());
        $this->install(new FakeModule());
    }
}

<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use PHPUnit\Framework\TestCase;

class FormFactoryTest extends TestCase
{
    private FormFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new FormFactory();
    }

    public function testNewInstance(): void
    {
        $form = $this->factory->newInstance(FakeMiniForm::class);
        $this->assertInstanceOf(AbstractForm::class, $form);
    }
}

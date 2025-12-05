<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use PHPUnit\Framework\TestCase;

class AbstractAuraFormTest extends TestCase
{
    private AbstractForm $form;

    protected function setUp(): void
    {
        parent::setUp();
        $this->form = (new FormFactory())->newInstance(FakeForm::class);
    }

    public function testForm(): void
    {
        $formHtml = $this->form->form();
        $this->assertSame('<form method="post" enctype="multipart/form-data">', $formHtml);
    }

    public function testAntiCsrfForm(): void
    {
        $this->form->setAntiCsrf(new FakeAntiCsrf());
        $this->form->postConstruct();
        $formHtml = $this->form->form();
        $this->assertSame('<form method="post" enctype="multipart/form-data"><input type="hidden" name="__csrf_token" value="goodvalue" />' . PHP_EOL, $formHtml);
    }

    public function testInput(): void
    {
        $name = $this->form->input('name');
        $this->assertSame('<input id="name" type="text" name="name" />' . PHP_EOL, (string) $name);
    }

    public function testError(): string
    {
        $this->form->fill([]);
        $data = ['name' => '@invalid@'];
        $isValid = $this->form->apply($data);
        $this->assertFalse($isValid);
        $error = $this->form->error('name');
        $this->assertSame('Name must be alphabetic only.', $error);

        return (string) $this->form;
    }

    /**
     * @depends testError
     */
    public function testInputDataRemainedOnValidationFailure(string $html): void
    {
        $expected = '<input id="name" type="text" name="name" value="@invalid@" />';
        $this->assertStringContainsString($expected, $html);
    }
}

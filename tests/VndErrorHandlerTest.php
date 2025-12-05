<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\WebFormModule\Exception\ValidationException;

class VndErrorHandlerTest extends TestCase
{
    private FakeController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = (new Injector(new FakeVndErrorModule(), __DIR__ . '/tmp'))->getInstance(FakeController::class);
    }

    public function testValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->controller->createAction('');
    }

    public function testValidationExceptionError(): void
    {
        try {
            $this->controller->createAction('');
        } catch (ValidationException $e) {
            $vndError = (string) $e->error;
            $this->assertSame('{
    "message": "Validation failed",
    "path": "",
    "validation_messages": {
        "name": [
            "Name must be alphabetic only."
        ]
    }
}', $vndError);
        }
    }

    public function testVndErrorAttribute(): void
    {
        /** @var FakeControllerVndError $controller */
        $controller = (new Injector(new FakeVndErrorModule()))->getInstance(FakeControllerVndError::class);
        try {
            $controller->createAction('');
        } catch (ValidationException $e) {
            $vndError = (string) $e->error;
            $this->assertSame('{
    "message": "foo validation failed",
    "path": "/path/to/error",
    "logref": "a1000",
    "validation_messages": {
        "name": [
            "Name must be alphabetic only."
        ]
    }
}', $vndError);
        }
    }
}

<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use PHPUnit\Framework\TestCase;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\WebFormModule\Exception\InvalidFormPropertyException;
use Ray\WebFormModule\Exception\InvalidOnFailureMethod;
use Ray\WebFormModule\Exception\ValidationException;

class AuraInputInterceptorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getMethodInvocation(string $method, array $submit, ?FailureHandlerInterface $handler = null): ReflectiveMethodInvocation
    {
        $handler = $handler ?? new OnFailureMethodHandler();
        $object = $this->getController($submit);

        return new ReflectiveMethodInvocation(
            $object,
            $method,
            $submit,
            [
                new AuraInputInterceptor($handler),
            ]
        );
    }

    public function getController(array $submit): FakeController
    {
        $controller = new FakeController();
        $fakeForm = (new FormFactory())->newInstance(FakeForm::class);
        $fakeForm->setSubmit($submit);
        $controller->setForm($fakeForm);

        return $controller;
    }

    public function proceed(object $controller): void
    {
        $invocation = new ReflectiveMethodInvocation(
            $controller,
            'createAction',
            [],
            [
                new AuraInputInterceptor(new OnFailureMethodHandler()),
            ]
        );
        $invocation->proceed();
    }

    public function testProceedFailed(): void
    {
        $invocation = $this->getMethodInvocation('createAction', []);
        $result = $invocation->proceed();
        $this->assertSame('400', $result);
    }

    public function testProceed(): void
    {
        $invocation = $this->getMethodInvocation('createAction', ['BEAR']);
        $result = $invocation->proceed();
        $this->assertSame('201', $result);
    }

    /** @return array<array{object}> */
    public static function invalidControllerProvider(): array
    {
        return [
            [new FakeInvalidController1()],
            [new FakeInvalidController2()],
        ];
    }

    /**
     * @dataProvider invalidControllerProvider
     */
    public function testInvalidFormPropertyByMissingProperty(object $controller): void
    {
        $this->expectException(InvalidFormPropertyException::class);
        $this->proceed($controller);
    }

    public function testInvalidFormPropertyException(): void
    {
        $this->expectException(InvalidOnFailureMethod::class);
        $controller = new FakeInvalidController3();
        $fakeForm = (new FormFactory())->newInstance(FakeForm::class);
        $fakeForm->setSubmit(['name' => '']);
        $controller->setForm($fakeForm);
        $this->proceed($controller);
    }

    public function testInvalidFormPropertyByInvalidInstance(): void
    {
        $this->expectException(InvalidFormPropertyException::class);
        $object = new FakeInvalidController1();
        $invocation = new ReflectiveMethodInvocation(
            $object,
            'createAction',
            ['name' => ''],
            [
                new AuraInputInterceptor(new OnFailureMethodHandler()),
            ]
        );
        $invocation->proceed();
    }

    public function testProceedWithVndErrorHandler(): void
    {
        try {
            $invocation = $this->getMethodInvocation('createAction', [], new VndErrorHandler());
            $invocation->proceed();
        } catch (ValidationException $e) {
            $this->assertInstanceOf(FormValidationError::class, $e->error);
            $json = (string) $e->error;
            $this->assertSame('{
    "message": "Validation failed",
    "path": "",
    "validation_messages": {
        "name": [
            "Name must be alphabetic only."
        ]
    }
}', $json);
        }
    }
}

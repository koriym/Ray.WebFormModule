<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use Aura\Session\CsrfTokenFactory;
use Aura\Session\Phpfunc;
use Aura\Session\Randval;
use Aura\Session\SegmentFactory;
use Aura\Session\Session;
use Iterator;
use PHPUnit\Framework\TestCase;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\WebFormModule\Exception\CsrfViolationException;
use Ray\WebFormModule\Exception\ValidationException;
use ReflectionProperty;

class AbstractFormTest extends TestCase
{
    private AbstractForm $form;

    protected function setUp(): void
    {
        parent::setUp();
        $this->form = (new FormFactory())->newInstance(FakeMiniForm::class);
    }

    public function getMethodInvocation(array $arguments): ReflectiveMethodInvocation
    {
        $fakeForm = (new FormFactory())->newInstance(FakeMiniForm::class);
        $controller = new FakeController();
        $controller->setForm($fakeForm);
        $interceptor = new AuraInputInterceptor(new VndErrorHandler());

        return new ReflectiveMethodInvocation(
            $controller,
            'createAction',
            $arguments,
            [
                $interceptor,
            ]
        );
    }

    public function testApply(): void
    {
        $data = ['name' => 'aaa'];
        $isValid = $this->form->apply($data);
        $this->assertTrue($isValid);
    }

    public function testSubmit(): void
    {
        $this->expectException(ValidationException::class);
        $invocation = $this->getMethodInvocation(['na']);
        $invocation->proceed();
    }

    public function testErrorReturnEmpty(): void
    {
        $result = $this->form->error('name');
        $expected = '';
        $this->assertSame($expected, $result);
    }

    public function testClone(): void
    {
        $form = clone $this->form;
        (new ReflectionProperty($form, 'filter'))->setAccessible(true);
        (new ReflectionProperty($this->form, 'filter'))->setAccessible(true);
        $this->assertNotSame(spl_object_hash($form), spl_object_hash($this->form));
    }

    public function testGetIterator(): void
    {
        $iterator = $this->form->getIterator();
        $this->assertInstanceOf(Iterator::class, $iterator);
    }

    public function testAntiCsrfViolation(): void
    {
        $this->expectException(CsrfViolationException::class);
        $session = new Session(
            new SegmentFactory(),
            new CsrfTokenFactory(new Randval(new Phpfunc())),
            new FakePhpfunc(),
            []
        );
        $this->form->setAntiCsrf(new AntiCsrf($session, false));
        $this->form->apply([]);
    }
}

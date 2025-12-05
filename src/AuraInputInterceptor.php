<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\WebFormModule\Annotation\AbstractValidation;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\Exception\InvalidFormPropertyException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class AuraInputInterceptor implements MethodInterceptor
{
    protected FailureHandlerInterface $failureHandler;

    public function __construct(FailureHandlerInterface $handler)
    {
        $this->failureHandler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation): mixed
    {
        $object = $invocation->getThis();
        $formValidation = $this->getValidationAttribute($invocation->getMethod());
        assert($formValidation instanceof AbstractValidation);
        $form = $this->getFormProperty($formValidation, $object);
        $data = $form instanceof SubmitInterface ? $form->submit() : $this->getNamedArguments($invocation);
        $isValid = $this->isValid($data, $form);
        if ($isValid === true) {
            return $invocation->proceed();
        }

        return $this->failureHandler->handle($formValidation, $invocation, $form);
    }

    private function getValidationAttribute(ReflectionMethod $method): ?AbstractValidation
    {
        $attributes = $method->getAttributes(AbstractValidation::class, ReflectionAttribute::IS_INSTANCEOF);
        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * @throws Exception\CsrfViolationException
     *
     * @return bool
     */
    public function isValid(array $submit, AbstractForm $form): bool
    {
        return $form->apply($submit);
    }

    /**
     * Return arguments as named arguments.
     *
     * @return array<string, mixed>
     */
    private function getNamedArguments(MethodInvocation $invocation): array
    {
        $submit = [];
        $params = $invocation->getMethod()->getParameters();
        $args = $invocation->getArguments()->getArrayCopy();
        foreach ($params as $param) {
            $arg = array_shift($args);
            $submit[$param->getName()] = $arg;
        }

        if (isset($_POST[AntiCsrf::TOKEN_KEY])) {
            $submit[AntiCsrf::TOKEN_KEY] = $_POST[AntiCsrf::TOKEN_KEY];
        }

        return $submit;
    }

    /**
     * Return form property
     */
    private function getFormProperty(AbstractValidation $formValidation, object $object): AbstractForm
    {
        if (! property_exists($object, $formValidation->form)) {
            throw new InvalidFormPropertyException($formValidation->form);
        }

        $prop = (new ReflectionClass($object))->getProperty($formValidation->form);
        $form = $prop->getValue($object);
        if (! $form instanceof AbstractForm) {
            throw new InvalidFormPropertyException($formValidation->form);
        }

        return $form;
    }
}

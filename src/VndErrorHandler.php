<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use Ray\Aop\MethodInvocation;
use Ray\WebFormModule\Annotation\AbstractValidation;
use Ray\WebFormModule\Annotation\VndError;
use Ray\WebFormModule\Exception\ValidationException;
use ReflectionMethod;

final class VndErrorHandler implements FailureHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(AbstractValidation $formValidation, MethodInvocation $invocation, AbstractForm $form): never
    {
        unset($formValidation);
        $vndError = $this->getVndErrorAttribute($invocation->getMethod());
        $error = new FormValidationError($this->makeVndError($form, $vndError));

        throw new ValidationException('Validation failed.', 400, null, $error);
    }

    private function getVndErrorAttribute(ReflectionMethod $method): ?VndError
    {
        $attributes = $method->getAttributes(VndError::class);
        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /** @return array<string, mixed> */
    private function makeVndError(AbstractForm $form, ?VndError $vndError = null): array
    {
        $body = ['message' => 'Validation failed'];
        $body['path'] = $_SERVER['PATH_INFO'] ?? '';
        $body['validation_messages'] = $form->getFailureMessages();
        $body = $vndError ? $this->optionalAttribute($vndError) + $body : $body;

        return $body;
    }

    /** @return array<string, string> */
    private function optionalAttribute(VndError $vndError): array
    {
        $body = [];
        if ($vndError->message !== '') {
            $body['message'] = $vndError->message;
        }

        if ($vndError->path !== '') {
            $body['path'] = $vndError->path;
        }

        if ($vndError->logref !== '') {
            $body['logref'] = $vndError->logref;
        }

        return $body;
    }
}

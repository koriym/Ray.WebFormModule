<?php
/**
 * This file is part of the Ray.WebFormModule package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\WebFormModule;

use Aura\Input\Form;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\Exception\InvalidArgumentException;
use Ray\WebFormModule\Exception\InvalidFormPropertyException;
use Ray\WebFormModule\Exception\InvalidOnFailureMethod;

class AuraInputInterceptor implements MethodInterceptor
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader Annotation reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        /* @var $formValidation FormValidation */
        $formValidation = $this->reader->getMethodAnnotation($invocation->getMethod(), FormValidation::class);
        $form = $this->getFormProperty($formValidation, $object);
        $isValid = $this->isValidForm($form->submit(), $form);
        if ($isValid === true) {
            // validation   success
            return $invocation->proceed();
        }
        $args = (array) $invocation->getArguments();
        $object = $invocation->getThis();
        if (! method_exists($object, $formValidation->onFailure)) {
            throw new InvalidOnFailureMethod($formValidation->onFailure);
        }

        return call_user_func_array([$invocation->getThis(), $formValidation->onFailure], $args);
    }

    /**
     * @param array $submit
     * @param Form  $form
     *
     * @return bool
     * @throws \Aura\Input\Exception\CsrfViolation
     */
    public function isValidForm(array $submit, Form $form)
    {
        $form->fill($submit);
        $isValid = $form->filter();

        return $isValid;
    }

    /**
     * Return form property
     *
     * @param FormValidation $formValidation
     * @param object         $object
     *
     * @return mixed
     */
    private function getFormProperty(FormValidation $formValidation, $object)
    {
        if (! property_exists($object, $formValidation->form)) {
            throw new InvalidFormPropertyException($formValidation->form);
        }
        $prop = (new \ReflectionClass($object))->getProperty($formValidation->form);
        $prop->setAccessible(true);
        $form = $prop->getValue($object);
        if (! $form instanceof FormInterface) {
            throw new InvalidFormPropertyException($formValidation->form);
        }

        return $form;
    }
}

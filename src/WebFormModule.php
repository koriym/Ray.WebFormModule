<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Aura\Filter\FilterFactory;
use Aura\Html\HelperLocatorFactory;
use Aura\Input\AntiCsrfInterface;
use Aura\Input\Builder;
use Aura\Input\BuilderInterface;
use Aura\Input\Filter;
use Aura\Input\FilterInterface;
use Aura\Session\CsrfTokenFactory;
use Aura\Session\Phpfunc;
use Aura\Session\Randval;
use Aura\Session\RandvalInterface;
use Aura\Session\SegmentFactory;
use Aura\Session\Session;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Ray\WebFormModule\Annotation\FormValidation;
use Ray\WebFormModule\Annotation\InputValidation;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class WebFormModule extends AbstractModule
{
    /** {@inheritDoc} */
    protected function configure()
    {
        $this->bind(Session::class)->toProvider(SessionProvider::class);
        $this->bind(SegmentFactory::class);
        $this->bind(CsrfTokenFactory::class);
        $this->bind(RandvalInterface::class)->to(Randval::class);
        $this->bind(Phpfunc::class)->in(Scope::SINGLETON);
        $this->bind(BuilderInterface::class)->to(Builder::class);
        $this->bind(FilterInterface::class)->to(Filter::class);
        $this->bind(AntiCsrfInterface::class)->to(AntiCsrf::class)->in(Scope::SINGLETON);
        $this->bind(FailureHandlerInterface::class)->to(OnFailureMethodHandler::class);
        $this->bind(FailureHandlerInterface::class)
            ->annotatedWith('vnd_error')->to(VndErrorHandler::class)->in(Scope::SINGLETON);
        $this->bind(HelperLocatorFactory::class);
        $this->bind(FilterFactory::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(InputValidation::class),
            [InputValidationInterceptor::class],
        );
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(FormValidation::class),
            [AuraInputInterceptor::class],
        );
    }
}

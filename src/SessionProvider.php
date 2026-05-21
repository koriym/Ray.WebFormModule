<?php

declare(strict_types=1);

namespace Ray\WebFormModule;

use Aura\Session\CsrfTokenFactory;
use Aura\Session\Phpfunc;
use Aura\Session\SegmentFactory;
use Aura\Session\Session;
use Ray\Di\ProviderInterface;

use function assert;
use function is_string;
use function setcookie;
use function time;

/** @implements ProviderInterface<Session> */
final class SessionProvider implements ProviderInterface
{
    public function __construct(
        private SegmentFactory $segmentFactory,
        private CsrfTokenFactory $csrfTokenFactory,
        private Phpfunc $phpfunc,
    ) {
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function get(): Session
    {
        /** @param array{path: string, domain: string} $params */
        $deleteCookie = static function (string $name, array $params): void {
            assert(is_string($params['path']));
            assert(is_string($params['domain']));

            setcookie(
                $name,
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
            );
        };

        return new Session(
            $this->segmentFactory,
            $this->csrfTokenFactory,
            $this->phpfunc,
            $_COOKIE,
            $deleteCookie,
        );
    }
}

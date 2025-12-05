<?php

declare(strict_types=1);

/**
 * This file is part of the Ray.WebFormModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Ray\WebFormModule;

use Aura\Input\Builder;
use Aura\Input\Fieldset;
use Aura\Input\Filter;
use Aura\Session\CsrfTokenFactory;
use Aura\Session\Phpfunc;
use Aura\Session\Randval;
use Aura\Session\SegmentFactory;
use Aura\Session\Session;
use PHPUnit\Framework\TestCase;

class AntiCsrfTest extends TestCase
{
    private FakePhpfunc $phpfunc;
    private AntiCsrf $antiCsrf;
    private Session $session;

    protected function setUp(): void
    {
        $this->phpfunc = new FakePhpfunc();
        $this->session = $this->newSession();
        $this->antiCsrf = new AntiCsrf($this->newSession([]), false, [AntiCsrf::TOKEN_KEY => AntiCsrf::TEST_TOKEN]);
    }

    public function testNew(): void
    {
        $this->assertInstanceOf(AntiCsrf::class, $this->antiCsrf);
    }

    public function testSetField(): void
    {
        $result = $this->antiCsrf->setField(new Fieldset(new Builder(), new Filter()));
        $this->assertNull($result);
    }

    public function testIsValid(): void
    {
        $data = [AntiCsrf::TOKEN_KEY => $this->session->getCsrfToken()->getValue()];
        $this->assertTrue($this->antiCsrf->isValid($data));
    }

    protected function newSession(array $cookies = []): Session
    {
        return new Session(
            new SegmentFactory(),
            new CsrfTokenFactory(new Randval(new Phpfunc())),
            $this->phpfunc,
            $cookies
        );
    }
}

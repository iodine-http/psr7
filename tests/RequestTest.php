<?php

namespace Iodine\Http\Tests\Psr7;

use Iodine\Http\Psr7\Request;
use Iodine\Http\Psr7\Uri;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testIfCurrentRequestObjectInstanceOfRequest()
    {
        $q = new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get');

        $this->assertInstanceOf(Request::class, $q);
    }

    public function testIfCurrentRequestObjectCanGetProtocolVersion()
    {
        $q = new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertSame('1.1', $q->getProtocolVersion());
    }

    public function testIfCurrentRequestObjectCanGetProtocolVersionManually()
    {
        $q = (new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get'))
            ->withProtocolVersion('1.0');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertSame('1.0', $q->getProtocolVersion());
    }

    public function testIfCurrentRequestObjectCanGetURITarget()
    {
        $q = new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertNotNull($q->getRequestTarget());
    }

    public function testIfCurrentRequestObjectCanGetURITargetManually()
    {
        $q = (new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get'))
            ->withRequestTarget('/apiEndpoint/aa/bb/cc');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertNotNull($q->getRequestTarget());
    }

    public function testIfCurrentRequestObjectCanGetURITargetWithQuery()
    {
        $q = new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get?q=shit');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertNotNull($q->getRequestTarget());
    }

    public function testIfCurrentRequestObjectCanGetURITargetWithQueryManually()
    {
        $q = (new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get?q=shit'))
            ->withRequestTarget('apiEndpoint/aa/bb/cc?aa=bb&cc=dd');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertNotNull($q->getRequestTarget());
    }

    public function testIfCurrentRequestObjectCanGetRequestMethod()
    {
        $q = new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertNotNull($q->getMethod());
    }

    public function testIfCurrentRequestObjectCanGetRequestMethodManually()
    {
        $q = (new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get'))
            ->withMethod('POST');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertNotNull($q->getMethod());
    }

    public function testIfCurrentRequestObjectCanGetURIInstance()
    {
        $q = new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get');

        $this->assertInstanceOf(Request::class, $q);

        $this->assertInstanceOf(Uri::class, $q->getUri());
    }

    public function testIfCurrentRequestObjectCanGetURIInstanceManually()
    {
        $q = (new Request('GET', 'http://localhost:1337/tugas_akhir/api/v1/log/get'))
            ->withUri(new Uri('http://localhost:1337/tugas_akhir/api/v1/log/get'));

        $this->assertInstanceOf(Request::class, $q);

        $this->assertInstanceOf(Uri::class, $q->getUri());
    }
}
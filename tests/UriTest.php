<?php

namespace Iodine\Http\Tests\Psr7;

use Iodine\Http\Psr7\Uri;

/**
 * @covers Iodine\Http\Psr7\Uri
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
    public function testAllURIPartsAreSame()
    {
        $uri = "https://setuid0:gandung31337@example.com:1337/aa/bb/cc?q=shit#kwkwkw";
        $q = new Uri($uri);

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com:1337", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(1337, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testCanTransformAndRetrieveURIPartsManually()
    {
        $uri = "https://setuid0:gandung31337@example.com:1337/aa/bb/cc?q=shit#kwkwkw";
        $q = (new Uri())
            ->withScheme("https")
            ->withUserInfo("setuid0", "gandung31337")
            ->withHost("example.com")
            ->withPort(1337)
            ->withPath("/aa/bb/cc")
            ->withQuery("q=shit")
            ->withFragment("kwkwkw");

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com:1337", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(1337, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutScheme()
    {
        $uri = "//setuid0:gandung31337@example.com:1337/aa/bb/cc?q=shit#kwkwkw";
        $q = new Uri($uri);

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("setuid0:gandung31337@example.com:1337", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(1337, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutSchemeManually()
    {
        $uri = "//setuid0:gandung31337@example.com:1337/aa/bb/cc?q=shit#kwkwkw";
        $q = (new Uri())
            ->withUserInfo("setuid0", "gandung31337")
            ->withHost("example.com")
            ->withPort(1337)
            ->withPath("/aa/bb/cc")
            ->withQuery("q=shit")
            ->withFragment("kwkwkw");

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("setuid0:gandung31337@example.com:1337", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(1337, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutUserInfo()
    {
        $uri = "https://example.com:1337/aa/bb/cc?q=shit#kwkwkw";
        $q = new Uri($uri);

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("example.com:1337", $q->getAuthority());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(1337, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutUserInfoManually()
    {
        $uri = "https://example.com:1337/aa/bb/cc?q=shit#kwkwkw";
        $q = (new Uri())
            ->withScheme("https")
            ->withHost("example.com")
            ->withPort(1337)
            ->withPath("/aa/bb/cc")
            ->withQuery("q=shit")
            ->withFragment("kwkwkw");

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("example.com:1337", $q->getAuthority());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(1337, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutPort()
    {
        $uri = "https://setuid0:gandung31337@example.com/aa/bb/cc?q=shit#kwkwkw";
        $q = new Uri($uri);

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(null, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutPortManually()
    {
        $uri = "https://setuid0:gandung31337@example.com/aa/bb/cc?q=shit#kwkwkw";
        $q = (new Uri())
            ->withScheme("https")
            ->withUserInfo("setuid0", "gandung31337")
            ->withHost("example.com")
            ->withPath("/aa/bb/cc")
            ->withQuery("q=shit")
            ->withFragment("kwkwkw");

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame(null, $q->getPort());
        $this->assertSame("/aa/bb/cc", $q->getPath());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutPath()
    {
        $uri = "https://setuid0:gandung31337@example.com?q=shit#kwkwkw";
        $q = new Uri($uri);

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutPathManually()
    {
        $uri = "https://setuid0:gandung31337@example.com?q=shit#kwkwkw";
        $q = (new Uri())
            ->withScheme("https")
            ->withUserInfo("setuid0", "gandung31337")
            ->withHost("example.com")
            ->withQuery("q=shit")
            ->withFragment("kwkwkw");

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame("kwkwkw", $q->getFragment());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutFragment()
    {
        $uri = "https://setuid0:gandung31337@example.com?q=shit";
        $q = new Uri($uri);

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame($uri, (string)$q);
    }

    public function testURIWithoutFragmentManually()
    {
        $uri = "https://setuid0:gandung31337@example.com?q=shit";
        $q = (new Uri())
            ->withScheme("https")
            ->withUserInfo("setuid0", "gandung31337")
            ->withHost("example.com")
            ->withQuery("q=shit");

        $this->assertInstanceOf(Uri::class, $q);

        $this->assertSame("https", $q->getScheme());
        $this->assertSame("setuid0:gandung31337@example.com", $q->getAuthority());
        $this->assertSame("setuid0:gandung31337", $q->getUserInfo());
        $this->assertSame("example.com", $q->getHost());
        $this->assertSame("q=shit", $q->getQuery());
        $this->assertSame($uri, (string)$q);
    }
}
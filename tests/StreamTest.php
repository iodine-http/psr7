<?php

namespace Iodine\Http\Tests\Psr7;

use Iodine\Http\Psr7\Stream;

/**
 * @covers Iodine\Http\Psr7\Stream
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    public function testCanInstantiateConstructor()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $q->close();
    }

    public function testDetermineIfStreamHandleAreReadable()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertSame(true, $q->isReadable());
        $q->close();
    }

    public function testDetermineIfStreamHandleAreWritable()
    {
        $q = new Stream(fopen("/tmp/foo.php", "wb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertSame(true, $q->isWritable());
        $q->close();
    }

    public function testDetermineIfStreamHandleAreSeekable()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertSame(true, $q->isSeekable());
        $q->close();
    }

    public function testDetermineIfStreamHandleCanGetContents()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotSame(false, $q->getContents());
        $q->rewind();
        $q->close();
    }

    public function testDetermineIfStreamHandleCanGetSize()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotSame(null, $q->getSize());
        $q->close();
    }

    public function testDetermineIfStreamHandleCanTellCurrentPosition()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotSame(false, $q->tell());
        $q->read(10);
        $this->assertNotSame(false, $q->tell());
        $q->rewind();
        $this->assertNotSame(false, $q->tell());
        $q->close();
    }

    public function testDetermineIfStreamHandleIsEOF()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertSame(false, $q->eof());
        $q->getContents();
        $this->assertSame(true, $q->eof());
        $q->rewind();
        $this->assertSame(false, $q->eof());
        $q->close();
    }

    public function testDetermineIfStreamHandleCanSeekOnOffset()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotSame(false, $q->tell());
        $q->seek(1 << 4);
        $this->assertNotSame(false, $q->tell());
        $q->rewind();
        $this->assertNotSame(false, $q->tell());
        $q->close();
    }

    public function testDetermineIfStreamCanRewinded()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotSame(false, $q->tell());
        $q->seek(1 << 4);
        $this->assertNotSame(false, $q->tell());
        $q->rewind();
        $this->assertNotSame(false, $q->tell());
        $q->close();
    }

    public function testDetermineIfStreamCanWritten()
    {
        $q = new Stream(fopen("/tmp/foo.php", "wb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotSame(false, $q->write(file_get_contents("/etc/passwd")));
        $q->close();
    }

    public function testDetermineIfStreamCanBeRead()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $q->rewind();
        $this->assertNotSame(false, $q->read(1024));
        $q->rewind();
        $q->close();
    }

    public function testDetermineIfStreamCanGetMetadata()
    {
        $q = new Stream(fopen("/etc/passwd", "rb"));

        $this->assertInstanceOf(Stream::class, $q);
        $this->assertNotNull($q->getMetadata('seekable'));
        $this->assertNotEmpty($q->getMetadata());
        $this->assertNull($q->getMetadata('nonExistentKey'));
        $q->close();
    }
}
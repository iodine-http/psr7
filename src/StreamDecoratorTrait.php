<?php

namespace Iodine\Http\Psr7;

/*
 * This file is a part of Iodine HTTP Client Library.
 *
 * Copyright (c) 2017 Paulus Gandung Prakosa (rvn.plvhx@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use Psr\Http\Message\StreamInterface;

trait StreamDecoratorTrait
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $stat;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var bool
     */
    private $seekable;

    /**
     * @var integer
     */
    private $size;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (Exception $e) {
            @trigger_error("(error) " . __TRAIT__ . "::" . __METHOD__ . ": " .
                $e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);

            $this->detach();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if ($this->stream === null) {
            return null;
        }

        $result = $this->stream;
        $this->metadata = $this->stat = null;
        $this->mode = $this->seekable = null;
        $this->size = $this->stream = null;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return ($this->size !== null ? $this->size : null);
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        $pos = ftell($this->stream);

        if (!$pos) {
            throw new \RuntimeException("Unable to tell current position on current stream handle.");
        }

        return $pos;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable())
            throw new \RuntimeException("\$this->stream is not seekable.");

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException("Unable to seek on current stream handle. (Offset: " .
                $offset . ", Whence: " . (int)$whence . ")");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Stub: determine if current stream handle is writable.
     *
     * @param string Stream mode
     * @return bool
     */
    private function __stub_isWritable($subject)
    {
        $v = preg_match(
            '~(?(?=[wa]{1}.?)(?:[wa]{1}(?(?=(?:\+?(?:b?))?)(?:\+?(?:b?))?|(?:(?:b?)\+?)?))|(?:[r]{1}(?(?=\+{1}(?:b?))(?:\+{1}(?:b?))|(?:(?:b?)\+{1}))))~',
            $subject
        );

        return (!$v ? false : true);
    }

    /**
     * Stub: determine if current stream handle is readable.
     *
     * @param string Stream mode
     * @return bool
     */
    private function __stub_isReadable($subject)
    {
        $v = preg_match('~^(?>(?:r))(.*)~', $subject);

        return (!$v ? false : true);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return $this->__stub_isWritable($this->mode);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException("The current stream handle is not writable.");
        }

        $sz = fwrite($this->stream, $string, strlen($string));

        if (!$sz) {
            throw new \RuntimeException("Unable to write data on current stream handle.");
        }

        return $sz;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->__stub_isReadable($this->mode);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException("The current stream handle is not readable.");
        }

        if ($length < 0) {
            throw new \RuntimeException("Buffer length must be greater or equal than zero.");
        }

        $buf = fread($this->stream, $length);

        if (!$buf) {
            throw new \RuntimeException("Unable to read data from current stream handle.");
        }

        return $buf;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $buf = stream_get_contents($this->stream);

        if (!$buf) {
            throw new \RuntimeException("Unable to read data from current stream handle.");
        }

        return $buf;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return null;
    }
}
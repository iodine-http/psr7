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

class LocalStream implements StreamInterface, LocalStreamAwareInterface
{

    /**
     * @var resource|null
     */
    private $stream;

    /**
     * @var string|null
     */
    private $filename;

    /**
     * @var string|null
     */
    private $mode;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $stat;

    /**
     * @var bool
     */
    private $seekable;

    /**
     * @var integer
     */
    private $size;

    /**
     * LocalStream constructor.
     * @param $filename
     * @param $mode
     */
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;

        $this->createStream();
    }

    /**
     * {@inheritdoc}
     */
    private function createStream()
    {
        if (!$this->isWritable($this->mode) || !$this->isReadable($this->mode))
            throw new \RuntimeException("Invalid stream mode.");

        $this->stream = fopen($this->filename, $this->mode);

        if (!is_resource($this->stream))
            throw new \RuntimeException("Cannot create stream.");

        $this->metadata = stream_get_meta_data($this->stream);
        $this->stat = fstat($this->stream);
        $this->seekable = ($this->metadata['seekable'] === 1 ? true : false);
        $this->size = (int)$this->stat['size'];

    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (Exception $e) {

        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (!is_resource($this->stream)) {
            fclose($this->stream);

            $this->detach();
        }
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        // TODO: Implement detach() method.
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        return ftell($this->stream);
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable())
            throw new \RuntimeException();

        $pos = fseek($this->handle, $offset, $whence);

        if (!$pos) {
            throw new \RuntimeException("Cannot seek on current stream handle. (offset: " .
                $offset . ", whence: " . $whence . ")");
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return (preg_match('~^(?:(?:(?:a|w)\+?)|(?:(?:r)\+{1}))(?:.*)$~', $this->mode)
            ? true : false);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if (!$this->isLocalStream())
            throw new \RuntimeException("Stream is not local.");

        if (!$this->isWritable())
            throw new \RuntimeException("\$this->stream is not writable.");

        $sz = fwrite($this->stream, $string, strlen($string));

        if (!$sz)
            throw new \RuntimeException("Unable to write on current stream handle.");

        return $sz;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return (preg_match('~^(?:(?:r))(?:.*)$~', $this->mode) ? true : false);
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if (!$this->isReadable())
            throw new \RuntimeException("\$this->stream is not readable.");

        if ($length < 0)
            throw new \RuntimeException("Length must be at least equal or greater than zero.");

        $buf = fread($this->stream, $length);

        if (!$buf)
            throw new \RuntimeException("Unable to read data from current stream handle.");

        return $buf;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        $buf = stream_get_contents($this->stream);

        if (!$buf)
            throw new \RuntimeException("Unable to read data from current stream handle.");

        return $buf;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if ($key === null)
            return $this->metadata;

        if (array_key_exists($key, $this->metadata))
            return $this->metadata[$key];

        return null;
    }

    /**
     * Determine if supplied stream is local.
     *
     * @return bool Returns true is supplied stream is local, otherwise false.
     */
    public function isLocalStream()
    {
        return stream_is_local($this->stream);
    }
}
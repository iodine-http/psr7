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

class Stream implements StreamInterface
{
	/**
	 * @var $stream
	 */
	private $stream;

	/**
	 * @var $mode
	 */
	private $mode;

	/**
	 * @var $seekable
	 */
	private $seekable;

	/**
	 * @var $metadata
	 */
	private $metadata;

	/**
	 * @var $stat
	 */
	private $stat;

	/**
	 * @var $size
	 */
	private $size;

	public function __construct($stream)
	{
		if (!is_resource($stream))
			throw new \InvalidArgumentException("The first argument is not a stream.");

		$this->stream = $stream;
		$this->metadata = stream_get_meta_data($this->stream);
		$this->stat = fstat($this->stream);
		$this->mode = $this->metadata['mode'];
		$this->seekable = ($this->metadata['seekable'] == 1 ? true : false);
		$this->size = $this->stat['size'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString()
	{
		try {
			$this->rewind();

			return $this->getContents();
		}
		catch (\Exception $e) {
			return '';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		if ($this->stream !== null) {
			if (is_resource($this->stream)) {
				fclose($this->stream);
			}

			$this->detach();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function detach()
	{
		if (!is_resource($this->stream))
			$this->stream = null;

		$this->mode = $this->seekable = $this->metadata = null;

		return $this->stream;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSize()
	{
		return ($this->size === $this->stat['size'] ? $this->size : null);
	}

	/**
	 * {@inheritdoc}
	 */
	public function tell()
	{
		if (!is_resource($this->stream))
			throw new \RuntimeException("\$this->stream is not a stream.");

		$position = ftell($this->stream);

		if (false === $position)
			throw new \RuntimeException("Unable to determine current stream position.");

		return $position;
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
	public function isWritable()
	{
		return (preg_match('~(?(?=[wa]{1}.?)(?:[wa]{1}(?(?=(?:\+?(?:b?))?)(?:\+?(?:b?))?|(?:(?:b?)\+?)?))|(?:[r]{1}(?(?=\+{1}(?:b?))(?:\+{1}(?:b?))|(?:(?:b?)\+{1}))))~', $this->mode)
			? true : false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isReadable()
	{
		return (preg_match('~^(?>(?:r))(.*)~', $this->mode) ? true : false);
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
	public function rewind()
	{
		$this->seek(0);
	}

	/**
	 * {@inheritdoc}
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->seekable)
			throw new \RuntimeException("The current stream is not seekable.");

		$result = fseek($this->stream, $offset, $whence);

		if ($result === -1)
			throw new \RuntimeException("Unable to seek at stream position " .
				$offset . " with whence " . $whence);
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($length)
	{
		if (false === $this->isReadable())
			throw new \RuntimeException("The current stream is not readable.");

		if ($length < 0)
			throw new \RuntimeException("Length must be greater or equal to zero.");

		$buf = fread($this->stream, $length);

		if (false === $buf)
			throw new \RuntimeException("Unable to read data from current stream handle.");

		return $buf;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($string)
	{
		if (false === $this->isWritable())
			throw new \RuntimeException("The current stream is not writable.");

		$sz = fwrite($this->stream, $string);

		if (false === $sz)
			throw new \RuntimeException("Unable to write to current stream handle.");

		return $sz;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContents()
	{
		$buf = stream_get_contents($this->stream);

		if (!$buf)
			throw new \RuntimeException("Unable to read data from current stream handle.");

		return $buf;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetadata($key = null)
	{
	    if ($key === null)
	        return $this->metadata;

		if ($key !== null && array_key_exists($key, $this->metadata))
			return $this->metadata[$key];

		if (!array_key_exists($key, $this->metadata))
			return null;
	}
}
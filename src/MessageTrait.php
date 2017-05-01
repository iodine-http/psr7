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

trait MessageTrait
{
	/**
	 * @var $protocolVersion
	 */
	private $protocolVersion = '1.1';

	/**
	 * @var $header
	 *
	 * Using 'array()' instead of '[]' for backward compatibility with PHP < 5.4.x
	 */
	private $headers = array();

	/**
	 * @var StreamInterface $stream
	 */
	private $stream;

	/**
	 * {@inheritdoc}
	 */
	public function getProtocolVersion()
	{
		return $this->protocolVersion;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withProtocolVersion($version)
	{
		if ($version === $this->protocolVersion)
			return $this;

		$q = clone $this;
		$q->protocolVersion = $version;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasHeader($name)
	{
		$headerName = array_map(function($q) { return strtolower($q); }, array_keys($this->headers));

		return in_array($name, $headerName, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeader($name)
	{
		if (!$this->hasHeader($name))
			return [];

		$name = strtolower($name);
		$header = array_merge(
			array_map(function($q) {
			    return strtolower($q);
            },
            array_keys($this->headers)),
        array_values($this->headers)
		);

		return is_array($header[$name]) ? $header[$name] : array($header[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderLine($name)
	{
		$q = $this->getHeader($name);

		if (empty($q))
			return '';

		return implode(',', $q);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withHeader($name, $value)
	{
		if (is_string($value))
			$value = [$value];

		$q = clone $this;
		$q->headers[$name] = $value;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withAddedHeader($name, $value)
	{
		if (is_string($value))
			$value = [$value];

		if (!$this->hasHeader($name))
			return new $this->withHeader($name, $value);

		$q = clone $this;
		$q->headers[$name] = array_merge($q->headers[$name], $value);

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withoutHeader($name)
	{
		if (!$this->hasHeader($name))
			return clone $this;

		$q = clone $this;
		unset($q->headers[$name]);

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBody()
	{
		return $this->stream;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withBody(StreamInterface $body)
	{
		$q = clone $this;
		$q->stream = $body;

		return $q;
	}
}
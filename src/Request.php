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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
	use MessageTrait;

	/**
	 * A URI matching pattern based on https://tools.ietf.org/html/rfc7230#section-5.3.4
	 */
	const RFC7230_URI_WILDCARD_PATTERN = '~^(\*)$~';

	/**
	 * A URI matching pattern based on https://tools.ietf.org/html/rfc7230#section-5.3.3
	 */
	const RFC7230_URI_AUTHORITY_PATTERN = '~(?:[a-z0-9]+\.?)+\:(?:\d+)~';

	/**
	 * A URI matching pattern based on https://tools.ietf.org/html/rfc7230#section-5.3.2
	 */
	const RFC7230_URI_ABSOLUTE_PATTERN = '~(?:[a-z]+)\:\/\/(?:(?:[a-z0-9]+\.?)+)\:(?:\d+)(?:(?:\/{1}[A-Za-z0-9\.\_\-]+)+)~';

	/**
	 * A URI matching pattern based on https://tools.ietf.org/html/rfc7230#section-5.3.1
	 */
	const RFC7230_URI_ORIGIN_PATTERN = '~(?:(?:\/{1}[A-Za-z0-9\.\-\_]+)+)(?:\?(?:(?:[a-zA-Z0-9\-\_]+\=[a-zA-Z0-9\-\_]+\&?)+))?~';

	/**
	 * @var $requestTarget
	 */
	private $requestTarget;

	/**
	 * @var UriInterface $uri
	 */
	private $uri;

	/**
	 * @var $method
	 */
	private $method;

	/**
	 * @var $protocol
	 */
	private $protocol;

	public function __construct(
		$method,
		$uri,
		array $headers = [],
		$body = null
	) {
		if (!($uri instanceof UriInterface)) {
			$uri = new Uri($uri);
		}

		$this->method = strtoupper($method);
		$this->uri = $uri;
		$this->protocol = $this->protocolVersion;

		if (!$this->hasHeader('Host')) {
			$this->updateHost();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRequestTarget()
	{
		if ($this->requestTarget !== null)
			return $this->requestTarget;

		$target = $this->uri->getPath();

		if ('' === $target)
			$target = '/';

		if ($this->uri->getQuery() !== '')
			$target .= '?' . $this->uri->getQuery();

		return $target;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withRequestTarget($requestTarget)
	{
		if (!$this->validateUri($requestTarget))
			throw new \InvalidArgumentException("Invalid URI: {$requestTarget}");

		$q = clone $this;
		$q->requestTarget = $requestTarget;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withMethod($method)
	{
		$q = clone $this;
		$q->method = strtoupper($method);

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUri()
	{
		return $this->uri;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function withUri(UriInterface $uri, $preserveHost = false)
	{
		if ($uri === $this->uri)
			return $this;

		$q = clone $this;
		$q->uri = $uri;

		if (!$preserveHost)
			$q->updateHost();

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	private function updateHost()
	{
		$host = $this->uri->getHost();

		if (($port = $this->uri->getPort()) !== null)
			$host .= ':' . $port;

		if (isset($this->headers['Host'])) {
			unset($this->headers['Host']);
		}

		$this->headers = array_merge(['Host' => $host], $this->headers);
	}

	/**
	 * {@inheritdoc}
	 */
	private function validateUri($uri)
	{
		if (preg_match(self::RFC7230_URI_ORIGIN_PATTERN, $uri, $matches))
			return $matches[0];

		if (preg_match(self::RFC7230_URI_ABSOLUTE_PATTERN, $uri, $matches))
			return $matches[0];

		if (preg_match(self::RFC7230_URI_AUTHORITY_PATTERN, $uri, $matches))
			return $matches[0];

		if (preg_match(self::RFC7230_URI_WILDCARD_PATTERN, $uri, $matches))
			return $matches[0];

		return false;
	}
}
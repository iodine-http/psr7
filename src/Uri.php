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

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
	/**
	 * IPv4-based host pattern.
	 */
	const HOST_IPv4_PATTERN = '~^(?:\d{1,3})\.(?:\d{1,3})\.(?:\d{1,3})\.(?:\d{1,3})$~';

	/**
	 * Host-based host pattern.
	 */
	const HOST_RESOLVED_PATTERN = '~(?:(?:[a-z0-9]+\.?))+~';

	/**
	 * @var $scheme
	 */
	private $scheme;

	/**
	 * @var $host
	 */
	private $host;

	/**
	 * @var $port
	 */
	private $port;

	/**
	 * @var $user
	 */
	private $user;

	/**
	 * @var $pass
	 */
	private $pass;

	/**
	 * @var $path
	 */
	private $path;

	/**
	 * @var $query
	 */
	private $query;

	/**
	 * @var $fragment
	 */
	private $fragment;

	public function __construct($uri = '')
	{
		if (empty($uri)) {
			// throw the exception here..
		}

		$uri_parts = parse_url($uri);

		if (false === $uri_parts) {
			throw new \InvalidArgumentException("Unable to parse URI: {$uri}");
		}

		$this->apply($uri_parts);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAuthority()
	{
		$authority = $this->host;

		if ($this->user !== '') {
			$userInfo = $this->user;

			if ($this->pass !== '') {
				$userInfo .= ':' . $this->pass;
			}

			$authority = $userInfo . '@' . $authority;
		}

		if ($this->port !== null) {
			$authority .= ':' . $this->port;
		}

		return $authority;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserInfo()
	{
		$userInfo = '';

		if ($this->user !== '') {
			$userInfo .= $this->user;

			if ($this->pass !== '') {
				$userInfo .= ':' . $this->pass;
			}
		}

		return $userInfo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFragment()
	{
		return $this->fragment;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function withScheme($scheme)
	{
		if ($scheme === $this->scheme)
			return $this;

		$q = clone $this;
		$q->scheme = $scheme;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withUserInfo($user, $password = null)
	{
		if ($user === $this->user && $password === $this->pass)
			return $this;

		$q = clone $this;
		$q->user = $user;
		$q->pass = $password;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withHost($host)
	{
		if ($host === $this->host)
			return $this;

		$filtered_host = $this->validateHost($host);

		if (!$filtered_host)
			throw \InvalidArgumentException("{$host} is not valid host.");

		$q = clone $this;
		$q->host = $filtered_host;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPort($port)
	{
		if ($port === $this->port)
			return $this;

		$q = clone $this;
		$q->port = $port;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPath($path)
	{
		if (!strnatcasecmp($path, $this->path))
			return $this;

		$q = clone $this;
		$q->path = $path;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withQuery($query)
	{
		if (!strnatcasecmp($query, $this->query))
			return $this;

		$q = clone $this;
		$q->query = $query;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withFragment($fragment)
	{
		if (!strnatcasecmp($fragment, $this->fragment))
			return $this;

		$q = clone $this;
		$q->fragment = $fragment;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	private function validateHost($host)
	{
		if (preg_match(self::HOST_IPv4_PATTERN, $host, $matches)) {
			return $matches[0];
		}

		if (preg_match(self::HOST_RESOLVED_PATTERN, $host, $matches)) {
			return $matches[0];
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	private function apply($uri_parts)
	{
		if (empty($uri_parts)) {
			// throw the exception here..
		}

		$this->scheme = isset($uri_parts['scheme']) ? $uri_parts['scheme'] : '';
		$this->host = isset($uri_parts['host']) ? $uri_parts['host'] : '';
		$this->port = isset($uri_parts['port']) ? $uri_parts['port'] : null;
		$this->user = isset($uri_parts['user']) ? $uri_parts['user'] : '';
		$this->pass = isset($uri_parts['pass']) ? $uri_parts['pass'] : '';
		$this->path = isset($uri_parts['path']) ? $uri_parts['path'] : '';
		$this->query = isset($uri_parts['query']) ? $uri_parts['query'] : '';
		$this->fragment = isset($uri_parts['fragment']) ? $uri_parts['fragment'] : '';

		$this->host = $this->validateHost($this->host);
	}
}
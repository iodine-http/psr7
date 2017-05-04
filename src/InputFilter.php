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

class InputFilter
{
	/**
	 * @var $get
	 */
	private $get = array();

	/**
	 * @var $post
	 */
	private $post = array();

	/**
	 * @var $cookie
	 */
	private $cookie = array();

	/**
	 * @var $server
	 */
	private $server = array();

    /**
     * @return static
     */
	public static function createFromGlobals()
	{
		return new static;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withGetParams()
	{
		$q = clone $this;

		array_walk($_GET, function($v, $k) use ($q) {
			$q->get[$k] = filter_input(INPUT_GET, $k, FILTER_SANITIZE_STRING);
		});

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPostParams()
	{
		$q = clone $this;

		array_walk($_POST, function($v, $k) use ($q) {
			$q->post[$k] = filter_input(INPUT_POST, $k, FILTER_SANITIZE_STRING);
		});

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withCookieParams()
	{
		$q = clone $this;

		array_walk($_COOKIE, function($v, $k) use ($q) {
			$q->cookie[$k] = filter_input(INPUT_COOKIE, $k, FILTER_SANITIZE_STRING);
		});

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withServerParams()
	{
		$q = clone $this;

		array_walk($_SERVER, function($v, $k) use ($q) {
			$q->server[$k] = filter_input(INPUT_SERVER, $k, FILTER_SANITIZE_STRING);
		});

		return $q;
	}

    /**
     * Return existing property if exists.
     *
     * @param string $name
     * @return mixed|null
     * @throws \InvalidArgumentException if the method trying to access nonexisting variable.
     */
	public function __get($name)
    {
        if (!property_exists($this, $name))
            throw new \InvalidArgumentException("Unable to access nonexistent variable {$name}");

        return $this->{$name};
    }
}
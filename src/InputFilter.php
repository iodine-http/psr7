<?php

namespace Iodine\Http\Psr7;

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

	public static function create()
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
	 * {@inheritdoc}
	 */
	public function getParams($opt = 'get')
	{
		if ($opt === 'get') {
			return $this->get;
		}
		else if ($opt === 'post') {
			return $this->post;
		}
		else if ($opt === 'cookie') {
			return $this->cookie;
		}
		else if ($opt === 'server') {
			return $this->server;
		}
	}
}
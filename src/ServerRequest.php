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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var null|array|object
     */
    private $parsedBody;

    /**
     * @var array
     */
    private $attribute = array();

	/**
	 * @var $serverParams
	 */
	private $serverParams;

	/**
	 * @var $cookieParams
	 */
	private $cookieParams;

	/**
	 * @var $uploadedFiles
	 */
	private $uploadedFiles;

	/**
	 * @var $filter
	 */
	public function __construct(array $serverParams = [])
	{
		$this->filter = InputFilter::createFromGlobals();

		$this->serverParams = (empty($serverParams) ?
			$this->filter->server : $serverParams);

		$this->cookieParams = $this->filter->cookie;
		$this->uploadedFiles = $this->normalizeUploadedFiles($_FILES);

		parent::__construct(
			$this->serverParams['REQUEST_METHOD'],
            $this->serverParams['HTTP_HOST'] . $this->serverParams['REQUEST_URI']
		);
	}

	/**
	 * Normalize structure of the uploaded files.
	 *
	 * See: http://www.php-fig.org/psr/psr-7/#uploaded-files
	 */
	private function normalizeUploadedFiles(array $uploadedFiles)
	{
		$normalized = array();

		if (empty($uploadedFiles))
			return $normalized;

		foreach ($uploadedFiles as $attr => $value) {
			if ($value instanceof UploadedFileInterface) {
				$normalized[$attr] = $value;
			}
			else if (is_array($value) && isset($value['tmp_name'])) {
				$normalized[$attr] = (is_array($value['tmp_name']) ?
					$this->createNestedUploadedFileInstance($value) :
					$this->createUploadedFileInstance($value));
			}
			else if (is_array($value)) {
				$normalized[$attr] = $this->normalizeUploadedFiles($value);

				continue;
			}
		}

		return $normalized;
	}

	/**
	 * Create and return one instance of UploadedFile.
	 */
	private function createUploadedFileInstance($value)
	{
		return new UploadedFile(
			$value['name'],
			$value['type'],
			$value['tmp_name'],
			(int)$value['error'],
			(int)$value['size']
		);
	}

	/**
	 * Normalize and return multiple instance of UploadedFile for each
	 * $_FILES associative keys.
	 */
	private function createNestedUploadedFileInstance($value)
	{
		$normalized = [];

		foreach (array_keys($value['tmp_name']) as $k) {
			$leaf = array(
				'name' => $value['name'][$k],
				'type' => $value['type'][$k],
				'tmp_name' => $value['tmp_name'][$k],
				'error' => $value['error'][$k],
				'size' => $value['size'][$k]
			);

			$normalized[$k] = $this->createUploadedFileInstance($leaf);
		}

		return $normalized;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getServerParams()
	{
		return $this->serverParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCookieParams()
	{
		return $this->cookieParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withCookieParams(array $cookies)
	{
		if ($this->cookieParams === $cookies)
			return $this;

		$q = clone $this;
		$q->cookieParams = $cookies;

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQueryParams()
	{
		$query = $this->getUri()->getQuery();

		if ($query === '')
			return array();
		
		$parts = explode('&', $query);
		$key = array();
		$value = array_map(function($q) use ($key) {
			$v = explode('=', $q);

			$key[] = $v[0];

			return $v[1];
		}, $parts);

		return array_combine($key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withQueryParams(array $query)
	{
		if ($query === $this->getQueryParams())
			return $this;

		$parts = array();

		array_walk($query, function($v, $k) use ($parts) {
			$parts[] = $k . '=' . $v;
		});

		$unify = implode('&', $parts);
		$q = clone $this;
		$q->withUri($q->getUri()->withQuery($parts));

		return $q;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUploadedFiles()
	{
		return $this->uploadedFiles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withUploadedFiles(array $uploadedFiles)
	{
		$q = clone $this;
		$q->uploadedFiles = $uploadedFiles;

		return $q;
	}

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        $q = clone $this;
        $q->parsedBody = $data;

        return $q;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attribute;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (!array_key_exists($name, $this->attribute)) {
            return $default;
        }

        return $this->attribute[$name];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $q = clone $this;
        $q->attribute[$name] = $value;

        return $q;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        if (!array_key_exists($name, $this->attribute)) {
            return $this;
        }

        $q = clone $this;

        unset($q->attribute[$name]);

        return $q;
    }
}
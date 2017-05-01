<?php

namespace Iodine\Http\Psr7;

/*
 * This file is a part of Iodine HTTP Client Library.
 *
 * Copyright (c) 2017 Egar Rizki Santoso (ch0c01d.xyz@gmail.com)
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

class JWTHandler
{
	/**
	 * @var $delay
	 */
	protected $delay = 0;

	/**
	 * @var $token
	 */
	protected $token;

	/**
	 * @var $key
	 */
	protected $key;

	/**
	 * @var $algorithm
	 */
	protected $algorithm;

	/**
	 * @var $timestamp
	 */
	protected $timestamp = 0;

	/**
	 * List Hash
	 */
	const HASH_LIST = array(
		"SHA256",
		"SHA384",
		"SHA512"
	);

	protected $custom_hex = "\\x";

	/**
	 * @return static
	 */
	public static function create()
	{
		return new static;
	}

	/**
	 * Token Getter
	 * @return String
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Token Setter
	 * @param String $token
	 */
	public function setToken( $token = "" )
	{
		$this->token = $token;
	}

	/**
	 * Key Getter
	 * @return String
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Key Setter
	 * @param String $key
	 */
	public function setKey( $key = "" )
	{
		$this->key = $key;
	}

	/**
	 * Algorithm Getter
	 * @return String
	 */
	public function getAlgorithm()
	{
		return $this->algorithm;
	}

	/**
	 * Algorithm Setter
	 * @param String $algorithm
	 */
	public function setAlgorithm( $algorithm = 0 )
	{
		$this->algorithm = HASH_LIST[ $algorithm ];
	}

	/**
	 * Custom Hex Getter
	 * @return String
	 */
	public function getCustomHex()
	{
		return $this->custom_hex;
	}

	/**
	 * Custom Hex Setter
	 * @param String $custom_hex
	 */
	public function setCustomHex( $custom_hex = "\\x" )
	{
		$this->custom_hex = $custom_hex;
	}

	/**
	 * Timestamp Getter
	 * @return Integer
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * Timestamp Setter
	 * @param Integer $timestamp
	 */
	public function setTimestamp( $timestamp = 0 )
	{
		$this->timestamp = $timestamp;
	}

	/**
	 * Delay Getter
	 * @return Integer
	 */
	public function getDelay()
	{
		return $this->delay;
	}

	/**
	 * Delay Setter
	 * @param Integer $delay
	 */
	public function setDelay( $delay = 0 )
	{
		$this->delay = 0;
	}

	/**
	 * Sign payload using HMAC ( determine the algorithm first ).
	 * for OTP purposed
	 * @param  String $payload
	 * @param  String $key
	 * @return String
	 */
	public function sign( $payload = "", $type = 0 )
	{
		$tmp = hash_hmac( $this->getAlgorithm(), $payload, $this->getKey(), true );

		switch( $type ) {
			// Standart Hex based output
			case 0:
				return hex2bin( $tmp );
			break;

			// Custom Hex format based output
			case 1:
				return $this->getCustomHex() . substr( chunk_split( bin2hex( $tmp ), 2, $this->getCustomHex() ), 0, -2 );
			break;

			// Base64 based output
			case 1:
				$output = base64_encode( $tmp );
			break;

			// Raw based output
			case 2:
				return $tmp;
			break;
		}
	}

	/**
	 * Verify the Payload
	 * for OTP purposed
	 * @param  String $expected
	 * @param  String $payload
	 * @return Boolean
	 */
	public function verify( $expected = "", $payload = "" )
	{
		return hash_equals( $expected, $this->sign( $payload, $this->getKey(), $this->getCustomHex() ) );
	}

	/**
	 * Encode the JWT Data
	 * @param  Object | Array $payload
	 * @param  String $keyId
	 * @param  Array $head
	 * @return String
	 */
	public static function jwt_encode( $payload, $keyId = "", $head = null )
	{
		$header = array(
			"typ" => "JWT",
			"alg" => $this->getAlgorithm()
		);

		if ( $keyId !== null ) {
			$header[ "kid" ] = $keyId;
		} else {
			$header[ "kid" ] = $this->getToken();
		}

		if ( isset( $head ) && is_array( $head ) ) {
			$header = array_merge( $head, $header );
		}

		return implode( ".", array(
			static::jwt_urlEncode( static::jwtSign( array(
				implode( ".", array(
					static::jwt_urlEncode( static::jwt_jsonEncode( $header ) ),
					static::jwt_urlEncode( static::jwt_jsonEncode( $payload ) )
				) )
			), $this->getToken(), $this->getAlgorithm() ) )
		) );
	}

	public static function jwt_decode( $data )
	{
		$timestamp = is_null( $this->getTimestamp() ) ? $this->setTimestamp( time() ) : $this->getTimestamp();

		list( $jwt_head, $jwt_body, $jwt_crypto ) = explode( ".", $data );

		$header = static::jwt_jsonDecode( static::jwt_urlDecode( $jwt_head ) );
		$payload = static::jwt_jsonDecode( static::jwt_urlDecode( $jwt_body ) );

		$sig = static::jwt_urlDecode( $jwt_crypto );

		// Throw Exception for invalid verifying of JWT data
		if ( static::jwtVerify( $jwt_head . "." . $jwt_body, $sig ) === false ) {
			throw new \RuntimeException( "Can't verify JWT Data !" );
		}

		// Throw Exception for invalid notBefore
		if ( isset( $payload[ "nbf" ] ) && $payload[ "nbf" ] > ( $this->getTimestamp() + $this->getDelay() ) ) {
			throw new \RuntimeException( "Can't Handle Token prior to " . date( Datetime::ISO8601, $payload[ "nbf" ] ) );
		}

		// Throw Exception for invalid Issued At
		if ( isset( $payload[ "iat" ] ) && $payload[ "iat" ] > ( $this->getTimestamp() + $this->getDelay() ) ) {
			throw new \RuntimeException( "Can't Handle Token prior to " . date( Datetime::ISO8601, $payload[ "iat" ] ) );
		}

		// Throw Exception for invalid Token Expired
		if ( isset( $payload[ "exp" ] ) && ( ( $this->getTimestamp() - $this->getDelay() ) >= $payload[ "exp" ] ) ) {
			throw new \RuntimeException( "Token Expired." );
		}

		return $payload;
	}

	/**
	 * JWT Data Signing
	 * @param  String $data
	 * @return String
	 */
	public static function jwtSign( $data )
	{
		// Return signed JWT Data
		return hash_hmac( $this->getAlgorithm(), $data, $this->getToken(), true );
	}

	/**
	 * Verifying JWT Data
	 * @param String $data
	 * @param String $signature
	 * @return Bool
	 */
	public static function jwtVerify( $data, $signature )
	{
		$hash = static::jwtSign( $data );

		if ( function_exists( 'hash_equals' ) ) {
			return hash_equals( $signature, static::jwtSign( $data ) );
		}

		// Use this if hash_equals function isn't available
		$sig_len = min( static::jwtStrlen( $signature ), static::jwtStrlen( $hash ) );

		$done = 0;

		for ( $i = 0; $i < $sig_len; $i++ ) {
			$done |= ( ord( $signature[ $i ] ) ^ ord( $hash[ $i ] ) );
		}

		$done |= ( static::jwtStrlen( $signature ) ^ static::jwtStrlen( $hash ) );

		return ( $done === 0 );
	}

	/**
	 * JWT Json Data Decode
	 * @param  String $data
	 * @return Array
	 */
	public static function jwt_jsonDecode( $data )
	{
		if ( version_compare( PHP_VERSION, "5.4.0", ">=" ) && !( defined( "JSON_C_VERSION" ) && PHP_INT_SIZE > 4 ) ) {
			return json_decode( $data, true, 512, JSON_BIGINT_AS_STRING );
		} else {
			return json_decode( preg_replace( '/:\s*(-?\d{' . (strlen( (string) PHP_INT_MAX ) - 1) . ',})/', ': "$1"', $data), true );
		}
	}

	/**
	 * JWT Json Data Encode
	 * @param  Object | Array $data
	 * @return String
	 */
	public static function jwt_jsonEncode( $data )
	{
		return json_encode( $data );
	}

	/**
	 * JWT Decode URL
	 * @param String $data
	 * @return String
	 */
	public static function jwt_urlDecode( $data )
	{
		$mod = strlen( $data ) % 4;

		if ( $mod ) {
			$padding = 4 - $mod;
			$data .= str_repeat( "=", $padding );
		}

		return base64_decode( strtr( $data, "-_", "+/" ) );
	}

	/**
	 * JWT Encode URL
	 * @param String $data
	 * @return String
	 */
	public static function jwt_urlEncode( $data )
	{
		return str_replace( "=", "", strtr( base64_encode( $data ), "+/", "-_" ) );
	}

	/**
	 * Get Length of JWT Data
	 * @param String $data
	 * @return Integer
	 */
	private static function jwtStrlen( $data )
	{
		if ( function_exists( "mb_strlen" ) ) {
			return mb_strlen( $data, "8bit" );
		}

		return strlen( $data );
	}
}
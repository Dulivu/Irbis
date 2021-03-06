<?php

namespace Irbis;


/**
 * @package 	irbis
 * @author		Jorge Luis Quico C. <GeorgeL1102@gmail.com>
 * @version		1.0
 */
class Json {

	private static $jsonsCache = [];
	public static $decodeAssoc = true;
	
	protected static $_messages = array(
		JSON_ERROR_NONE => 'JSON, No error has occurred',
		JSON_ERROR_DEPTH => 'JSON, The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'JSON, Invalid or malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'JSON, Control character error, possibly incorrectly encoded',
		JSON_ERROR_SYNTAX => 'JSON, Syntax error',
		JSON_ERROR_UTF8 => 'JSON, Malformed UTF-8 characters, possibly incorrectly encoded'
	);

	public static function encode($value, $options = JSON_UNESCAPED_UNICODE) {
		$result = json_encode($value, $options);
		if ($result !== false) return $result;
		throw new \RuntimeException(static::$_messages[json_last_error()]);
	}

	public static function decode(string $json, bool $assoc = false) {
		$result = json_decode($json, $assoc);
		if ($result !== null) return $result;
		throw new \RuntimeException(static::$_messages[json_last_error()]);
	}
}

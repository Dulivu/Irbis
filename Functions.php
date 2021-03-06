<?php

if (DEBUG_MODE) {
	error_reporting( E_ALL );
	ini_set('display_errors', 1);
	# ini_set('display_startup_errors', 1);
}

/*
|--------------------------------------------------------------------------
| Autoloader
|--------------------------------------------------------------------------
|
| permite realizar autocarga de clases en función del namespace y el nombre de la clase
| registra un directorio base para la busqueda de los archivos PHP, al directorio enviado le
| agrega el directorio base establecido en BASE_PATH
|
*/
function irbis_loader (string $base = '') {
	spl_autoload_register(function ($k) use ($base) {
		$s = DIRECTORY_SEPARATOR;
		$base = str_replace(['\\','/'], $s, $base).$s;
		$path = implode($s, explode('\\', $k)).'.php';
		$file = BASE_PATH.$base.$path;
		if (file_exists($file)) require_once($file);
	});
}

/*
|--------------------------------------------------------------------------
| Herramientas
|--------------------------------------------------------------------------
|
| safe_file_write(string $file, mix $data): guardar datos en un archivo
| write_ini_file(string $fileName, array $data): guarda un array como un archivo de configuracion
|
*/
function safe_file_write (string $file, $data) {
	if ($fp = fopen($file, 'w')) {
		$time = microtime(TRUE);
		do {
			$writeable = flock($fp, LOCK_EX);
			if(!$writeable) usleep(round(rand(0, 100)*1000));
		} while ((!$writeable) && ((microtime(TRUE)-$time) < 5));

		if ($writeable) {
			fwrite($fp, $data);
			flock($fp, LOCK_UN);
		}

		fclose($fp);
	} else trigger_error('Error, al modificar el archivo de configuración: '.$file);
}

function write_ini_file (string $file, array $data) {
	$res = array();
	foreach($data as $key => $val) {
		if (is_array($val)) {
			$res[] = "[$key]";
			foreach($val as $k => $v) 
				$res[] = "$k = ".(is_numeric($v) ? $v : '"'.$v.'"');
		}
		else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
	}
	safe_file_write($file, implode("\r\n", $res));
}

/*
|--------------------------------------------------------------------------
| Herramientas; arreglos
|--------------------------------------------------------------------------
| is_assoc:
|		devuelve true o false en caso un array sea o no asociativo.
|
| delete:
|		elimina un elemento de un arreglo dado, y lo devuelve.
|
| array_get, array_set, array_unset:
| 		entregado un arreglo asociativo, permite obtener, establecer o eliminar
|		un valor por medio de una ruta (devuelve el valor eliminado).
| 		$arr = ['uno'=> ['dos'=> ['tres' => 'valor']]]
| 		$val = array_get($arr, 'uno.dos.tres') // 'valor'
|
*/
function is_assoc($_array) { 
	if ( !is_array($_array) || empty($_array) )
		return false;
	$keys = array_keys($_array);
	return array_keys($keys) !== $keys;
}

function delete (&$arr, $key) {
	$tmp = $arr[$key];
	unset($arr[$key]);
	return $tmp;
}

function array_get(array $array, string $path, string $separator = '.') {
	$keys = explode($separator, $path);
	$current = $array;
	foreach ($keys as $key) {
		if (!isset($current[$key])) return;
		$current = $current[$key];
	}
	return $current;
}

function array_set(array &$array, string $path, $value, string $separator = '.') {
	$keys = explode($separator, $path);
	$current = &$array;
	foreach ($keys as $key) {
		$current = &$current[$key];
	}
	$current = $value;
}

function array_unset(array &$array, string $path, string $separator = '.') {
	$keys = explode($separator, $path);
	$current = &$array;
	$parent = &$array;
	foreach ($keys as $i => $key) {
		if (!array_key_exists($key, $current)) return;
		if ($i) $parent = &$current;
		$current = &$current[$key];
	}
	$temp = $parent[$key];
	unset($parent[$key]);
	return $temp;
}

/*
|--------------------------------------------------------------------------
| Herramientas; cadenas de texto
|--------------------------------------------------------------------------
|
| encrypt: 		encripta una cadena
| decrypt: 		desencripta una cadena
|
| decamelize, camelize:
|		convierten cadenas de texto en formato CamelCase y viceversa
| 		HolaMundoGenial => hola_mundo_genial
|
*/
function encrypt($cadena){
	return base64_encode(openssl_encrypt($cadena, CRYPT_METHOD, CRYPT_KEY, 0, ''));
}
 
function decrypt($cadena){
	return rtrim(openssl_decrypt(base64_decode($cadena), CRYPT_METHOD, CRYPT_KEY, 0, ''), "\0");
}

function decamelize ($string) {
	return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
}

function camelize ($string) {
	return $word = preg_replace_callback("/(^|_)([a-z])/", function($m) { 
		return strtoupper("$m[2]"); 
	}, $string);
}
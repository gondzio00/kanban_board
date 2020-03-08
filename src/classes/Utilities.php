<?php
namespace KanbanBoard;

/**
 * Utilities
 */
class Utilities
{	
	/**
	 * __construct
	 *
	 * @return void
	 */
	private function __construct() {
	}
	
	/**
	 * env
	 *
	 * @param  mixed $name
	 * @param  mixed $default
	 * @return string
	 */
	public static function env(string $name, string $default = NULL) : string
	{
		$value = getenv($name);
		if($default !== NULL) {
			if(!empty($value))
				return $value;
			return $default;
		}
		return (empty($value) && $default === NULL) ? die('Environment variable ' . $name . ' not found or has no value') : $value;
	}
	
	/**
	 * hasValue
	 *
	 * @param  mixed $array
	 * @param  mixed $key
	 * @return bool
	 */
	public static function hasValue(array $array, string $key) : bool
	{
		return is_array($array) && array_key_exists($key, $array) && !empty($array[$key]);
	}
	
	/**
	 * dump
	 *
	 * @param  mixed $data
	 * @return void
	 */
	public static function dump(array $data) : void
	{
		echo '<pre>';
		var_dump($data);
		echo '</pre>';
	}
}
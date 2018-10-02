<?php 

/*
* Allows us to access config array
* Sam Fullen
* 11/12/16
*/

class Config {

	// Get path to config element in config array
	public static function get($path = null) {
		if ($path) {
			$config = $GLOBALS['config'];
			// Explode the path that is passed e.g.
			// if Config::get('mysql/host') is called
			// explode into mysql and host
			$path = explode('/', $path);

			// Then foreach through our exploded string
			foreach ($path as $bit) {
				// echo '<pre>' .print_r($bit, TRUE) .'</pre>';

				// Check if what is passed actually exists in config array
				// Using the example Config::get('mysql/host') we get mysql
				// and host, mysql is an array directly under the config array
				// host is within that array. So on first time through foreach
				// it checks if 'mysql' exists within config array, if it does
				// we set config = mysql, then in next loop it checks if 'host'
				// exists in $config (which is now set to mysql)
				if (isset($config[$bit])) {
					$config = $config[$bit];
				}
			}

			return $config;
		}

		return false;
	}

}
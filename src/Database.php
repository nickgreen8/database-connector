<?php
namespace N8G\Database;

use N8G\Utils\Log,
	N8G\Database\Databases\MySql,
	N8G\Database\Databases\Mongo,
	N8G\Database\Exceptions\DatabaseException;

/**
 * This class is used to connect to the relevant database and interact with it. Before anything can
 * happen in regards to interaction, the init function must be called to establish the connection.
 * Once a connection has been made, the database can be fully interacted with.
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
class Database
{
	/**
	 * The database class to interact with.
	 * @var object
	 */
	private static $db;

	/**
	 * This is the function that will create the connection to the relevant database. If the function
	 * is successful in connecting to the DB, the new object is stored. If not, the 'db' variable is
	 * set to NULL. Nothing is returned. The parameters that are passed to the function are the
	 * parmameters needed to access the database and they type of database.
	 *
	 * @param  array|object $conf The data needed to create the database connection.
	 * @param  string $dbType     The type of database to be connected to (Default: mysql)
	 * @return void
	 */
	public static function init($conf, $dbType = 'mysql')
	{
		Log::notice('Initilising database connection');

		//Convert conf if required
		if (is_object($conf)) {
			$conf = (array) $conf;
		}
		//Check for host
		$conf['host'] = !isset($conf['host']) ? 'localhost' : $conf['host'];

		//Make connection to the database
		try {
			//Get the relevant database class
			switch ($dbType) {
				case 'mysql' :
					Log::notice('Attempting connection to MySQL database');
					self::$db = MySql::getInstance();
					self::$db->connect($conf['host'], $conf['username'], $conf['password'], $conf['name']);
					break;

				case 'mongo':
					Log::notice('Attempting connection to MongoDB');
					self::$db = Mongo::getInstance();
					self::$db->connect($conf['host'], isset($conf['port']) ? $conf['port'] : 27017, $conf['name']);
					break;
			}

			Log::success('Database connection established');
		} catch (\Exception $e) {
			self::$db = null;
		}
	}

	/**
	 * This function is used to call the function that is called on the relevant database class. The
	 * method that is called is the first paramter that is passed. The arguments that are passed are
	 * then passed. The relevant function is then checked and called with the parameters passed on.
	 * The result is then returned. If the function does not exist or a connection has not been made,
	 * then an exception is throw.
	 *
	 * @param  string $method The method to be called.
	 * @param  array  $args   An array of the arguments passed to the function.
	 * @return mixed          The result of the function call.
	 */
	public static function __callStatic($method, $args)
	{
		//Check for connection
		if (!isset(self::$db)) {
			throw new DatabaseException('There was no database connection found.');
		}

		//Check that the function exists
		if (method_exists(self::$db, $method)) {
			//Call the function
			return call_user_func_array(array(self::$db, $method), $args);
		}

		throw new DatabaseException('Function not implemented');
	}
}
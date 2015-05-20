<?php
namespace N8G\Database;

use N8G\Utils\Log,
	N8G\Database\DatabaseInterface,
	N8G\Database\Databases\MySql,
	N8G\Database\Exceptions\QueryException,
	N8G\Database\Exceptions\NoDatabaseConnectionException,
	N8G\Database\Exceptions\UnableToCreateDatabaseConnectionException;

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
	 * The prefix to database tables
	 * @var string
	 */
	private static $prefix;

	/**
	 * This is the function that will create the connection to the relevant database. If the function
	 * is successful in connecting to the DB, the new object is stored. If not, the 'db' variable is
	 * set to NULL. Nothing is returned. The parameters that are passed to the function are the
	 * username and password to the database. The DB name is then passed followed by the DB type.
	 * The final argumant is the host of the DB.
	 *
	 * @param  string $username The username to log into the database
	 * @param  string $password The password to log into the database
	 * @param  string $dbName   The name of the database to connect to
	 * @param  string $dbType   The type of database to be connected to (Default: mysql)
	 * @param  string $host     The database host (Default: localhost)
	 * @return void
	 */
	public static function init($username, $password, $dbName, $dbType = 'mysql', $host = 'localhost')
	{
		Log::info('Initilising database connection');

		//Make connection to the database
		try {
			//Get the relevant database class
			switch ($dbType) {
				case 'mysql' :
					Log::notice('Attempting connection to MySQL database');
					self::$db = MySql::getInstance();
					self::$db->connect($host, $username, $password, $dbName);
					break;
			}

			Log::success('Database connection established');
		} catch (UnableToCreateDatabaseConnectionException $e) {
			self::$db = null;
		}
	}

	/**
	 * This function is used to build up a query and then to execute it. It is more of
	 * a helper function that something that will be used in practice. The table to
	 * query, the data, an action and any parameters are passed. The query is then built
	 * and executed. The result object is then returned.
	 *
	 * @param  string $table      The table to be interacted with as a string
	 * @param  array  $data       An array of data
	 * @param  string $action     'select', 'insert' or 'update'
	 * @param  mixed  $parameters Either an array of parameters or a string
	 * @return object             A query reuslt object
	 */
	public static function perform($table, array $data, $action = 'insert', $parameters = null)
	{
		Log::notice('Building query');

		if (strtoupper($action) !== 'SELECT' && strtoupper($action) !== 'INSERT' && strtoupper($action) !== 'UPDATE' && strtoupper($action) !== 'DELETE') {
			Log::ERROR(sprintf('An invalid action was specified. The action must be INSERT, UPDATE, SELECT or DELETE. %s specified.', strtoupper($action)));
			return;
		}

		if (strtoupper($action) === 'SELECT') {
			//Create query string
			$query = 'SELECT ';

			//Build the query
			foreach ($data as $arg) {
				$query .= $arg . ', ';
			}
			$query = substr($query, 0, -2);

			$query .= ' FROM ';
			$query .= $table;

			//Check for params
			if ($parameters !== null) {
				$query .= ' WHERE ' . $parameters;
			}
		} elseif (strtoupper($action) === 'INSERT') {
			//Create query string
			$query = 'INSERT INTO ' . $table . ' (';
			$values = '';

			//Build query
			foreach ($data as $col => $val) {
				$query .= $col . ', ';

				if (is_int($val)) {
					$values .= $val . ', ';
				} elseif (is_bool($val)) {
					$values .= $val === true ? 'TRUE, ' : 'FALSE, ';
				} else {
					$values .= '\'' . $val . '\', ';
				}
			}

			$query = substr($query, 0, -2) . ') VALUES (' . substr($values, 0, -2) . ')';
		} elseif (strtoupper($action) === 'UPDATE') {
			//Create query string
			$query = 'UPDATE ' . $table . ' SET ';

			//Build query
			foreach ($data as $col => $val) {
				$query .= $col . ' = ';

				if (is_int($val)) {
					$query .= $val . ', ';
				} elseif (is_bool($val)) {
					$query .= $val === true ? 'TRUE, ' : 'FALSE, ';
				} else {
					$query .= '\'' . $val . '\', ';
				}
			}

			$query = substr($query, 0, -2) . ' WHERE ' . $parameters;
		} elseif (strtoupper($action) === 'DELETE') {
			//Create query string
			$query = 'DELETE FROM ' . $table . ' WHERE ' . $parameters;
		}

		Log::success(sprintf('Query built: %s', $query));

		//Make the query
		return self::query($query);
	}

	/**
	 * This function is used to make a query. All the query needs is a query in the
	 * form of a string and a result object is returned.
	 *
	 * @param  string $query The query to be passed to the DB
	 * @return object        A query result object
	 */
	public static function query($query)
	{
		Log::notice(sprintf('Executing query: %s', $query));

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->query($query);
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to make multipul queries to the database at once. The
	 * queries can be passed in as an array of strings or long string. A result object
	 * is returned.
	 *
	 * @param  mixed  $queries Either an array of strings that make up the queries or a long string.
	 * @return object          A query result object
	 */
	public static function multiQuery($queries)
	{
		Log::notice('Executing multiple queries');

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->mulitQuery($queries);
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to execute a database procedure. The name of the procedure
	 * to be executed is passed followed by an array which is the arguments to be passed
	 * to the procedure.
	 *
	 * @param  string $name The name of the procedure to be executed.
	 * @param  array  $args The arguments to be passed to the procedure.
	 * @return object       A query result object.
	 */
	public static function execProcedure($name, $args = array())
	{
		Log::notice('Executing procedure');
		Log::debug('The name of the procedure being executed is: %s(%s)', $name, implode(', ', $args));

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->execProcedure();
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function gets the number of rows returned by the query. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object (Default: null)
	 * @return int            The number of rows returned from the query
	 */
	public static function getNumRows($result = null)
	{
		Log::notice('Getting the number of rows retrieved');

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return (int) self::$db->getNumRows($result);
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to get the query results as an array. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object (Default: null)
	 * @return array          The query result in the form of an array
	 */
	public static function getArray($result = null)
	{
		Log::notice('Getting the result of the query as an array');

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->getArray($result);
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to get the ID of the inserted or updated record in the
	 * database. The ID is returned as an integer.
	 *
	 * @return int The ID of the record added to the DB
	 */
	public static function getInsertID()
	{
		Log::notice('Getting the ID of the last item added to the database');

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->getInsertID();
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function closes the database connection. This ensures that the database is
	 * not clogged up with connections. This will be called in the destructor the
	 * majority of the time.
	 *
	 * @return void
	 */
	public static function close()
	{
		Log::notice('Closing the database connection');

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->close();
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function gets the connection to the database. This is so that it can be
	 * utilised in multipul places.
	 *
	 * @return object DB connection object
	 */
	public static function getConnection()
	{
		Log::notice('Getting the database connection');

		try {
			if (!isset(self::$db)) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}

			return self::$db->getConnection();
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to set the database table prefixes.
	 *
	 * @param  string $prefix The DB table prefix
	 * @return void
	 */
	public static function setPrefix($prefix)
	{
		if (self::$prefix !== $prefix) {
			Log::notice(sprintf('Setting DB table prefix to: %s', $prefix));

			self::$prefix = $prefix;
		}
	}

	/**
	 * Gets the prefixed value on the DB tables
	 *
	 * @return string The prefix to the DB tables
	 */
	public static function getPrefix()
	{
		return self::$prefix;
	}
}
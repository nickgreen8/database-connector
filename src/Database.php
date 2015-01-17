<?php
namespace N8G\Database;

use N8G\Utils\Log,
	N8G\Database\DatabaseInterface,
	N8G\Database\Databases\MySQL,
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
class Database implements DatabaseInterface
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
	public static function init($username, $password, $dbName, $dbType = 'mysql', $host = 'localhost') {
		//Make connection to the database
		try {
			//Get the relevant database class
			switch ($dbType) {
				case 'mysql' :
					Log::notice('Attempting connection to MySQL database');
					self::$db = new MySql($host, $username, $password, $dbName);
					break;
			}

			Log::info('Database connection established');
			return self::$db;
		} catch (UnableToCreateDatabaseConnectionException $e) {
			self::$db = null;
		}
	}

	/**
	 * This function checks for a database connection. If there is no connection then no interactions
	 * can be made.
	 *
	 * @return bool
	 */
	private function checkForConnetion()
	{
		if (self::$db !== null) {
			return true;
		} else {
			return false;
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
	public function perform(string $table, array $data, $action = 'insert', $parameters = null)
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to make a query. All the query needs is a query in the
	 * form of a string and a result object is returned.
	 *
	 * @param  string $query The query to be passed to the DB
	 * @return object        A query result object
	 */
	public function query($query)
	{
		var_dump(self::$db);
		/*Log::info(sprintf('Attempting query: %s', $query));
		try {
var_dump($this->$checkForConnetion());
echo '<br />';
			if (!$this->$checkForConnetion()) {
echo '4<br />';
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
echo '5<br />';
			return self::$db->query($query);
echo '6<br />';
		} catch (NoDatabaseConnectionException $e) {}
		  catch (QueryException $e) {}
echo '7<br />';*/
	}

	/**
	 * This function is used to make multipul queries to the database at once. The
	 * queries can be passed in as an array of strings or long string. A result object
	 * is returned.
	 *
	 * @param  mixed  $queries Either an array of strings that make up the queries or a long string.
	 * @return object          A query result object
	 */
	public function mulitQuery($queries)
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to execute a database procedure.
	 *
	 * @return object          A query result object
	 */
	public function execProcedure()
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function gets the number of rows returned by the query. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object
	 * @return int            The number of rows returned from the query
	 */
	public function getNumRows($result)
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to get the query results as an array. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object
	 * @return array          The query result in the form of an array
	 */
	public function getArray($result)
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function is used to get the ID of the inserted or updated record in the
	 * database. The ID is returned as an integer.
	 *
	 * @return int The ID of the record added to the DB
	 */
	public function getInsertID()
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function closes the database connection. This ensures that the database is
	 * not clogged up with connections. This will be called in the destructor the
	 * majority of the time.
	 *
	 * @return void
	 */
	public function close()
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}

	/**
	 * This function gets the connection to the database. This is so that it can be
	 * utilised in multipul places.
	 *
	 * @return object DB connection object
	 */
	public function getConnection()
	{
		try {
			if (!self::$checkForConnetion()) {
				throw new NoDatabaseConnectionException('There was no database connection found.');
			}
		} catch (NoDatabaseConnectionException $e) {}
	}
}
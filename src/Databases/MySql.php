<?php
namespace N8G\Database\Databases;

use N8G\Database\Exceptions\MySqlException,
	N8G\Utils\Log,
	\mysqli;

/**
 * This class is used to connect to a MySQL database and interact with it. Before anything can
 * happen in regards to interaction, the connect function must be called to establish the
 * connection. Once a connection has been made, the database can be fully interacted with.
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
class MySql
{
	/**
	 * An instance of this class
	 * @var object|null
	 */
	private static $instance = null;

	/**
	 * An instance of the connection to the database
	 * @var object
	 */
	private $connection;

	/**
	 * In instance of the last query object
	 * @var object
	 */
	private $query;

	/**
	 * Default constructor
	 */
	private function __construct() {}

	/**
	 * This function creates or gets an instance of the MySQL class. Nothing is passed and an
	 * instance of this object is returned.
	 *
	 * @return object
	 */
	public static function getInstance()
	{ 
		if (!self::$instance) { 
			self::$instance = new MySql(); 
		}
		return self::$instance; 
	}

	/**
	 * This function is used to make the connection to the database. Four arguments are passed to
	 * the function. The first is the database host, the next is the username and then the password
	 * with the name of the database to connect with passed last. The function then returns an
	 * instance of the connection.
	 *
	 * @param  string $host     The DB host
	 * @param  string $username The DB username
	 * @param  string $password The DB password
	 * @param  string $name     The DB to connect with
	 * @return object
	 */
	public function connect($host, $username, $password, $name)
	{
		$this->connection = new mysqli($host, $username, $password, $name);

		//Check for connection
		if (mysqli_connect_errno()) {
			throw new MySqlException(sprintf('Connect failed: %s', mysqli_connect_error()), Log::FATAL);
		}

		return $this->connection;
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
		//Check for success
		if ($this->query = $this->connection->query($query)) {
			//Return results
			return $this->query;
		}

		//Throw error if not successful
		throw new MySqlException(sprintf('Query error: #%d - %s', $this->connection->errno, $this->connection->error), Log::FATAL);
	}

	/**
	 * This function is used to make multipul queries to the database at once. The
	 * queries can be passed in as an array of strings or long string. A result object
	 * is returned.
	 *
	 * @param  mixed  $queries Either an array of strings that make up the queries or a long string.
	 * @return object          A query result object
	 */
	public function multiQuery($query)
	{
		//Check for success
		if ($this->connection->multi_query($query)) {
			//Return results
			return $this->query;
		}

		//Throw error if not successful
		throw new MySqlException(sprintf('Query error: #%d - %s', $this->connection->errno, $this->connection->error), Log::FATAL);
	}

	/**
	 * This function is used to execute a database procedure. The name of the procedure
	 * and the arguments to pass to the procedure are passed.
	 *
	 * @param  string $procedure  The name of the procedure to be executed.
	 * @param  string|array $args The arguments as an array or string to be passed to the procedure
	 * @return array              An array of the data retrieved
	 */
	public function execProcedure($procedure, $args)
	{
		Log::debug(sprintf('Executing procedure againse the MySQL databse: CALL %s(%s)', $procedure, is_array($args) ? implode(', ', $args) : $args));

		//Create result array
		$result = array();
		//Execute the procedure
		$this->connection->multi_query(sprintf('CALL %s(\'%s\')', $procedure, is_array($args) ? implode('\', \'', $args) : $args));

		//Go through the data
		do {
			//Check the data
			if ($res = $this->connection->store_result()) {
				while ($data = $res->fetch_assoc()) {
					//Add data to return array
					array_push($result, $data);
				}
				$res->free();
			} else {
				//Check for an error
				if ($this->connection->errno) {
					Log::error(sprintf('Store failed: (%s) %s', $this->connection->errno, $this->connection->error));
				}
			}
		} while ($this->connection->more_results() && $this->connection->next_result());

		return $result;
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
		if ($result === null) {
			return $this->query->num_rows;
		} else {
			return $result->num_rows;
		}
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
		if ($result === null) {
			return $this->query->fetch_assoc();
		} else {
			return $result->fetch_assoc();
		}
	}

	/**
	 * This function is used to get the ID of the inserted or updated record in the
	 * database. The ID is returned as an integer.
	 *
	 * @return int The ID of the record added to the DB
	 */
	public function getInsertID()
	{
		return $this->connection->insert_id;
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
		return $this->connection->close();
	}

	/**
	 * This function gets the connection to the database. This is so that it can be
	 * utilised in multipul places.
	 *
	 * @return object DB connection object
	 */
	public function getConnection()
	{
		return $this->connection;
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
	public function perform($table, array $data, $action = 'insert', $parameters = null)
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
}
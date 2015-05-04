<?php
namespace N8G\Database\Databases;

use N8G\Utils\Log,
	N8G\Database\DatabaseInterface,
	\mysqli;

/**
 * This class is used to connect to a MySQL database and interact with it. Before anything can
 * happen in regards to interaction, the connect function must be called to establish the
 * connection. Once a connection has been made, the database can be fully interacted with.
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
class MySql implements DatabaseInterface
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
	function connect($host, $username, $password, $name)
	{
		$this->connection = mysqli_connect($host, $username, $password, $name);
		return $this->connection;
	}

	/**
	 * This function is used to make a query. All the query needs is a query in the
	 * form of a string and a result object is returned.
	 *
	 * @param  string $query The query to be passed to the DB
	 * @return object        A query result object
	 */
	function query($query)
	{
		$this->query = $this->connection->query($query);
		return $this->query;
	}

	/**
	 * This function is used to make multipul queries to the database at once. The
	 * queries can be passed in as an array of strings or long string. A result object
	 * is returned.
	 *
	 * @param  mixed  $queries Either an array of strings that make up the queries or a long string.
	 * @return object          A query result object
	 */
	function multiQuery($query)
	{
		return $this->connection->multi_query($query);
	}

	/**
	 * This function is used to execute a database procedure.
	 *
	 * @return object          A query result object
	 */
	public function execProcedure()
	{}

	/**
	 * This function gets the number of rows returned by the query. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object
	 * @return int            The number of rows returned from the query
	 */
	function getNumRows($result)
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
	function getArray($result)
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
	function getInsertID()
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
	function close()
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
}
<?php
namespace N8G\Database\Databases;

use N8G\Utils\Log,
	N8G\Database\DatabaseInterface,
	N8G\Database\Exceptions\UnableToCreateDatabaseConnectionException,
	N8G\Database\Exceptions\QueryException,
	\mysqli;

/**
 * This class is used to connect to a MySQL database and interact with it. Before anything can
 * happen in regards to interaction, the connect function must be called to establish the
 * connection. Once a connection has been made, the database can be fully interacted with.
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
class MySql extends mysqli
{
	/**
	 * The constructor takes no arguments
	 */
	public function __construct($host, $username, $password, $dbName)
	{
		parent::__construct($host, $username, $password, $dbName);
	}

	/**
	 * Default destructor
	 */
	public function __destruct() {
		self::close();
	}

	public function query($query) {
		//Attept query
		$result = $this->query($query);

		//Check the query was successful
		if ($result === FALSE) {
			throw new QueryException(sprintf('[%s] %s - %s', $this->errno, $this->error, $query));
		}

		//Return query result
		return $result;
	}
}
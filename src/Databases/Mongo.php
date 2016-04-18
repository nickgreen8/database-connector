<?php
namespace N8G\Database\Databases;

use N8G\Utils\Log,
	N8G\Database\Exceptions\DatabaseException,
	\MongoClient;

/**
 * This class is used to connect to a MongoDB and interact with it. Before anything can
 * happen in regards to interaction, the connect function must be called to establish the
 * connection. Once a connection has been made, the database can be fully interacted with.
 *
 * Pre-request: PHP MongoClient must be installed on the server
 * (http://php.net/manual/en/mongo.installation.php).
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
class Mongo
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
	 * The connection to the specific DB
	 * @var object
	 */
	private $db;

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
			self::$instance = new Mongo(); 
		}
		return self::$instance; 
	}

	/**
	 * This function is used to make the connection to the database. Four arguments are passed to
	 * the function. The first is the database host, the next is the username and then the password
	 * with the name of the database to connect with passed last. The function then returns an
	 * instance of the connection.
	 *
	 * @param  string $name     The DB to connect with
	 * @param  string $host     The DB host
	 * @param  string $port     The port that the DB is running on
	 * @return object
	 */
	public function connect($name, $host = 'localhost', $port = 27017)
	{
		Log::notice('Creating MongoDB connection');

		$this->connection = new MongoClient(sprintf('mongodb://%s:%s', $host, $port));

		if ($name === null) {
			//Throw exception
			throw new MongoException('No database name specified');
		}

		Log::debug('Making connection to specific DB');
		$this->db = $this->connectToDb($name);

		return $this->db;
	}

	private function connectToDb($db)
	{
		Log::info(sprintf('Making connection to: %s', $db));
		//Make connection to specific DB
		return $this->connection->$db;
	}

	public function close()
	{
		//Get the list of connections
		$connections = $this->connection->getConnections();

		//Close connections
		foreach ($connections as $con) {
			if ($con['connection']['connection_type_desc'] === 'SECONDARY') {
				if (!$this->connection->close($con['hash'])) {
					throw new DatabaseException('Mongo connection could not be closed!');
				}
			}
		}
	}

	public function getConnection()
	{
		return $this->connection;
	}
}
<?php
namespace N8G\Database\Databases;

use N8G\Database\DatabaseInterface;
use N8G\Database\DatabaseException;
use \MongoClient;

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
class Mongo implements DatabaseInterface
{
    /**
     * A referance to the application container.
     * @var object
     */
    private $container;

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
     * Default constructor.
     * Takes the an instance of the application container.
     *
     * @param object $container Instance of the application container.
     */
    public function __construct($container, $type)
    {
        //Set the container
        $this->container = $container;
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
        $this->container->get('logger')->notice('Creating MongoDB connection');

        $this->connection = new MongoClient(
            sprintf('mongodb://%s:%s', $credentials['host'], $credentials['port'])
        );

        if ($name === null) {
            //Throw exception
            throw new MongoException('No database name specified');
        }

        $this->container->get('logger')->debug('Making connection to specific DB');
        $this->db = $this->connectToDb($name);

        return $this->db;
    }

    /**
     * Connects to a specific database within Mongo. The database name to connect to. The connection is then returned.
     *
     * @param  string $db The name of the database to connect to.
     * @return object     Mongo database connection.
     */
    private function connectToDb($db)
    {
        $this->container->get('logger')->info(sprintf('Making connection to: %s', $db));
        //Make connection to specific DB
        return $this->connection->$db;
    }

    /**
     * Closes the connection to the DB.
     *
     * @return void
     */
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

    /**
     * Gets the database connection.
     *
     * @return object The connection to the database.
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

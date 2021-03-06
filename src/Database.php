<?php
namespace N8G\DatabaseConnector;

use N8G\DatabaseConnector\DatabaseException;
use N8G\DatabaseConnector\Databases\MySql;
use N8G\DatabaseConnector\Databases\Mongo;

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
    private $db;

    /**
     * Default constructor.
     * Takes the an instance of the application container so that the database instances can utilise the contents. The
     * database type is also passed as a string so that the relevant object can be created for the right type of
     * database.
     *
     * @param object $container Instance of the application container.
     * @param string $type      Type of database object to create.
     */
    public function __construct($container, $type)
    {
        //Set the container
        $this->container = $container;

        //Get the relevant database object
        switch ($type) {
            case 'mysql':
                $this->db = new MySql($container);
                break;

            case 'mongo':
                $this->db = new Mongo($container);
                break;
            
            default:
                throw new DatabaseException(sprintf('The database type specified is invalid. Type: %s.', $type));
                break;
        }
    }

    /**
     * Default destructor.
     * If there is a close function for the database it is called to close the DB connection.
     */
    public function __destruct()
    {
        //Check that the function exists
        if (method_exists($this->db, 'close')) {
            //Call the close function
            $this->db->close();
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
    public function __call($method, $args)
    {
        //Check for connection
        if (!isset($this->db)) {
            throw new DatabaseException('There was no database connection found.');
        }

        //Check that the function exists
        if (method_exists($this->db, $method)) {
            //Call the function
            return call_user_func_array(array($this->db, $method), $args);
        }

        throw new DatabaseException('Function not implemented');
    }

    /**
     * Gets the database type from the database object that has been created.
     *
     * @return string Database type.
     */
    public function getDatabaseType()
    {
        return str_replace('N8G\DatabaseConnector\Databases\\', '', get_class($this->db));
    }
}

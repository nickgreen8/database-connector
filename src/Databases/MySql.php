<?php
namespace N8G\DatabaseConnector\Databases;

use N8G\DatabaseConnector\DatabaseInterface;
use N8G\DatabaseConnector\DatabaseException;
use \mysqli;

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
     * A referance to the application container.
     * @var object
     */
    private $container;

    /**
     * An instance of the connection to the database.
     * @var object
     */
    private $connection;

    /**
     * Instance of the last query object.
     * @var object
     */
    private $query;

    /**
     * Default constructor.
     * Takes the an instance of the application container.
     *
     * @param object $container Instance of the application container.
     */
    public function __construct($container)
    {
        //Set the container
        $this->container = $container;
    }

    /**
     * This function is used to make the connection to the database. The only argument passed is the database
     * credentials as either an array or an object. The function then returns nothing.
     *
     * @param  string $credentials Database credentials to be used to connect to the DB.
     * @return void
     */
    public function connect($credentials)
    {
        //Format the credentials
        $credentials = (array) $credentials;
        $this->connection = new mysqli(
            $credentials['host'],
            $credentials['username'],
            $credentials['password'],
            $credentials['name']
        );

        //Check for connection
        if (mysqli_connect_errno()) {
            throw new DatabaseException(sprintf('Connect failed: %s', mysqli_connect_error()), 500);
        }

        $this->container->get('logger')->notice('Database connection made');

        return $this->connection;
    }

    /**
     * This function is used to make a query. All the query needs is a query in the
     * form of a string and a result object is returned.
     *
     * @param  string $query The query to be passed to the DB.
     * @return object        A query result object.
     */
    public function query($query)
    {
        $this->container->get('logger')->debug(sprintf('Executing Query: %s', $query));

        //Check for success
        if ($this->query = $this->connection->query($query)) {
            //Return results
            return $this->query;
        }

        //Throw error if not successful
        throw new DatabaseException(
            sprintf('Query error: #%d - %s', $this->connection->errno, $this->connection->error),
            500
        );
    }

    /**
     * This function is used to make multipul queries to the database at once. The
     * queries should be passed as a string. A result object is returned.
     *
     * @param  string $queries A string of queries.
     * @return object          A query result object.
     */
    public function multiQuery($query)
    {
        $this->container->get('logger')->debug(sprintf('Executing Multi-query: %s', $query));

        //Check for success
        if ($this->connection->multi_query($query)) {
            //Return results
            return $this->query;
        }

        //Throw error if not successful
        throw new DatabaseException(
            sprintf('Query error: #%d - %s', $this->connection->errno, $this->connection->error),
            500
        );
    }

    /**
     * This function is used to execute a database procedure. The name of the procedure
     * and the arguments to pass to the procedure are passed.
     *
     * @param  string       $procedure  The name of the procedure to be executed.
     * @param  string|array $args       The arguments as an array or string to be passed to the procedure.
     * @return array                    An array of the data retrieved.
     */
    public function execProcedure($procedure, $args)
    {
        $this->container->get('logger')->debug(
            sprintf(
                'Executing procedure againse the MySQL databse: CALL %s(%s)',
                $procedure,
                is_array($args) ? implode(', ', $args) : $args
            )
        );

        //Create result array
        $result = array();
        //Execute the procedure
        $this->connection->multi_query(
            sprintf('CALL %s(\'%s\')', $procedure, is_array($args) ? implode('\', \'', $args) : $args)
        );

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
                    $this->container->get('logger')->error(
                        sprintf('Store failed: (%s) %s', $this->connection->errno, $this->connection->error)
                    );
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
    public function getNumRows($result = null)
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
    public function getArray($result = null)
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
        if ($this->connection) {
            return $this->connection->close();
        }
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
        $this->container->get('logger')->notice('Building query');

        if (in_array(strtoupper($action), array('SELECT', 'INSERT', 'UPDATE', 'DELETE'))) {
            $this->container->get('logger')->error(sprintf(
                'An invalid action was specified. The action must be INSERT, UPDATE, SELECT or DELETE. %s specified.',
                strtoupper($action)
            ));
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

        $this->container->get('logger')->success(sprintf('Query built: %s', $query));

        //Make the query
        return $this->query($query);
    }
}

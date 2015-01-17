<?php
namespace N8G\Database;

interface DatabaseInterface
{
	/**
	 * This function is used to make a query. All the query needs is a query in the
	 * form of a string and a result object is returned.
	 *
	 * @param  string $query The query to be passed to the DB
	 * @return object        A query result object
	 */
	public function query($query);

	/**
	 * This function is used to make multipul queries to the database at once. The
	 * queries can be passed in as an array of strings or long string. A result object
	 * is returned.
	 *
	 * @param  mixed  $queries Either an array of strings that make up the queries or a long string.
	 * @return object          A query result object
	 */
	public function mulitQuery($queries);

	/**
	 * This function is used to execute a database procedure.
	 *
	 * @return object          A query result object
	 */
	public function execProcedure();

	/**
	 * This function gets the number of rows returned by the query. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object
	 * @return int            The number of rows returned from the query
	 */
	public function getNumRows($result);

	/**
	 * This function is used to get the query results as an array. The result object is
	 * passed and the number of rows is returned as an integer.
	 *
	 * @param  object $result The query result object
	 * @return array          The query result in the form of an array
	 */
	public function getArray($result);

	/**
	 * This function is used to get the ID of the inserted or updated record in the
	 * database. The ID is returned as an integer.
	 *
	 * @return int The ID of the record added to the DB
	 */
	public function getInsertID();

	/**
	 * This function closes the database connection. This ensures that the database is
	 * not clogged up with connections. This will be called in the destructor the
	 * majority of the time.
	 *
	 * @return void
	 */
	public function close();

	/**
	 * This function gets the connection to the database. This is so that it can be
	 * utilised in multipul places.
	 *
	 * @return object DB connection object
	 */
	public function getConnection();
}
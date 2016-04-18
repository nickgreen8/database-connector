<?php
namespace N8G\Database;

/**
 * This interface will outline all functions that must be included in each individual database object. This must be
 * implemented by each class that is built for each different database type.
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
interface DatabaseInterface
{
	/**
	 * This function will create the single database connection for the database type. The argument that is to be
	 * passed is either an array or an object of the credentials to make the database connection. Due to the different
	 * requirements for different database types, this should be no more specific than a single array or object.
	 * Nothing is returned from this function but there should be an internal referance to the database connection held
	 * in each class.
	 *
	 * @param  array|object $credentials The credentials to make the database connection.
	 * @return void
	 */
	public function connect($credentials);
}

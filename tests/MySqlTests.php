<?php
namespace Tests;

use N8G\Database\Database;

class MySqlTests extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testConnection()
	{
		$this->assertEquals('1', '1');
	}
}
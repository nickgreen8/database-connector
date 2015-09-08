<?php
namespace N8G\Database;

class DatabaseTests extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testConnection()
	{
		$this->assertEquals('1', '1');
	}
}
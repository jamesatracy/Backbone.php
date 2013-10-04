<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("MySQL");

/**
 * Mock MySQL class for spoofing a connection.
 */
class MockSQL extends MySQL
{
	public function __construct()
	{
		$this->_is_connected = true;
	}
}

/**
 * PHPUnit Test suite for MySQL class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class MySQLTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->db = new MockSQL();
	}
	
	public function testBehavior_describe()
	{
		$query = $this->db->buildQuery("describe", array("table" => "blog_posts"));
		$this->assertEquals($query, "DESCRIBE `blog_posts`");
	}
	
	public function testBehavior_select()
	{
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*"
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts`");
		
		// with one field
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "ID"
		));
		$this->assertEquals($query, "SELECT `ID` FROM `blog_posts`");
		
		// with multiple fields
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => array("ID", "title")
		));
		$this->assertEquals($query, "SELECT `ID`,`title` FROM `blog_posts`");
		
		// with limit
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"limit" => 10
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` LIMIT 10");
		
		// with offset
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"offset" => 50
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` OFFSET 50");
		
		// with offset and limit
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"limit" => 10,
			"offset" => 50
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` LIMIT 10 OFFSET 50");
		
		// order by
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"order_by" => "title"
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` ORDER BY `title` ASC");
		
		// order by, array notation
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"order_by" => array("title")
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` ORDER BY `title` ASC");
		
		// order by, array notation, DESC
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"order_by" => array("title", "DESC")
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` ORDER BY `title` DESC");
		
		// select count(*)
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "COUNT(*)"
		));
		$this->assertEquals($query, "SELECT COUNT(*) FROM `blog_posts`");
		
		// functions
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "MAX(tag_count)"
		));
		$this->assertEquals($query, "SELECT MAX(`tag_count`) FROM `blog_posts`");
		
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "MAX(field1,field2)"
		));
		$this->assertEquals($query, "SELECT MAX(`field1`,`field2`) FROM `blog_posts`");
	}
	
	// Note that these cover where clause construction tests for all methods
	public function testBehavior_selectWhere()
	{
		// simple key, value
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array("ID" => 10)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE (`ID` = '10')");
		
		// multiple key, value pairs
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array("ID" => 10, "type" => "News")
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE (`ID` = '10' AND `type` = 'News')");
		
		// LIKE compare
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array("author" => array("LIKE", "John"))
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE (`author` LIKE 'John')");
		
		// multiple LIKE compares
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array("author" => array("LIKE", "John"), "type" => array("LIKE", "News"))
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE (`author` LIKE 'John' AND `type` LIKE 'News')");
		
		// multiple key, value pairs, OR comparison
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array(
				"OR" => array(
					"ID" => 10, 
					"type" => "News"
				)
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE ((`ID` = '10' OR `type` = 'News'))");
		
		// multiple LIKE OR comparisons
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array(
				"OR" => array(
					"author" => array("LIKE", "John"), 
					"type" => array("LIKE", "News")
				)
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE ((`author` LIKE 'John' OR `type` LIKE 'News'))");
		
		// multiple OR comparisons on the same field
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array(
				"type" => array("OR", "News", "Gossip")
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE ((`type` = 'News' OR `type` = 'Gossip'))");
		
		// IN statement
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array(
				"type" => array("IN", "News", "Gossip")
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE (`type` IN (News,Gossip))");
		
		// NOT IN statement
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"where" => array(
				"type" => array("NOT IN", "News", "Gossip")
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` WHERE (`type` NOT IN (News,Gossip))");
	}
	
	public function testBehavior_selectJoin()
	{
		// default left join
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"join" => array(
				"blog_authors" => array(
					"fields" => array("blog_posts.author_id","blog_authors.id")
				)
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` LEFT JOIN `blog_authors` ON `blog_posts`.`author_id` = `blog_authors`.`id` ");
		
		// inner join
		$query = $this->db->buildQuery("select", array(
			"table" => "blog_posts",
			"fields" => "*",
			"join" => array(
				"blog_authors" => array(
					"type" => "INNER",
					"fields" => array("blog_posts.author_id","blog_authors.id")
				)
			)
		));
		$this->assertEquals($query, "SELECT * FROM `blog_posts` INNER JOIN `blog_authors` ON `blog_posts`.`author_id` = `blog_authors`.`id` ");
	}
}
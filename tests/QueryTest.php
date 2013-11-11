<?php
/**
 * Backbone.php
 * 
 * @author	James Tracy <james.a.tracy@gmail.com>
 * @copyright	2012-2013
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://github.com/jamesatracy/Backbone.php GitHub Page
 */

Backbone::uses("DB");
use Backbone\DB;
use Backbone\Query;

/**
 * Mock PDO
 */
class MockPDO
{
	public function quote($string)
	{
		return "'".$this->escape($string)."'";
	}
	
	public function escape($string)
	{
		return addslashes($string);
	}
}

/**
 * PHPUnit Test suite for Query class
 *
 * Tests for individual class methods following this naming
 * convention:
 *		public function testMethod_${name}
 *
 * Tests for general behavior following this naming 
 * conventions:
 * 		public function testBehavior_${description}
 */
class QueryTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$pdo = new MockPDO();
		DB::setPDO($pdo);
	}
	
	public function testBehavior_select()
	{
		$query = DB::table("blog_posts")->select()->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts");
		
		// with one field
		$query = DB::table("blog_posts")->select("ID")->getQuery();
		$this->assertEquals($query, "SELECT ID FROM blog_posts");
		
		// with multiple fields
		$query = DB::table("blog_posts")->select("ID", "title")->getQuery();
		$this->assertEquals($query, "SELECT ID,title FROM blog_posts");
		
		// with limit
		$query = DB::table("blog_posts")->select()->limit(10)->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts LIMIT 10");
		
		// with offset
		$query = DB::table("blog_posts")->select()->offset(50)->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts OFFSET 50");
		
		// with offset and limit
		$query = DB::table("blog_posts")->select()->limit(10)->offset(50)->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts LIMIT 10 OFFSET 50");
		
		// order by
		$query = DB::table("blog_posts")->select()->orderBy("title ASC")->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts ORDER BY title ASC");
		
		// select count(*)
		$query = DB::table("blog_posts")->count()->getQuery();
		$this->assertEquals($query, "SELECT COUNT(*) FROM blog_posts");
	}
	
	// Note that these cover where clause construction tests for all methods
	public function testBehavior_selectWhere()
	{
		// simple key, value
		$query = DB::table("blog_posts")
		->select()
		->where("ID", "10")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE ID = '10'");
		
		// multiple key, value pairs
		$query = DB::table("blog_posts")
		->select()
		->where("ID", "10")
		->where("type", "News")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE ID = '10' AND type = 'News'");
		
		// LIKE compare
		$query = DB::table("blog_posts")
		->select()
		->where("author", "LIKE", "John")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author LIKE 'John'");
		
		// multiple LIKE compares
		$query = DB::table("blog_posts")
		->select()
		->where("author", "LIKE", "John")
		->where("type", "LIKE", "News")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author LIKE 'John' AND type LIKE 'News'");
		
		// multiple key, value pairs, OR comparison
		$query = DB::table("blog_posts")
		->select()
		->where("ID", 10)
		->orWhere("type", "News")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE ID = '10' OR type = 'News'");
		
		// multiple LIKE OR comparisons
		$query = DB::table("blog_posts")
		->select()
		->where("author", "LIKE", "John")
		->orWhere("type", "LIKE", "News")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author LIKE 'John' OR type LIKE 'News'");
		
		// multiple OR comparisons on the same field
		$query = DB::table("blog_posts")
		->select()
		->where("type", "News")
		->orWhere("type", "Gossip")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type = 'News' OR type = 'Gossip'");
	}
	
	public function testBehavior_selectWhereIn()
	{
		// IN statement
		$query = DB::table("blog_posts")
		->select()
		->whereIn("type", array("News", "Gossip"))
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IN ('News','Gossip')");
		
		// NOT IN statement
		$query = DB::table("blog_posts")
		->select()
		->whereNotIn("type", array("News", "Gossip"))
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type NOT IN ('News','Gossip')");
		
		// AND IN statement
		$query = DB::table("blog_posts")
		->select()
		->where("author", "John")
		->whereIn("type", array("News", "Gossip"))
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author = 'John' AND type IN ('News','Gossip')");
		
		// OR IN statement
		$query = DB::table("blog_posts")
		->select()
		->where("author", "John")
		->orWhereIn("type", array("News", "Gossip"))
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author = 'John' OR type IN ('News','Gossip')");
		
		// AND NOT IN statement
		$query = DB::table("blog_posts")
		->select()
		->where("author", "John")
		->whereNotIn("type", array("News", "Gossip"))
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author = 'John' AND type NOT IN ('News','Gossip')");
		
		// OR NOT IN statement
		$query = DB::table("blog_posts")
		->select()
		->where("author", "John")
		->orWhereNotIn("type", array("News", "Gossip"))
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE author = 'John' OR type NOT IN ('News','Gossip')");
	}
	
	public function testBehavior_whereNull()
	{
		$query = DB::table("blog_posts")
		->select()
		->whereNull("type")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IS NULL");
		
		$query = DB::table("blog_posts")
		->select()
		->whereNotNull("type")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IS NOT NULL");
		
		$query = DB::table("blog_posts")
		->select()
		->whereNull("type")
		->whereNull("name")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IS NULL AND name IS NULL");
		
		$query = DB::table("blog_posts")
		->select()
		->whereNotNull("type")
		->whereNotNull("name")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IS NOT NULL AND name IS NOT NULL");
		
		$query = DB::table("blog_posts")
		->select()
		->whereNull("type")
		->orWhereNull("name")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IS NULL OR name IS NULL");
		
		$query = DB::table("blog_posts")
		->select()
		->whereNotNull("type")
		->orWhereNotNull("name")
		->getQuery();
		$this->assertEquals($query, "SELECT * FROM blog_posts WHERE type IS NOT NULL OR name IS NOT NULL");
	}
	
	public function testBehavior_update()
	{
		$query = DB::table("blog_posts")
		->update(array("type" => "news"))
		->getQuery();
		$this->assertEquals($query, "UPDATE blog_posts SET type = 'news'");
		
		$query = DB::table("blog_posts")
		->update(array("type" => "news", "title" => "Title"))
		->getQuery();
		$this->assertEquals($query, "UPDATE blog_posts SET type = 'news', title = 'Title'");
		
		$query = DB::table("blog_posts")
		->update(array("type" => "news", "title" => "Title"))
		->where("ID", 10)
		->getQuery();
		$this->assertEquals($query, "UPDATE blog_posts SET type = 'news', title = 'Title' WHERE ID = '10'");
	}
	
	public function testBehavior_insert()
	{
		$query = DB::table("blog_posts")
		->insert(array("type" => "news"))
		->getQuery();
		$this->assertEquals($query, "INSERT INTO blog_posts (type) VALUES ('news')");
		
		$query = DB::table("blog_posts")
		->insert(array("type" => "news", "title" => "Title"))
		->getQuery();
		$this->assertEquals($query, "INSERT INTO blog_posts (type, title) VALUES ('news', 'Title')");
		
		// escape string
		$query = DB::table("posts")
		->insert(array("first" => "John's", "last" => "Doe"))
		->getQuery();
		$this->assertEquals($query, "INSERT INTO posts (first, last) VALUES ('John\'s', 'Doe')");
	}
	
	public function testBehavior_delete()
	{
		$query = DB::table("blog_posts")->delete()->getQuery();
		$this->assertEquals($query, "DELETE FROM blog_posts");
		
		$query = DB::table("blog_posts")
		->delete()
		->where("ID", 10)
		->getQuery();
		$this->assertEquals($query, "DELETE FROM blog_posts WHERE ID = '10'");
	}
}
?>
<?php
$this->extend("layout");

Backbone::uses("DB");
use Backbone\DB;
?>

<?php $this->define("content"); ?>
<?php
DB::connect("mysql:dbname=blog;host=127.0.0.1", "root", "");
$rows = DB::table("posts")
->insert(array("first" => "John's", "last" => "Doe"))
->getQuery();
if(is_array($rows)) {
	foreach($rows as $i => $row) {
		echo "[".$i."]  ";
		foreach($row as $key => $val) {
			echo $key.":".$val."  ";
		}
		echo "<br/>";
	}
} else {
	print_r($rows);
}
?>
<?php $this->end(); ?>

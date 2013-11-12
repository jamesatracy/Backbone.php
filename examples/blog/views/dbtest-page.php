<?php
$this->extend("layout");

Backbone::uses("/models/Post");
?>

<?php $this->define("content"); ?>
<?php
$posts = Post::fetch()->exec();
echo "Total: ".$posts->length;
echo "<br/><br/>";
while($posts->valid()) {
	$cur = $posts->next();
	echo $cur->post_title." by ".$cur->post_author;
	echo "<br/><br/>";
}
?>
<?php $this->end(); ?>

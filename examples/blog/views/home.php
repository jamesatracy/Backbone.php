<?php
$this->extend("master-template");
?>

<?php $this->define("content"); 
$posts = $this->get("posts");

if($posts->length == 0): ?>
	<p>No Posts</p>
<?php else: 
	// loop over posts
	while($posts->items()):
	?>
	<?php $post = $posts->current(); ?>
	<h2 class="post_title"><?php echo $post->post_title; ?></h2>
	<div class="post_author">By: <?php echo $post->post_author; ?></div>
	<br/>
	<article class="post_body">
		<pre><?php echo $post->post_body; ?></pre>
	</article>
	<hr/>
	<?php $posts->next(); ?>
	<?php endwhile; ?>
<?php endif; ?>
<?php $this->end(); ?>

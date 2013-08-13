<?php
$this->extend("master-template");

// Get the list of posts to display
// Notice that this information is passed from the Router
// and not computed withiin this view.
$posts = $this->get("posts");
?>

<?php $this->define("content"); ?>
<?php if($posts->length == 0): ?>
	<p>No Posts</p>
<?php else: 
	// loop over posts
	while($posts->items()):
	?>
	<?php $post = $posts->current(); ?>
	<h2 class="post_title"><?php echo $post->post_title; ?></h2>
	<div class="post_author">By: <?php echo $post->post_author; ?> (<span class="post_created"><?php echo date('m/d/Y h:m:s', strtotime($post->post_created)); ?></span>)</div>
	<br/>
	<article class="post_body">
		<pre><?php echo $post->post_body; ?></pre>
	</article>
	<hr/>
	<?php $posts->next(); ?>
	<?php endwhile; ?>
<?php endif; ?>
<?php $this->end(); ?>
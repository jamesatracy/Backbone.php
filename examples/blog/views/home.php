<?php
$this->extend("layout");

// Get the list of posts to display
// Notice that this information is passed from the Router and not computed within this view.
$posts = $this->get("posts");
?>

<?php $this->define("content"); ?>
<?php if($posts->length == 0): ?>
	<p>No Posts</p>
<?php else: 
	// loop over posts
	while($posts->valid()):
	?>
	<?php $post = $posts->next(); ?>
	<h2 class="post_title"><?php echo $post->post_title; ?></h2>
	<div class="post_author">By: <?php echo $post->post_author; ?> (<span class="post_created"><?php echo date('m/d/Y h:m:s', strtotime($post->post_created)); ?></span>)</div>
	<br/>
	<article class="post_body">
		<pre><?php echo $post->post_body; ?></pre>
	</article>
	<hr/>
	<?php endwhile; ?>
<?php endif; ?>
<?php $this->end(); ?>

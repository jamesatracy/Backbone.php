<?php
$this->extend("layout");
?>

<?php $this->define("content"); ?>
<form id="create_post" method="post" action="">
	<fieldset>
		<strong>Title:</strong>
		<br/>
		<input type="text" id="post_title" name="post_title" value="<?php echo Backbone::$request->post("post_title"); ?>" style="width:100%" />
		<strong>Author:</strong>
		<br/>
		<input type="text" id="post_author" name="post_author" value="<?php echo Backbone::$request->post("post_author"); ?>" style="width:100%" />
		<strong>Post:</strong>
		<br/>
		<textarea id="post_body" name="post_body" rows="20" style="width:100%"><?php echo Backbone::$request->post("post_body"); ?></textarea>
		<br/>
		<?php if($this->get("errors")): ?>
			<div style="color:red;font-weight:bold"><?php echo $this->get("errors"); ?></div>
		<?php endif; ?>
		<input type="submit" id="cancel" name="cancel" value="Cancel" style="float:right" />
		<input type="submit" id="submit" name="submit" value="Submit" style="float:right" />
	</fieldset>
</form>
<?php $this->end(); ?>
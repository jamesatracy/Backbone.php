<!DOCTYPE html>
<html>
<head>
	<title><?php echo $this->get("title"); ?></title>
</head>
<body>
<h1>Blog Example</h1>
<?php $this->display("_navigation"); ?>
<section id="content">
	<?php $this->render("content"); ?>
</section>
</body>
</html>

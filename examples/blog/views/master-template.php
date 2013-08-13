<!DOCTYPE html>
<html>
<head>
	<title><?php echo $this->get("title"); ?></title>
</head>
<body>
<h1>Blog Example</h1>
<table width="980px">
	<tr>
		<td align="left" valign="top">
			<section id="content">
				<?php $this->render("content"); ?>
			</section>
		</td>
		<td width="300px" align="left" valign="top" style="padding-left: 10px">
			<button id="create_post" onclick="window.location = '<?php echo Backbone::$request->link("/create/"); ?>'">Create Post</button>
		</td>
	</tr>
</table>
</body>
</html>

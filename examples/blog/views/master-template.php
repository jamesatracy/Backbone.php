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
			<div id="content">
				<?php $this->render("content"); ?>
			</div>
		</td>
		<td width="300px" align="left" valign="top" style="padding-left: 10px">
			<input type="button" id="create_post" name="create_post" onclick="window.location = '<?php echo Backbone::$request->link("/create/"); ?>'" value="Create Post" />
		</td>
	</tr>
</table>
</body>
</html>
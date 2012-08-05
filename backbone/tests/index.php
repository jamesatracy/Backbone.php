<?php
/* Test Driver */

// load the framework
require("load.php");

Backbone::uses("BackboneTest");

BackboneTest::load("DataTypeTest");
BackboneTest::load("DataSetTest");
?>
<h2>Backbone.php Unit Tests</h2>
<?php if(Backbone::$request->get("id") == "all"): // run all tests?>
	<div><strong><a href="<?php echo Backbone::$request->link(""); ?>">Back to list</a></strong></div>
	<br/>
	<?php BackboneTest::run(); ?>
<?php elseif(Backbone::$request->get("id") >= 0 && Backbone::$request->get("name")): // run one test suite ?>
	<div><strong><a href="<?php echo Backbone::$request->link(""); ?>">Back to list</a></strong></div>
	<br/>
	<?php BackboneTest::run(Backbone::$request->get("name"), Backbone::$request->get("id")); ?>
<?php else: // enumerate tests ?>
	<div><strong><a href="<?php echo Backbone::$request->link("?id=all"); ?>">Run All</a></strong></div>
	<br/>
	<?php
	$tests = BackboneTest::enumerate();
	foreach($tests as $test)
	{
		echo '<div><a href="'.Backbone::$request->link("?name=".$test['classname']."&id=".$test['id']).'">'.$test['name'].' ('.$test['count'].')</a></div>';
	}
	?>
<?php endif; ?>
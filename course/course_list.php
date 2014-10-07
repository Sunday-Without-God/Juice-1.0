<?php
	if (!isset($prefix)) {
		$prefix = '../';
	}
	require_once $prefix.'config/web_preprocess.php';
	
	if (!permission_check('login')) {
		header("Location: ".$prefix."index.php");
		exit();
	}
	
	$lesson = new lesson('mysql', DATABASE_MYSQL_HOST, DATABASE_MYSQL_DBNAME, DATABASE_MYSQL_USERNAME, DATABASE_MYSQL_PASSWORD);
	$result = $lesson->list_lesson();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>課程列表</title>
		<!--<link rel="icon" href="" type="image/x-icon">-->
<?php display_css_link($prefix); ?>
<?php display_scripts_link(); ?>
	</head>
	<body>
<?php display_navigation($prefix); ?>
		<div id="main">
			<div class="pure-g">
				<div class="pure-u-1-1">
					<div class="lesson_list">
						<div>
							<h1 class="title t-center">課程列表</h1>
						</div>
						<div class="course_option_table">
<?php
	if (!empty($result)) {
		$lesson_level_name = array('初階', '中階', '高階', '終階');
		foreach ($result as $tmp) {
?>
							<div class="course_option" onClick="redirect(<?php echo $tmp['lesson_unit']; ?>);">
										<p>單　　元　　<?php echo $tmp['lesson_unit']; ?></p>
										<p><?php echo $lesson_level_name[$tmp['lesson_level']-1]; ?></p>
										<p><?php echo $tmp['lesson_title']; ?></p>
							</div>
<?php
		}
	}
?>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php display_footer($prefix); ?>
		<script>
			function redirect(course_id) {
				window.location.href = 'http://crux.coder.tw/freedom/juice/course/course.php?unit=' + course_id;
			}
		</script>
	</body>
</html>
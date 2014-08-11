<?php
	if (!isset($prefix)) {
		$prefix = '../../';
	}
	require_once $prefix.'config/web_preprocess.php';
	
	$_SESSION['uid'] = 1;
	
	if (!isset($_COOKIE['verify_code_add_lesson'])) {
		setcookie("verify_code_add_lesson", verify_code(), $current_time + 3600, "/", WEB_DOMAIN_NAME);
	}
	
	if (isset($_GET['key'])) {
		$lesson = new lesson('mysql', DATABASE_MYSQL_HOST, DATABASE_MYSQL_DBNAME, DATABASE_MYSQL_USERNAME, DATABASE_MYSQL_PASSWORD);
		$lesson_content = $lesson->get_lesson_content($_GET['key']);
		if (empty($lesson_content)) {
			$lesson_content = false;
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo ($lesson_content) ? '修改課程' : '新增課程'; ?></title>
		<link rel="icon" href="" type="image/x-icon">
		<link type="text/css" href="<?php echo $prefix.'scripts/css/pure.css' ?>" rel="stylesheet">
		<link type="text/css" href="<?php echo $prefix.'scripts/css/lesson_add.css' ?>" rel="stylesheet">
<?php display_scripts_link(); ?>
		<script src="http://cdn.ckeditor.com/4.4.3/standard-all/ckeditor.js"></script>
	</head>
	<body>
<?php display_navigation($prefix); ?>
		<div class="juice-lesson-body">
			<div>
				<h3 id="message"></h3>
			</div>
			<div>
				<form name="lesson_refine" id="lesson_refine" action="<?php echo $prefix.'juice/lesson/lesson_handle.php' ?>" method="POST" onSubmit="return false;">
					<fieldset>
						<div>
							<div class="juice-lesson-titles">
								<label for="unit">單元：</label>
								<input type="text" id="unit" name="unit" value="<?php echo ($lesson_content) ? $lesson_content['lesson_unit'] : ''; ?>" maxlength="2" pattern="^\d{1,2}$" autocomplete="off" required>
							</div>
							<div class="juice-lesson-titles">
								<label for="level">難度：</label>
								<select name="level" id="level" required>
									<option value="1"<?php echo ($lesson_content['lesson_level'] == 1) ? 'selected' : ''; ?>>初階</option>
									<option value="2"<?php echo ($lesson_content['lesson_level'] == 2) ? 'selected' : ''; ?>>中階</option>
									<option value="3"<?php echo ($lesson_content['lesson_level'] == 3) ? 'selected' : ''; ?>>高階</option>
									<option value="4"<?php echo ($lesson_content['lesson_level'] == 4) ? 'selected' : ''; ?>>終階</option>
								</select>
							</div>
							<div class="juice-lesson-titles">
								<label for="title">標題：</label>
								<input type="text" id="title" name="title" value="<?php echo ($lesson_content) ? $lesson_content['lesson_title'] : ''; ?>" maxlength="128" autocomplete="off" required>
							</div>
						</div>
						<br>
						<div>
							<div class="juice-lesson-contents">
								<label for="goal">學習目標：</label>
								<textarea class="ckeditor" name="goal" id="goal" required></textarea>
							</div>
							<br>
							<hr>
							<br>
							<div class="juice-lesson-contents">
								<label for="content">課程內容：</label>
								<textarea class="ckeditor" name="content" id="content" required></textarea>
							</div>
							<br>
							<hr>
							<br>
							<div class="juice-lesson-contents">
								<label for="example">範　　例：</label>
								<textarea class="ckeditor" name="example" id="example" required></textarea>
							</div>
							<br>
							<hr>
							<br>
							<div class="juice-lesson-contents">
								<label for="practice">填空練習：</label>
								<textarea class="ckeditor" name="practice" id="practice" required></textarea>
							</div>
							<br>
							<hr>
							<br>
							<div class="juice-lesson-contents">
								<label for="implement">動 動 腦：</label>
								<textarea class="ckeditor" name="implement" id="implement" required></textarea>
							</div>
							<div>
								<input type="text" name="verify_code" id="verify_code" value="<?php echo $_COOKIE['verify_code_add_lesson']; ?>" hidden readonly autocomplete="off" required>
								<input type="text" name="key" id="key" value="<?php echo ($lesson_content) ? $_GET['key'] : ''; ?>" hidden readonly autocomplete="off">
							</div>
						</div>
						<br>
						<button class="juice-lesson-button" type="submit" name="submit" id="submit"><?php echo (isset($_GET['key'])) ? '修改' : '新增'; ?></button>
					</fieldset>
				</form>
			</div>
		</div>
		<div>
			<footer>
				Web Create by : Juice / Copyright © 2014
			</footer> 
		</div>
		<script>
			$(document).ready(function() {
				var auto_update_next = 0;
				
				function auto_update() {
					$.post(
						'<?php echo $prefix.'juice/lesson/lesson_handle.php' ?>',
						{
							unit:$('#unit').val(),
							level:$('#level').val(),
							title:$('#title').val(),
							goal:CKEDITOR.instances.goal.getData(),
							content:CKEDITOR.instances.content.getData(),
							example:CKEDITOR.instances.example.getData(),
							practice:CKEDITOR.instances.practice.getData(),
							implement:CKEDITOR.instances.implement.getData(),
							verify_code:$('#verify_code').val(),
							key:$('#key').val()
						},
						function (data) {
							var d = new Date();
							if (typeof data.error != 'undefined') {
								$('#message').text(data.error);
								$('html,body').animate({
									scrollTop:0
								});
							} else if (typeof data.updated != 'undefined') {
								$('#message').text('系統已自動存檔 - ' + d);
							} else if (typeof data.key != 'undefined') {
								$('#message').text('課程已新增 - ' + d);
								$('#submit').text('修改');
								$('#key').val(data.key);
								$("#unit").attr("readonly",true);
								$('html,body').animate({
									scrollTop:0
								});
							} else {
								$('#message').text('未知的錯誤 - ' + d);
								$('html,body').animate({
									scrollTop:0
								});
							}
						}, 'json'
					);
					auto_update_next = setTimeout(auto_update, 300000);
				}
				
				$("#lesson_refine").submit(function(){
					if (auto_update_next) {
						clearTimeout(auto_update_next);
						auto_update_next = 0;
					}
					auto_update();
					return false;
				});
				
<?php
	if ($lesson_content) {
?>
				CKEDITOR.instances.goal.setData(<?php echo $lesson_content['lesson_goal']; ?>);
				CKEDITOR.instances.content.setData(<?php echo $lesson_content['lesson_content']; ?>);
				CKEDITOR.instances.example.setData(<?php echo $lesson_content['lesson_example']; ?>);
				CKEDITOR.instances.practice.setData(<?php echo $lesson_content['lesson_practice']; ?>);
				CKEDITOR.instances.implement.setData(<?php echo $lesson_content['lesson_implement']; ?>);
				$("#unit").attr("readonly",true);
				auto_update();
<?php
	}
?>
			});
		</script>
	</body>
</html>
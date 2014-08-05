<?php
	class lesson extends db_connect {
		
		public function __construct($db_type, $db_host, $db_name, $db_username, $db_password) {
			parent::__construct($db_type, $db_host, $db_name, $db_username, $db_password);
		}
		
		public function __destruct() {
			parent::__destruct();
		}
		
		public function list_lesson(array $filter = array()) {
			$sql = "SELECT `lesson_key`, `lesson_unit`, `lesson_level`, `lesson_title`, `lesson_is_visible` FROM `lesson` WHERE `lesson_is_delete` = :lesson_is_delete";
			$params = array(
				':lesson_is_delete' => false
			);
			if (!empty($filter)) {
				if (isset($filter['level'])) {
					$sql .= " AND `lesson_level` = :lesson_level";
					$params[':lesson_level'] = $filter['level'];
				}
			}
			$this->query($sql, $params);
			return $this->fetchAll();
		}
		
		public function show_lesson_content($key) {
			$sql = "SELECT `lesson_unit`, `lesson_level`, `lesson_title`, `lesson_goal`, `lesson_content`, `lesson_example`, `lesson_practice`, `lesson_implement`, `lesson_is_visible` FROM `lesson` WHERE `lesson_key` = :lesson_key AND `lesson_is_delete` = :lesson_is_delete";
			$params = array(
				':lesson_key' => $key,
				':lesson_is_delete' => false
			);
			$this->query($sql, $params);
			return $this->fetch();
		}
		
		public function add_lesson($unit, $level, $title, $goal, $content, $example, $practice, $implement) {
			$key = hash_key('sha1');
			$title = htmlspecialchars($title, ENT_QUOTES);
			$goal = htmlspecialchars($goal, ENT_QUOTES);
			$content = htmlspecialchars($content, ENT_QUOTES);
			$example = htmlspecialchars($example, ENT_QUOTES);
			$practice = htmlspecialchars($practice, ENT_QUOTES);
			$implement = htmlspecialchars($implement, ENT_QUOTES);
			if (!preg_match("/^\d{1,2}$/", $unit) and $unit > 0) {
				$result = array(
					'error' => 'Invalid unit.'
				);
			} else if (!preg_match("/^[0-3]{1}$/", $level)) {
				$result = array(
					'error' => 'Invalid level.'
				);
			} else if (($length = mb_strlen($title, 'UTF-8')) == 0 or $length > 128) {
				$result = array(
					'error' => 'Invalid title.'
				);
			} else {
				$sql = "SELECT `id` FROM `lesson` WHERE `lesson_unit` = :lesson_unit";
				$params = array(
					':lesson_unit' => $unit
				);
				$this->query($sql, $params);
				if ($this->rowCount() >= 1) {
					$result = array(
						'error' => 'The unit is already exists.'
					);
				} else {
					$this->closeCursor();
					$sql = "INSERT INTO `lesson` (`lesson_key`, `lesson_unit`, `lesson_level`, `lesson_title`, `lesson_goal`, `lesson_content`, `lesson_example`, `lesson_practice`, `lesson_implement`, `lesson_create_user`, `lesson_create_time`) ";
					$sql .= "VALUES (:lesson_key, :lesson_unit, :lesson_level, :lesson_title, :lesson_goal, :lesson_content, :lesson_example, :lesson_practice, :lesson_implement, :lesson_create_user, :lesson_create_time)";
					$params = array(
						':lesson_key' => $key,
						':lesson_unit' => $unit,
						':lesson_level' => $level,
						':lesson_title' => $title,
						':lesson_goal' => $goal,
						':lesson_content' => $content,
						':lesson_example' => $example,
						':lesson_practice' => $practice,
						':lesson_implement' => $implement,
						':lesson_create_user' => $_SESSION['uid'],
						':lesson_create_time' => $this->current_time
					);
					$this->query($sql, $params);
					if ($this->rowCount() != 1) {
						$result = array(
							'error' => 'There is something wrong when updating the data.'
						);
					} else {
						$result = array(
							'key' => $key
						);
					}
				}
				$this->closeCursor();
			}
			return json_encode($result);
		}
		
		public function update_lesson($key, $level, $title, $goal, $content, $example, $practice, $implement) {
			$title = htmlspecialchars($title, ENT_QUOTES);
			$goal = htmlspecialchars($goal, ENT_QUOTES);
			$content = htmlspecialchars($content, ENT_QUOTES);
			$example = htmlspecialchars($example, ENT_QUOTES);
			$practice = htmlspecialchars($practice, ENT_QUOTES);
			$implement = htmlspecialchars($implement, ENT_QUOTES);
			if (!preg_match("/^[0-3]{1}$/", $level)) {
				$result = array(
					'error' => 'Invalid level.'
				);
			} else if (($length = mb_strlen($title, 'UTF-8')) == 0 or $length > 128) {
				$result = array(
					'error' => 'Invalid title.'
				);
			} else {
				$sql = "SELECT `id` FROM `lesson` WHERE `lesson_key` = :lesson_key";
				$params = array(
					':lesson_key' => $key
				);
				$this->query($sql, $params);
				if ($this->rowCount() == 0) {
					$result = array(
						'error' => 'The unit is not exists.'
					);
				} else {
					$this->closeCursor();
					$sql = "UPDATE `lesson` SET `lesson_level` = :lesson_level, `lesson_title` = :lesson_title, `lesson_goal` = :lesson_goal, `lesson_content` = :lesson_content, `lesson_example` = :lesson_example, `lesson_practice` = :lesson_practice, ";
					$sql .= "`lesson_implement` = :lesson_implement, `lesson_last_update_user` = :lesson_last_update_user, `lesson_last_update_time` = :lesson_last_update_time WHERE `lesson_key` = :lesson_key";
					$params = array(
						':lesson_level' => $level,
						':lesson_title' => $title,
						':lesson_goal' => $goal,
						':lesson_content' => $content,
						':lesson_example' => $example,
						':lesson_practice' => $practice,
						':lesson_implement' => $implement,
						':lesson_last_update_user' => $_SESSION['uid'],
						':lesson_last_update_time' => $this->current_time,
						':lesson_key' => $key
					);
					$this->query($sql, $params);
					if ($this->rowCount() != 1) {
						$result = array(
							'error' => 'There is something wrong when updating the data.'
						);
					} else {
						$result = array(
							'result' => true
						);
					}
				}
				$this->closeCursor();
			}
			return json_encode($result);
		}
		
		public function change_lesson_visible($key, $type) {
			if (!preg_match("/^[0-1]{1}$/", $type)) {
				return 'Invalid type.';
			} else {
				$type = ($type) ? true : false;
				$sql = "UPDATE `lesson` SET `lesson_is_visible` = :lesson_is_visible WHERE `lesson_key` = :lesson_key";
				$params = array(
					':lesson_is_visible' => $type,
					':lesson_key' => $key
				);
				$this->query($sql, $params);
				if ($this->rowCount() != 1) {
					$this->closeCursor();
					return 'There is something wrong when updating the data.';
				} else {
					$this->closeCursor();
					return true;
				}
			}
		}
		
		public function delete_lesson($key) {
			$sql = "UPDATE `lesson` SET `lesson_is_delete` = :lesson_is_delete WHERE `lesson_key` = :lesson_key";
			$params = array(
				':lesson_is_delete' => true,
				':lesson_key' => $key
			);
			$this->query($sql, $params);
			if ($this->rowCount() != 1) {
				$this->closeCursor();
				return 'There is something wrong when updating the data.';
			} else {
				$this->closeCursor();
				return true;
			}
		}
		
		public function show_image($key, $image_id) {
			$lesson_id = $this->get_lesson_id($key);
			if (isset($lesson_id['error'])) {
				$result = array(
					'error' => 'The unit is not exists.'
				);
			} else {
				$sql = "SELECT `image_type`, `image_width`, `image_height`, `image_data` FROM `lesson_image` WHERE `id` = :image_id AND `lesson_id` = :lesson_id AND `image_is_delete` = :image_is_delete";
				$params = array(
					':image_id' => $image_id,
					':lesson_id' => $lesson_id['id'],
					':image_is_delete' => false
				);
				$this->query($sql, $params);
				return $this->fetch();
			}
		}
		
		public function list_lesson_image($key) {
			$lesson_id = $this->get_lesson_id($key);
			if (isset($lesson_id['error'])) {
				$result = array(
					'error' => 'The unit is not exists.'
				);
			} else {
				$sql = "SELECT `id` FROM `lesson_image` WHERE `lesson_id` = :lesson_id AND `image_is_used` = :image_is_used AND `image_is_delete` = :image_is_delete";
				$params = array(
					':lesson_id' => $lesson_id['id'],
					':image_is_used' => true,
					':image_is_delete' => false
				);
				$this->query($sql, $params);
				return $this->fetchAll();
			}
		}
		
		public function list_unused_image($key) {
			$lesson_id = $this->get_lesson_id($key);
			if (isset($lesson_id['error'])) {
				$result = array(
					'error' => 'The unit is not exists.'
				);
			} else {
				$sql = "SELECT `id` FROM `lesson_image` WHERE `lesson_id` = :lesson_id AND `image_is_used` = :image_is_used AND `image_is_delete` = :image_is_delete";
				$params = array(
					':lesson_id' => $lesson_id['id'],
					':image_is_used' => false,
					':image_is_delete' => false
				);
				$this->query($sql, $params);
				return $this->fetchAll();
			}
		}
		
		public function add_image($key, $image) {
			if (($image_data = getimagesize($image["tmp_name"])) === false) {
				$result = array(
					'error' => 'Please check the image that you have uploaded.'
				);
			} else if ($image['size'] == 0 or $image['size'] >= 16777215) {
				$result = array(
					'error' => 'Please check the image that you have uploaded.'
				);
			} else {
				$lesson_id = $this->get_lesson_id($key);
				if (isset($lesson_id['error'])) {
					$result = array(
						'error' => 'The unit is not exists.'
					);
				} else {
					$sql = "INSERT INTO `lesson_image` (`lesson_id`, `image_type`, `image_size`, `image_width`, `image_height`, `image_data`) VALUES (:lesson_id, :image_type, :image_size, :image_width, :image_height, :image_data)";
					$params = array(
						array(':lesson_id', $lesson_id['id'], 'PARAM_INT'),
						array(':image_type', $image_data['mime'], 'PARAM_STR'),
						array(':image_size', $image['size'], 'PARAM_INT'),
						array(':image_width', $image_data['0'], 'PARAM_INT'),
						array(':image_height', $image_data['1'], 'PARAM_INT'),
						array(':image_data', file_get_contents($image['tmp_name']), 'PARAM_LOB')
					);
					$this->prepare($sql);
					$this->bindParam($params);
					$this->execute();
					$insert_id = $this->lastInsertId();
					if ($this->rowCount() != 1) {
						$result = array(
							'error' => 'There is something wrong when updating the data.'
						);
					} else {
						$result = array(
							'id' => $insert_id
						);
					}
					$this->closeCursor();
				}
			}
			return json_encode($result);
		}
		
		public function delete_image($key, $image_id) {
			$lesson_id = $this->get_lesson_id($key);
			if (isset($lesson_id['error'])) {
				$result = array(
					'error' => 'The unit is not exists.'
				);
			} else {
				$sql = "UPDATE `lesson_image` SET `image_is_delete` = :image_is_delete WHERE `id` = :image_id AND `lesson_id` = :lesson_id";
				$params = array(
					':image_is_delete' => true,
					':image_id' => $image_id,
					':lesson_id' => $lesson_id['id']
				);
				$this->query($sql, $params);
				if ($this->rowCount() != 1) {
					$result = array(
						'error' => 'There is something wrong when updating the data.'
					);
				} else {
					$result = array(
						'id' => $lesson_id['id']
					);
				}
				$this->closeCursor();
			}
			return json_encode($result);
		}
		
		public function get_lesson_id($key) {
			$sql = "SELECT `id` FROM `lesson` WHERE `lesson_key` = :lesson_key";
			$params = array(
				':lesson_key' => $key
			);
			$this->query($sql, $params);
			if ($this->rowCount() != 1) {
				$result = array(
					'error' => 'The unit is not exists.'
				);
			} else {
				$lesson_id = $this->fetch();
				$this->closeCursor();
				$result = array(
					'id' => $lesson_id['id']
				);
			}
			return $result;
		}
	}
?>
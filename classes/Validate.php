<?php

class Validate {

	private $_passed = false,
	$_errors = array(),
	$_db = null,
	$_imagePassed = false;

	public function __construct() {
		$this->_db = DB::getInstance();
	}

	public function validateImage($source, $items = array(), $destination = false) {

		// If image or file
		if($source == $_FILES) {
			foreach ($items as $item => $rules) {
			$itemName = $rules['name'];
			foreach ($rules as $rule => $rule_value) {
				switch ($rule) {
					case 'image': 
						if (empty($_FILES['image']['name'])) {
					    	$this->addError("Please select an image to upload.");
					  	} else {
						  	if(empty($_POST) === false) {
						  		if ($destination) {
						  			$directory = $destination;
						  		} else {
							    	$directory = "public/images/profilePictures/";
							    }

							    $target_file = $directory . basename($_FILES["image"]["name"]);
							    $uploadOk = 1;
							    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
							    $new_name = round(microtime(true)) . @end($target_file) . "." . $imageFileType;
							    $new_image_name = $directory . basename($new_name);

							    $image = $new_name;

							    // Check if image file is a actual image or fake image
							    if(isset($_POST["submit"])) {
							      	$check = getimagesize($_FILES["image"]["tmp_name"]);
							      	if($check !== false) {
							        	$uploadOk = 1;
							      	} else {
							        	$this->addError("That file is not an image");
							        	$uploadOk = 0;
							      	}
							    }

							    // Check if file already exists
							    if (file_exists($new_image_name)) {
							      	$this->addError("Sorry, file already exists.");
							      	$uploadOk = 0;
							    } else {
							      	// Check file size
							      	if ($_FILES["image"]["size"] > 500000) {
							        	$this->addError("Sorry, your file is too large.");
							        	$uploadOk = 0;
							      	}
							      	// Allow certain file formats
							      	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
							      	&& $imageFileType != "gif" ) {
							        	$this->addError("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
							        	$uploadOk = 0;
							      	}
							      	// Check if $uploadOk is set to 0 by an error
							      	if ($uploadOk == 0) {
							        	$this->addError("Your image could not be uploaded.");
							      	} else {
							        	if (move_uploaded_file($_FILES["image"]["tmp_name"], $new_image_name)) {	
							        		if (empty($this->_errors)) {
												$this->_imagePassed = true;
											}
							        		return $new_image_name;
							        	} else {
							          		$this->addError("Could not upload your image");
							        	}
						      		}
						    	}
						  	}
					  	}
					break;
					
					default:
						# code...
						break;
					}
				}
			}
		}
		
		return false;

	}

	public function check($source, $items = array()) {

		foreach ($items as $item => $rules) {
			$itemName = $rules['name'];
			foreach ($rules as $rule => $rule_value) {
				// echo $item. ' - '.$rules .' - '. $rule .' - '. $rule_value.'<br>';
				$value = trim($source[$item]);
				$item = escape($item);

				if ($rule === 'required' && empty($value)) {
					$this->addError("{$itemName} is required");
				} else if(!empty($value)) {
					switch ($rule) {
						case 'min':
							if (strlen($value) < $rule_value) {
								$this->addError("{$itemName} must be a minimum of {$rule_value} characters.");
							}
							break;
						case 'max':
							if (strlen($value) > $rule_value) {
								$this->addError("{$itemName} must be a maximum of {$rule_value} characters.");
							}
							break;
						case 'matches':
							if ($value != $source[$rule_value]) {
								$this->addError("{$rule_value} must match {$itemName}");
							}
							break;
						case 'unique':
							$check = $this->_db
									->select($rule_value)
									->where(array($item, '=', $value))
									->get();
							if ($check->count()) {
								// Already exists
								$this->addError("{$itemName} is already in use.");
							}
							break;
						default:
							
							break;
					}
				}

			}
		}

		if (empty($this->_errors)) {
			$this->_passed = true;
		}

		return $this;
	}

	// Setter
	private function addError($error) {
		$this->_errors[] = $error;
	}

	// Getter
	public function errors() {
		return $this->_errors;
	}

	public function passed() {
		return $this->_passed;
	}

	public function imagePassed() {
		return $this->_imagePassed;
	}
}
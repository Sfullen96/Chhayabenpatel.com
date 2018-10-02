<?php

class User {
	private $_db,
			$_data,
			$_sessionName,
			$_isLoggedIn,
			$_cookieName;

	public function __construct($user = null) {
		$this->_db = DB::getInstance();

		$this->_sessionName = Config::get('session/session_name');

		$this->_cookieName = Config::get('remember/cookie_name');

		// Grab data of user who is logged in
		if (!$user) {
			if (Session::exists($this->_sessionName)) {
				$user = Session::get($this->_sessionName);

				if ($this->find($user)) {
					$this->_isLoggedIn = true;
				} else {
					// Process Logout
					$this->logout();
				}
			}
		} else { // Else get user data for user that was passed
			$this->find($user);
		}
	}

	public function update($fields = array(), $id = null) {

		if (!$id && $this->isLoggedIn()) {
			$id = $this->data()->id;
		}

		if (!$this->_db->update('users', 'id', $id, $fields)) {
			throw new Exception('There was a problem updating your account.');
		}
	}

	public function create($fields = array()) {
		if (!$this->_db->insert('users', $fields)) {
			throw new Exception('There was a problem creating your account');
		}
	}

	public function createAddress($fields = array()) {
		if (!$this->_db->insert('user_address', $fields)) {
			throw new Exception('There was a problem creating your account');
		}
	}

	public function find($user = null) {
		if ($user) {
			$field = (is_numeric($user)) ? 'id' : 'email';
			$data = $this->_db->select('users')
					->where(array($field, '=', $user))->get();

			if ($data->count()) {
				// Found user
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}

	public function login($email = null, $password = null, $remember = false) {
		
		
		if (!$email && !$password && $this->exists()) {
			 // Login
			Session::put($this->_sessionName, $this->data()->id);
		} else {
			$user = $this->find($email);

			if ($user) {
				if ($this->data()->password === Hash::make($password, $this->data()->salt)) {
					Session::put($this->_sessionName, $this->data()->id);

					if ($remember) {
						$hash = Hash::unique();
						$hashCheck = $this->_db->select('user_sessions')
								->where(array('user_id', '=', $this->data()->id))->get();

						if (!$hashCheck->count()) {
							$this->_db->insert('user_sessions', array(
								'user_id' => $this->data()->id,
								'hash' => $hash
							));
						} else {
							$hash = $hashCheck->first()->hash;
						}

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));

					}

					return true;
				}
			}
		}

		return false;
	}

	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}

	public function logout() {
		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);

		$this->_db->delete('user_sessions', array('user_id', '=', $this->data()->id));
	}

	// Getter
	public function data() {
		return $this->_data;
	}

	// getter
	public function isLoggedIn() {
		return $this->_isLoggedIn;
	}

	public function hasPermission($key) {
		$group = $this->_db
				->select('user_groups')
				->where(array('id', '=', $this->data()->user_group))
				->get();
		
		if ($group->count()) {
			$permissions = json_decode($group->first()->permissions, true);

			if ($permissions[$key] == true) {
				return true;
			}
		}
		return false;
	}
}
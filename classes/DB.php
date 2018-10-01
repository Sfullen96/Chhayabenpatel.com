<?php 

/*
* DB wrapper, uses singleton design pattern
* uses static method called get_instance which
* gets instace of DB if it's already been
* instantiated. Means we don't have to connect
* to DB on each page, much more efficient
*/

class DB {
	private static $_instance = null; // 
	private $_pdo, // Store PDO object here once instantiated
			$_query, // Last query executed
			$_error = false, // Whether query failed or not
			$_results, // Store results set of query
			$_count = 0, // Row count
			$_sql = '',
			$_params,
			$_paramCount; 

	// Run when class instantiated, used to connect to database
	// this method is private so other files can't make an object
	// of DB, this is to prevent the database being connected to
	// multiple times on a page to make things more efficient
	// this is what makes it a 'singleton' class, it's only ever
	// instantiated once
	private function __construct() {
		try {
			$this->_pdo = new PDO('mysql:host='.Config::get('mysql/host'). ';dbname='.Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
		} catch (PDOException $e) {
			die($e->getMessage());
		}

		$this->_sql = '';
		$this->_params = array();
		// $this->_paramCount = 0;
	}

	// Checks if db instantiated
	// SIDE NOTE: self works the same as $this
	// but $this is used to refer to current object
	// whereas self is used to reference current class
	// Use $this to refer to the current object. Use self to refer to the current class. In other words, use  $this->member for non-static members, use // self::$member for static members.
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			// If db not set, create database instance
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	// Build a query!
	public function select($table, $select = array()) {
		$this->_sql .= 'SELECT ';
		$x = 1;
		if (empty($select)) {
			$this->_sql .= '* ';
		} else {
			foreach ($select as $key => $value) {
				$this->_sql .= $value.' ';
				if ($x < count($select)) {
					$this->_sql .= ', ';
				}
				$x++;
			}
		}
		
		$this->_sql .= 'FROM '.$table;
		
		return $this;
	}

	public function where($where = array()) {

		if (count($where) === 3) { // SOMETHING - OPERATOR - SOMETHING e.g name = Sam
			$operators = array('=', '>', '<', '>=', '<=', 'LIKE');

			$field 		= $where[0];
			$operator 	= $where[1];
			$value 		= $where[2];

			//if($this->_paramCount > 0) {
				array_push($this->_params, $value);
			//} else {
				//$this->_params = array($value);
			//}
			
			

			// Check if allowed operator
			if (in_array($operator, $operators)) {
				if($this->_paramCount > 0) {
					$this->_sql .= " AND {$field} {$operator} ?";				
				} else {
					$this->_sql .= " WHERE {$field} {$operator} ?";				
				}
			}
		}
		$this->_paramCount++;
		return $this;
	}

	public function join($type, $table, $on = array()) {
		switch ($type) {
			case 'left':
				$action = ' LEFT JOIN';
				break;
			case 'inner':
				$action = ' INNER JOIN';
				break;
			default:
				# code...
				break;
		}
		$x = 1;
		$this->_sql.= $action.' '.$table.'  ON';

		foreach ($on as $key => $value) {
			$this->_sql.= ' '.$value;			
			$x++;
		}

		return $this;
	}

	public function orderBy($clause) {
		$this->_sql .= ' ORDER BY '.$clause;
		return $this;
	}

	public function limit($limit) {
		$this->_sql .= ' LIMIT '.$limit;
		return $this;
	}

	public function get() {
		return $this->query($this->_sql, $this->_params);
	}

	
	public function query($sql, $params = array()) {
		$this->_error = false; // incase we do multiple queries on one page, and one errors we need to reset the error
		$this->_params = array();
		$this->_paramCount = 0;

		// Check if query prepared correctly
		if ($this->_query = $this->_pdo->prepare($sql)) {
			// If any parameters passed, bind them
			$x = 1;
			// echo count($params);
			if (count($params)) {
				foreach ($params as $param) {
					$this->_query->bindValue($x, $param);
					$x++;
				}
			}

			// Check if query successfully executed
			if ($this->_query->execute()) {
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count = $this->_query->rowCount();
				$this->_sql = '';
			} else {
				$this->_sql = '';
				$this->_error = true;
			}
		}
		return $this; // return current object
	}

	public function action($action, $table, $where = array(), $orderBy = null, $limit = null, $join = null) { 	

		if (count($where) === 3) { // SOMETHING - OPERATOR - SOMETHING e.g name = Sam
			$operators = array('=', '>', '<', '>=', '<=', 'LIKE');

			$field 		= $where[0];
			$operator 	= $where[1];
			$value 		= $where[2];

			// Check if allowed operator
			if (in_array($operator, $operators)) {
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
				
				if (!empty($join)) {
					$sql.= ' '.$join;
				}

				if(!empty($orderBy)) {
					$sql.= " ORDER BY {$orderBy}";
				}

				if(!empty($limit)) {
					$sql.= " LIMIT {$limit}";
				}

				// Run the query;
				if (!$this->query($sql, array($value))->error) { // If query runs with no error
					return $this;
				}
			}
		} else {
			$sql = "{$action} FROM {$table}";
			
			if(!empty($orderBy)) {
				$sql.= " ORDER BY {$orderBy}";
			}

			if(!empty($limit)) {
				$sql.= " LIMIT {$limit}";
			}

			// Run the query;
			if (!$this->query($sql, array($value))->error) { // If query runs with no error
				return $this;
			}
		}

		return false;
	}
	
	public function rawQuery($sql) {
		if ($this->_query = $this->_pdo->prepare($sql)) {
			// If any parameters passed, bind them
			$x = 1;
			if (count($params)) {
				foreach ($params as $param) {
					$this->_query->bindValue($x, $param);
					$x++;
				}
			}

			// Check if query successfully executed
			if ($this->_query->execute()) {
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count = $this->_query->rowCount();
			} else {
				$this->_error = true;
			}
		}
		return $this; // return current object
	}

	public function insert($table, $fields = array()) {
		$keys = array_keys($fields);
		$values = null;
		$x = 1; // Counter	

		// For each field add a question mark to be binded
		foreach ($fields as $field) {
			$values .= "?";
			if ($x < count($fields)) {
				$values .= ', ';
			}
			$x++;
		}

		$sql = "INSERT INTO {$table} (`". implode('`, `', $keys) ."`) VALUES ({$values})";
		
		if (!$this->query($sql, $fields)->error()) {
			return true;
		}

		return false;
	}

	public function update($table, $where, $id, $fields) {
		$set = '';
		$x = 1; // Counter

		foreach ($fields as $name => $value) {
			$set .= "{$name} = ?";
			if ($x < count($fields)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE  {$table} SET {$set} WHERE {$where} = {$id}";
		
		if (!$this->query($sql, $fields)->error()) {
			return true;
		}

		return false;
	}

	public function delete($table, $where) {
		return $this->action('DELETE', $table, $where);
	}

	public function results() {
		return $this->_results;
	}

	public function first() {
		return $this->results()[0];
	}

	public function error() {
		return $this->_error;
	}

	public function count() {
		return $this->_count;
	}

	public function lastInsertId() {
		return $this->_pdo->lastInsertId();
	}
}
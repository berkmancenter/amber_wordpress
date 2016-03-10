<?php

/* Allow Amber PHP libraries to use the database connection provided
   by the platform on which they are running (e.g. Wordpress, Drupal)
 */
interface iAmberDB {
 
  public function db_type();
  public function select($sql, $options = array());
  public function selectAll($sql, $options = array());
  public function insert($sql, $options = array());
  public function update($sql, $options = array());
  public function delete($sql, $options = array());

}

Class AmberPDO implements iAmberDB {

	public function __construct(PDO $db) {
  		$this->db = $db;
	}

	private function convert_to_question_marks($sql) {
		$sql = str_replace('%s', '?', $sql);
		$sql = str_replace('%d', '?', $sql);
		$sql = str_replace('%f', '?', $sql);
		return $sql;  		
	}

	private function execute($sql, $options) {
	    $query = $this->db->prepare($this->convert_to_question_marks($sql));
		if (!$query) {
			error_log("Could not create query: $sql");
			return false;
		}
	    $query->execute($options);
	    return $query;
	}

	public function db_type() {
		return $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	public function select($sql, $options = array()) {
		$query = $this->execute($this->convert_to_question_marks($sql), $options);
		if (!$query) {
			error_log("Could not create query: $sql");
			return false;
		}
		$result = $query->fetch(PDO::FETCH_ASSOC);
		$query->closeCursor();
		return $result;
	}

	public function selectAll($sql, $options = array()) {
		$query = $this->execute($this->convert_to_question_marks($sql), $options);
		if (!$query) {
			error_log("Could not create query: $sql");
			return false;
		}
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		$query->closeCursor();
		return $result;
	}

	public function insert($sql, $options = array()) {
	    $query = $this->execute($this->convert_to_question_marks($sql), $options);
		if (!$query) {
			error_log("Could not create query: $sql");
			return false;
		}
	    $query->closeCursor();      
	}

	public function update($sql, $options = array()) {
		$query = $this->execute($this->convert_to_question_marks($sql), $options);
		if (!$query) {
			error_log("Could not create query: $sql");
			return false;
		}
    	$query->closeCursor();      
	}

	public function delete($sql, $options = array()) {
    	$query = $this->execute($this->convert_to_question_marks($sql), $options);
		if (!$query) {
			error_log("Could not create query: $sql");
			return false;
		}
	    $query->closeCursor();      
	}	
}

Class AmberWPDB implements iAmberDB {

	public function __construct(wpdb $db) {
  		$this->db = $db;
	}

  	public function db_type() {
    	return 'mysql';
  	}

  	private function prepare($sql, $options) {
  		if (empty($options)) {
  			return $sql;
  		} else {
  			return $this->db->prepare($sql, $options);
  		}
  	}

	public function select($sql, $options = array())
	{
		$query = $this->prepare($sql, $options);
		return $this->db->get_row($query, ARRAY_A);
	}

	public function selectAll($sql, $options = array())
	{
	    $query = $this->prepare($sql, $options);
	    return $this->db->get_results($query, ARRAY_A); 
	}

	public function insert($sql, $options = array())
	{
		$query = $this->prepare($sql, $options);
		$this->db->query($query,$options);
	}

	public function update($sql, $options = array())
	{
		$query = $this->prepare($sql, $options);
		$this->db->query($query,$options);
	}

	public function delete($sql, $options = array())
	{
		$query = $this->prepare($sql, $options);
		$this->db->query($query,$options);
	}
}

?>
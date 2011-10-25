<?php 
/**
 * Model class that represents a Selectable Person
 * 
 * @author dopey (mdobrinic@cozmanova.com)
 * for SURFnet (www.surfnet.nl)
 *
 */


class Person extends Selectable {
	
	private $_sContactIdentifier = NULL;
	
	/**
	 * Array of arrays of values
	 * @var array
	 */
	public $_aAttributes = array();
	
	public function __construct($sUID) {
		$this->_sContactIdentifier = $sUID;
	}
	
	
	public function getIdentifier() {
		return $this->_sContactIdentifier;
	}
	
	public function getAttributes() {
		return $this->_aAttributes;
	}

	
	public static function create($o) {
		// override to make it work in context-specific environments
		return new Person($o["uid"]);
	}

	public function __toString() {
		if (isset($this->_aAttributes["name"])) {
			return $this->_aAttributes["name"];
		}
		
		return $this->getIdentifier(); 
	}
	
}

?>
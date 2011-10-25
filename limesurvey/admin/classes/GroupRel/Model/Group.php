<?php 
/**
 * Model class that represents a Selectable Group
 * 
 * @author dopey (mdobrinic@cozmanova.com)
 * for SURFnet (www.surfnet.nl)
 *
 */

class Group extends Selectable {
	
	private $_sGroupIdentifier = NULL;
	
	public $_aContacts = array();
	
	/**
	 * Array of arrays of values
	 * @var array
	 */
	public $_aAttributes = array();
	
	
	public function __construct($sGroupIdentifier) {
		$this->_sGroupIdentifier = $sGroupIdentifier;
	}
	
	
	public function getIdentifier() {
		return $this->_sGroupIdentifier;
	}

	
	public function getContacts() {
		return $this->_aContacts;
	}
	
	
	public function __toString() {
		return $this->getIdentifier(); 
	}
	
}
?>
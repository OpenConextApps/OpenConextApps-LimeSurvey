<?php 
/**
 * Abstract Model class that represents a Selectable item
 * Relies on two-way mapping of an Identifier to and from a representation 
 *   that can be used as HTML id 
 * 
 * @author dopey (mdobrinic@cozmanova.com)
 * for SURFnet (www.surfnet.nl)
 *
 */


abstract class Selectable {
	
	/**
	 * One way encoding of the Identifier to be used in HTML-context<br />
	 * Is NOT associative, as in "un-htmlSafe( htmlSafe($id)) != $id" <br />
	 * Watch out though as only alfanumeric characters are identifying!
	 * @return string that can be used to refer to this group
	 */
	public function htmlSafeIdentifier() {
		if ($this->getIdentifier() == NULL) return "NULL";
		
		$sResult = preg_replace("/[^a-zA-Z0-9\@]/", "", $this->getIdentifier());
		
		return $sResult;
	}
	
	abstract public function getIdentifier();
	
	public function getInputName() {
		return "cb".$this->htmlSafeIdentifier();
	}
	
	
	/**
	 * Returns an array of cCoinSelectable-instances that were submitted
	 * @param $aSubmitData Data that was submitted ($_POST or $_GET, etc.)
	 * @return array of cCoinSelectable instances
	 */
	public static function selectedFromForm($aSubmitData, $aSelectables) {
		$aSelected = array();

		foreach ($aSelectables as $aSelectable) {
			if (array_key_exists($aSelectable->getInputName(), $aSubmitData)) {
				if ($aSubmitData[$aSelectable->getInputName()] == "on" ) {
					$aSelected[] = $aSelectable;
				}
			}
		}

		return $aSelected;
	}
}

?>
<?php
/**
 * Model class that represents a Message
 * 
 * @author dopey (mdobrinic@cozmanova.com)
 * for SURFnet (www.surfnet.nl)
 *
 */

class Message {
	
	public $_subject;
	public $_content;
	
	public $_sender;
	public $_recipients = array();

	public function addRecipient($recipient) {
		if (is_array($this->_recipients)) {
			$this->_recipients[] = $recipient;
		} else {
			$this->_recipients = array($recipient);
		}
	}

	public function __toString() {
		return <<<HERE
From: {$this->_sender}
To: {$this->_recipients[0]}
Subject: {$this->_subject}
{$this->_content}		
HERE
;
	}
	
}
?>
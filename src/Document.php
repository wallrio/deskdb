<?php 

namespace deskdb;

class Document{

	private $_id;

	public function setID($id){
		$this->_id = $id;
	}

	public function getID(){
		return $this->_id;
	}

}
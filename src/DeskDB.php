<?php 


namespace deskdb;

use deskdb\File; 
use deskdb\Strings; 
use deskdb\Document; 

class DeskDB{

	private $driver;
	
	private $parameters,
			$baseDir,
			$collectionDir,
			$posfixDocument = '_deskdb.json',
			$documents = [];

	public function getBaseDir(){
		return $this->baseDir;
	}

	public function getposfixDocument(){
		return $this->posfixDocument;
	}

	function __construct($driver){
		$this->driver = $driver;
	}

	
}
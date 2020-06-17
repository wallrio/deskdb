<?php 

namespace deskdb;

class Collection{
	
	private $collectionName,
			$collectionDir,
			$baseName;

	function __construct($collectionName,  $driver){
		$driver->setCollectionName($collectionName);
		$this->driver = $driver;		
	}

	public function __call($name, $arguments){
    	return $this->driver->{$name}($arguments);
    }


}
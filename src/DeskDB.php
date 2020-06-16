<?php 


namespace deskdb;

use deskdb\File; 
use deskdb\Strings; 
use deskdb\Document; 

class DeskDB{

	private $parameters,
			$baseName,
			$collectionDir,
			$posfixDocument = '_deskdb.json',
			$documents = [];


	function __construct(array $parameters){
		$this->parameters = $parameters;

		$base = isset($this->parameters['base'])?$this->parameters['base']:null;
		$collection = isset($this->parameters['collection'])?$this->parameters['collection']:null;

		if($base === null){

			$base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'deskdb'.DIRECTORY_SEPARATOR;

			$server = $_SERVER;
			unset($server['REMOTE_ADDR']);
			unset($server['SERVER_ADDR']);
			unset($server['REMOTE_PORT']);
			unset($server['REQUEST_TIME_FLOAT']);
			unset($server['REQUEST_TIME']);
			$server = json_encode($server);
			$idServer = md5($server);
			$base = $base.$idServer.DIRECTORY_SEPARATOR;

		}


		if(is_null($base)){
			die('Base not found');
		}

		if(is_null($collection)){
			die('Collection not found');
		}
		
		$collectionDir = $base.DIRECTORY_SEPARATOR.$collection.DIRECTORY_SEPARATOR;
		$collectionDir = preg_replace('#\/\/#m', '/', $collectionDir);
		$this->collectionDir = $collectionDir;
		$this->baseName = $base;

		$baseName = $this->baseName;
		$collectionDir = $this->collectionDir;

		if(!file_exists($baseName)) if(@mkdir($baseName,0777,true) === false) die('Permission denied on directory: '.$baseName);
		if(!file_exists($collectionDir)) if(@mkdir($collectionDir,0777,true) === false) die('Permission denied on directory: '.$collectionDir);
	}


	/**
	 * Create a document
	 */
	public function post($document){

		if( is_array($document)){
			foreach ($document as $key => $value) {			
				array_push($this->documents, $value);
			}
		}else{
			$this->documents[] = $document;
		}

		$this->execute();
	}


	/**
	 * list only first document
	 */
	public function getFirst($key = null,$value = null,$operator = '=='){
		$result = $this->get($key,$value,$operator);
		return reset($result);
	}


	/**
	 * List a or multiples documents
	 */
	public function get($key = null,$value = null,$operator = '=='){
		$collectionDir = $this->collectionDir;
		$dirArray = scandir($collectionDir);
		foreach ($dirArray as $keyDir => $valueDir) {
			if($valueDir === '.' || $valueDir === '..') unset($dirArray[$keyDir]);
		}
		$dirArray = array_values($dirArray);

		$listFound = [];
		
		foreach ($dirArray as $keyDir => $documentName) {
			$documentNameFull = $collectionDir.$documentName;
			
			$content = File::load($documentNameFull);
			$document = json_decode($content);

			$id = str_replace($this->posfixDocument, '', $documentName);

			$documentModel = new Document;
			foreach ($document as $keyModel => $valueModel) {
				if (property_exists($documentModel, $keyModel)){	
					$documentModel->setID($id);
					if ( !is_callable(array($documentModel, $keyModel)) ) continue;					
				}
				$documentModel->{$keyModel} = $valueModel;
			}

			

			if($key === null){
				$listFound[$id] = $documentModel;
				continue;
			}

			if(!isset($documentModel->{$key})) continue;

			if($operator === '==')				
				if( strtolower($documentModel->{$key})  == strtolower($value))
					$listFound[$id] = $documentModel;

			if($operator === '!=')				
				if( strtolower($documentModel->{$key})  != strtolower($value))
					$listFound[$id] = $documentModel;

			if($operator === '===')				
				if( ($documentModel->{$key})  === ($value)) $listFound[$id] = $documentModel;

			if($operator === '!==')				
				if( ($documentModel->{$key})  !== ($value)) $listFound[$id] = $documentModel;
			

			if($operator === 'like')
				if(soundex(strtolower($documentModel->{$key})) === soundex(strtolower($value)) )
					$listFound[$id] = $documentModel;

			if($operator === '!like')
				if(soundex(strtolower($documentModel->{$key})) !== soundex(strtolower($value)) )
					$listFound[$id] = $documentModel;

			if($operator === 'contain')
				if(strpos(strtolower($documentModel->{$key}),strtolower($value)) !== false) 
					$listFound[$id] = $documentModel;

			if($operator === '!contain')
				if(strpos(strtolower($documentModel->{$key}),strtolower($value)) !== false){}else{
					$listFound[$id] = $documentModel;
				} 
			
		}

		return $listFound;
	}




	/**
	 * Update a document
	 */
	public function put($documents){
		
		$found = false;
		$collectionDir = $this->collectionDir;

		if( is_array($documents)){
			foreach ($documents as $key => $value) {
				$id = $value->getID();

				$documentNameFull = $collectionDir.$key.$this->posfixDocument;			
				if(file_exists($documentNameFull)){
					$found = true;
					File::save($documentNameFull, json_encode($value));
				}
			}
		}else{
			$id = $documents->getID();							
			$documentNameFull = $collectionDir.$id.$this->posfixDocument;			
			if(file_exists($documentNameFull)){
				$found = true;
				File::save($documentNameFull, json_encode($documents));
			}
		}

		return $found;
	}



	/**
	 * Delete a document
	 */
	public function delete($documents){
		$found = false;
		$collectionDir = $this->collectionDir;

		if( is_array($documents)){
			foreach ($documents as $key => $value) {
				$id = $value->getID();
				$documentNameFull = $collectionDir.$id.$this->posfixDocument;			
				if(file_exists($documentNameFull)){
					$found = true;
					unlink($documentNameFull);
				}
			}
		}else{
			$id = $documents->getID();
			$documentNameFull = $collectionDir.$id.$this->posfixDocument;			
			if(file_exists($documentNameFull)){
				$found = true;
				unlink($documentNameFull);
			}
		}
		return $found;
	}


	/**
	 * Performs the recording operation effectively
	 */
	public function execute(){
		$baseName = $this->baseName;
		$collectionDir = $this->collectionDir;

		if(!file_exists($baseName)) if(@mkdir($baseName,0777,true) === false) die('Permission denied on directory: '.$baseName);
		if(!file_exists($collectionDir)) if(@mkdir($collectionDir,0777,true) === false) die('Permission denied on directory: '.$collectionDir);

		foreach ($this->documents as $key => $document) {
			$documentID = isset($document->_id)?Strings::slugify($document->_id):microtime(true);
			$documentID = preg_replace('#[\.|\s]#m', '', $documentID);
			$documentID = md5($documentID);
			
			$checkId = $document->getID();
			$outDocument = new \StdClass;
			
			if($checkId !== null) $documentID = $checkId;

			$documentName = $collectionDir.$documentID.'_deskdb.json';			
			$outDocument->_id = $documentID;

			foreach ($document as $keyModel => $valueModel) {				
				$outDocument->$keyModel = $valueModel;
			}

			File::save($documentName, json_encode($outDocument));
		}

	}



	
}
<?php 

namespace deskdb\drivers;

use deskdb\libs\File; 
use deskdb\libs\Strings; 
use deskdb\Document; 

use deskdb\DriverInterface as DriverInterface;

class JSON implements DriverInterface{

	private $collectionName,
			$collectionDir,
			$baseName,
			$posfixDocument = '_deskdb.json';

	function __construct($baseDir = null){

		if($baseDir === null){
			$baseDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'deskdb'.DIRECTORY_SEPARATOR;
			$server = $_SERVER;
			unset($server['REMOTE_ADDR']);
			unset($server['SERVER_ADDR']);
			unset($server['REMOTE_PORT']);
			unset($server['REQUEST_TIME_FLOAT']);
			unset($server['REQUEST_TIME']);
			$server = json_encode($server);
			$idServer = md5($server);
			$baseDir = $baseDir.$idServer.DIRECTORY_SEPARATOR;
		}

		if(is_null($baseDir)){
			die('Base not found');
		}

		$this->baseDir = $baseDir;

	}

	public function setCollectionName($collectionName){

		$this->collectionName = $collectionName;
		
		$collection = isset($this->collectionName)?$this->collectionName:null;
		if(is_null($collection)) die('Collection not found');

		$collectionDir = $this->baseDir.DIRECTORY_SEPARATOR.$collection.DIRECTORY_SEPARATOR;
		
		$collectionDir = preg_replace('#\/\/#m', '/', $collectionDir);
		$this->collectionDir = $collectionDir;
	}
	

	/**
	 * List a or multiples documents
	 */
	public function get($parameters){

		$key = isset($parameters[0])?$parameters[0]:null;
		$value = isset($parameters[1])?$parameters[1]:null;
		$operator = isset($parameters[2])?$parameters[2]:'===';
	
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
			if($content === false)continue;
			$document = json_decode($content);

			$id = str_replace($this->posfixDocument, '', $documentName);

			$documentModel = new Document;
			if(is_object($document) || is_array($document))
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
				if( (string) ($documentModel->{$key})  === (string) ($value) )				
					$listFound[$id] = $documentModel;

			if($operator === '!==')				
				if( (string) ($documentModel->{$key})  !== (string) ($value)) $listFound[$id] = $documentModel;
			

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
	 * Create a document
	 */
	public function post($parameters){


		$documents = isset($parameters[0])?$parameters[0]:null;

		$found = false;
		$collectionDir = $this->collectionDir;

		if( is_array($documents)){
			$this->documents = $documents;
		}else{
			$this->documents = [$documents];
		}

		return $this->execute();

	}


	/**
	 * list only first document
	 */
	public function getFirst($parameters){
		$result = $this->get($parameters);
		return reset($result);
	}


	
	/**
	 * Update a document
	 */
	public function put($parameters){
		
		$documents = isset($parameters[0])?$parameters[0]:null;

		$found = false;
		$collectionDir = $this->collectionDir;

		if( is_array($documents)){
			$this->documents = $documents;
		}else{
			$this->documents = [$documents];
		}

		return $this->execute();

	}



	/**
	 * Delete a document
	 */
	public function delete($parameters){

		$documents = isset($parameters[0])?$parameters[0]:null;



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
			
			if($documents === false) return false;

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

		$found = false;

		$baseDir = $this->baseDir;
		$collectionDir = $this->collectionDir;

		if(!file_exists($baseDir)) if(@mkdir($baseDir,0777,true) === false) die('Permission denied on directory: '.$baseDir);
		if(!file_exists($collectionDir)) if(@mkdir($collectionDir,0777,true) === false) die('Permission denied on directory: '.$collectionDir);

		
		

		foreach ($this->documents as $key => $document) {
			$documentID = isset($document->_id)?Strings::slugify($document->_id):microtime(true);
			$documentID = preg_replace('#[\.|\s]#m', '', $documentID);
			$documentID = md5($documentID);
			

			if($document === false) continue;

			$checkId = $document->getID();
			$outDocument = new \StdClass;
			
			if($checkId !== null) $documentID = $checkId;

			$documentName = $collectionDir.$documentID.'_deskdb.json';			
			$outDocument->_id = $documentID;


			foreach ($document as $keyModel => $valueModel) {				
				if($valueModel === '') continue;
				$outDocument->$keyModel = $valueModel;
			}
			
			$found = true;
			File::save($documentName, json_encode($outDocument));
			usleep(10);
		}

		return $found;
	}



}
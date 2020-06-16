<?php 

namespace deskdb;

class Strings{

	public static function slugify($text){
	  return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
	}
	
}
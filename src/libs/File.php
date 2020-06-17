<?php 

namespace deskdb\libs;

class File{

	public static function save($filename, $content){		
		$fp = fopen($filename, "w");
        fwrite($fp, $content);
        fclose($fp);
	}

	public static function load($filename){		
		$handle = fopen($filename, "rb");
	    $contents = fread($handle, filesize($filename));
	    fclose($handle);

	    return $contents;
	}
	
}
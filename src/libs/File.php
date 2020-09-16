<?php 

namespace deskdb\libs;

class File{

	public static function save($filename, $content){		
		$fp = fopen($filename, "w");
        fwrite($fp, $content);
        fclose($fp);
	}

	public static function load($filename){ 
            $filesize = filesize($filename);
            if($filesize < 1) return false; 
            $handle = fopen($filename, "rb");
        $contents = fread($handle, $filesize);
        fclose($handle);

        return $contents;
    }

	
}
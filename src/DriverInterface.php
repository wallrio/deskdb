<?php 

namespace deskdb;

interface DriverInterface {
 
	public function get($parameters);
	public function post($parameters);
	public function put($parameters);
	public function delete($parameters);
 
}
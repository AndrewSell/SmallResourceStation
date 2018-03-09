<?php

$action = $_GET['action'];
$actions = array('tk', 'up', 'fd');

if(! in_array($action, $actions)){
	
	exit;
}

$upload = new upload();
$upload->$action();


class upload
{
	private $_tokenPath = '../upload/tokens/';
	private $_filePath = '../upload/file/';

	

	public function tk(){
	
		$file['name'] = $_GET['name'];                
		$file['size'] = $_GET['size'];                
		$file['token'] = md5(json_encode($file['name'] . $file['size']));
		
		if(! file_exists($this->_tokenPath . $file['token'] . '.token')){
		
			$file['up_size'] = 0;                    
			$pathInfo = pathinfo($file['name']);
			$path = $this->_filePath . date('Ymd') .'/';
			
			if(! is_dir($path)){
				mkdir($path, 0700);
			}
			
			$file['filePath'] = $path . $file['token'] .'.'. $pathInfo['extension'];
			$file['modified'] = $_GET['modified'];      
			
			$this->setTokenInfo($file['token'], $file);
		}
		$result['token'] = $file['token'];
		$result['success'] = true;
	

		echo json_encode($result);
		exit;
	}
	
	
	
	public function up(){
		if('html5' == $_GET['client']){
			$this->html5Upload();
		}
		elseif('form' == $_GET['client']){
			$this->flashUpload();
		}
		else {
			
			exit;
		}

	}
	
	
	protected function html5Upload(){
		$token = $_GET['token'];
		$fileInfo = $this->getTokenInfo($token);
		
		if($fileInfo['size'] > $fileInfo['up_size']){
			
			$data = file_get_contents('php://input', 'r');
			if(! empty($data)){
				
				$fp = fopen($fileInfo['filePath'], 'a');
				flock($fp, LOCK_EX);
				fwrite($fp, $data);
				flock($fp, LOCK_UN);
				fclose($fp);
				
				$fileInfo['up_size'] += strlen($data);
				if($fileInfo['size'] > $fileInfo['up_size']){
					$this->setTokenInfo($token, $fileInfo);
				}
				else {
					
					@unlink($this->_tokenPath . $token . '.token');
				}
			}
		}
		$result['start'] = $fileInfo['up_size'];
		$result['success'] = true;

		echo json_encode($result);
		exit;
	}
	
	
	public function flashUpload(){
	
	
		$result['success'] = false;

		echo json_encode($result);
		exit;
	}
	
	
	protected function setTokenInfo($token, $data){
		
		file_put_contents($this->_tokenPath . $token . '.token', json_encode($data));
	}

	
	protected function getTokenInfo($token){
		$file = $this->_tokenPath . $token . '.token';
		if(file_exists($file)){
			return json_decode(file_get_contents($file), true);
		}
		return false;
	}


}
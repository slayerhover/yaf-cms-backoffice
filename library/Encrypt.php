<?php    
class Encrypt
{
	private $privateKey;
	private $publicKey;
	private $privateKeyFilePath;    
	private $publicKeyFilePath;
	
	public function __construct(){
		if(!extension_loaded('openssl')){ throw new Exception('需要openssl扩展支持');}
		$this->privateKeyFilePath   = CERT_DIR.'/rsa_private_key.pem';    
		$this->publicKeyFilePath	= CERT_DIR.'/rsa_public_key.pem';
	
		if(!file_exists($this->privateKeyFilePath) || !file_exists($this->publicKeyFilePath)){ throw new Exception('密钥或者公钥的文件路径不正确');}
		$this->privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyFilePath));    
		$this->publicKey = openssl_pkey_get_public(file_get_contents($this->publicKeyFilePath));    			
		if(!$this->privateKey || !$this->publicKey){ throw new Exception('密钥或者公钥不可用');}    
	}	
	public function encode($originData=[]){
		$originData = json_encode($originData, JSON_UNESCAPED_UNICODE);		
		$encryptData= '';		
		if (openssl_private_encrypt($originData, $encryptData, $this->privateKey)) {    
			return base64_encode($encryptData);
		} else {    
			throw new Exception('加密失败');
		}
	}	
	public function decode($encryptData){		
		$decryptData = '';
		$encryptData = base64_decode($encryptData);
		if (openssl_public_decrypt($encryptData, $decryptData, $this->publicKey)) {    
			return json_decode($decryptData, TRUE);
		} else {
			throw new Exception('解密失败');
		}	
	}   
}	

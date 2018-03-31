<?php
namespace Vendor;
use Predis\Client;

class Redis{

	public $client;
	function __construct(){
		// 读取配置
		$config = require '/../config.php';
		$this->client = new Client( $config['redis'] );

	}

	public function set($key,$value){
		$res = $this->client->set($key,$value);
		// 设置缓存60秒
		$this->client->executeRaw(['EXPIRE', $key, 60]);
		return $res;
	}

	public function get($value){
		return $this->client->get($value);
	}

}

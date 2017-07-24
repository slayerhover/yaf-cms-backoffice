<?php

class CacheRedis extends Cache {

	protected $redis;

	protected $is_connected = false;

	public function __construct() {
		if ($this->is_connected==FALSE)
		{
			$this->connect();
		}			
	}

	public function __destruct() {
		$this->close();
	}

	public function connect() {
		if (extension_loaded('redis') && class_exists('Redis'))
		{
			$this->redis = new Redis();
		}
		else
		{
			return false;
		}
		$servers = self::getRedisServers();
		if (!$servers)
			return false;
		foreach ($servers as $server)
			$this->redis->connect($server['host'], $server['port']);
		$this->is_connected = true;

		return true;
	}

	protected function _set($key, $value, $ttl = 900) {
		if (!$this->is_connected)
			return false;
		
		if($ttl>0){
			return $this->redis->setEx($key, $ttl, json_encode($value, JSON_UNESCAPED_UNICODE));
		}else{
			return $this->redis->set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
		}
	}

	protected function _get($key) {
		if (!$this->is_connected)
			return false;		
		return json_decode($this->redis->get($key), TRUE);
	}
	
	protected function _incr($key) {
		if (!$this->is_connected)
			return false;		
		return $this->redis->incr($key);
	}

	protected function _expire($key, $ttl = 900) {
		if (!$this->is_connected)
			return false;
		
		return $this->redis->expire($key, $ttl);		
	}
	
	protected function _exists($key) {		
		if (!$this->is_connected)
			return false;
		
		return $this->redis->exists($key);
	}

	protected function _delete($key) {
		if (!$this->is_connected)
			return false;

		return $this->redis->delete($key);
	}

	public function flush() {
		if (!$this->is_connected)
			return false;

		return $this->redis->flush();
	}

	protected function close() {
		if (!$this->is_connected)
			return false;

		return $this->redis->close();
	}

	public static function getRedisServers() {
		if (Yaf_Registry::has('redis_servers'))
		{
			return Yaf_Registry::get('redis_servers');
		}
		else
		{
			$servers = array();
			$rediscaches = Yaf_Registry::get('config')->cache->redis;
			if (!empty($rediscaches))
			{
				$hosts = explode('|', $rediscaches->hosts);
				$ports = explode('|', $rediscaches->ports);
				foreach ($hosts as $key => $host)
				{
					if (isset($ports[$key]))
					{
						$servers[] = array('host' => $host, 'port' => $ports[$key]);
					}
				}
				Yaf_Registry::set('redis_servers', $servers);
			}

			return $servers;
		}

	}
}

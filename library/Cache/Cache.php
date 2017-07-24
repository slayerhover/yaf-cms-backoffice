<?php

abstract class Cache {

	const KEYS_NAME = '_KEYS_';
	const SQL_TABLES_NAME = 'tablesCached';

	protected static $instance;
	protected $sql_tables_cached;
	protected $blacklist = array();
	protected static $local = array();

	abstract protected function _set($key, $value, $ttl = 0);

	abstract protected function _get($key);
	
	abstract protected function _incr($key);

	abstract protected function _expire($key, $ttl = 0);
	
	abstract protected function _exists($key);

	abstract protected function _delete($key);

	abstract public function flush();

	public static function getInstance() {
		if (!self::$instance)
		{
			$caching_system = Yaf_Registry::get('config')->cache->caching_system;
			if (!empty($caching_system) && file_exists(dirname(__FILE__) . '/' . $caching_system . '.php'))
			{
				Yaf_Loader::import(dirname(__FILE__) . '/' . $caching_system . '.php');
				self::$instance = new $caching_system();
				self::$instance->blacklist = Yaf_Registry::get('cache_exclude_table');
				if (!defined('CACHE_KEY_PREFIX'))
				{
					define('CACHE_KEY_PREFIX', 'cye_');
				}
			}
		}

		return self::$instance;
	}

	public function set($key, $value, $ttl = 900) {
		$key = CACHE_KEY_PREFIX . $key;
		if (strlen($key) <= 250)
		{
			return $this->_set($key, $value, $ttl);
		}

		return false;
	}

	public function get($key) {
		$key = CACHE_KEY_PREFIX . $key;			
		if (strlen($key) <= 250)
			return $this->_get($key);
		
		return false;
	}
	
	public function incr($key) {
		$key = CACHE_KEY_PREFIX . $key;			
		if (strlen($key) <= 250)
			return $this->_incr($key);
		
		return 0;
	}
	
	public function expire($key, $ttl = 900) {
		$key = CACHE_KEY_PREFIX . $key;
		if (strlen($key) <= 250)
		{
			return $this->_expire($key, $ttl);
		}

		return false;
	}

	public function exists($key) {
		$key = CACHE_KEY_PREFIX . $key;		
		if (strlen($key) <= 250)			
			return $this->_exists($key);
		
		return false;
	}

	public function delete($key) {
		$key = CACHE_KEY_PREFIX . $key;
		if (strlen($key) <= 250)			
			return $this->_delete($key);
		
		return false;
	}

	public function setQuery($query, $result) {
		if ($this->isBlacklist($query))
			return true;

		if (is_null($this->sql_tables_cached))
		{
			$this->sql_tables_cached = $this->get(self::SQL_TABLES_NAME);
			if (!is_array($this->sql_tables_cached))
				$this->sql_tables_cached = array();
		}

		$key = md5($query);
		if ($this->exists($key))
			return true;
		$this->set($key, $result);

		if ($tables = $this->getTables($query))
		{
			foreach ($tables as $table)
			{
				if (!empty($table))
				{
					if (!isset($this->sql_tables_cached[$table][$key]))
					{
						$this->sql_tables_cached[$table][$key] = true;
					}
				}
			}
		}		
		$this->set(self::SQL_TABLES_NAME, $this->sql_tables_cached);
	}

	protected function getTables($string) {
		if (preg_match_all('/(?:from|join|update|into)\s+`?([a-z_-]+)`?(?:,\s{0,}`?([a-z_-]+)`?)?\s.*/Umsi', $string . ' ', $res))
		{
			return array_merge($res[1], $res[2]);
		}
		else
		{
			return false;
		}
	}

	public function deleteQuery($query) {
		if (is_null($this->sql_tables_cached))
		{
			$this->sql_tables_cached = $this->get(self::SQL_TABLES_NAME);
			if (!is_array($this->sql_tables_cached))
				$this->sql_tables_cached = array();
		}

		if ($tables = $this->getTables($query))
		{
			foreach ($tables as $table)
			{
				if (!empty($table))
				{
					if (isset($this->sql_tables_cached[$table]))
					{
						foreach (array_keys($this->sql_tables_cached[$table]) as $fs_key)
						{
							$this->delete($fs_key);
							$this->delete($fs_key . '_nrows');
						}
						unset($this->sql_tables_cached[$table]);
					}
				}
			}
		}
		$this->set(self::SQL_TABLES_NAME, $this->sql_tables_cached);
	}

	protected function isBlacklist($query) {
		if ($this->blacklist && is_array($this->blacklist))
		{
			foreach ($this->blacklist as $find)
			{
				if (stripos($query, $find) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	public static function store($key, $value) {
		Cache::$local[$key] = $value;
	}

	public static function retrieve($key) {
		return isset(Cache::$local[$key]) ? Cache::$local[$key] : null;
	}

	public static function retrieveAll() {
		return Cache::$local;
	}

	public static function isStored($key) {
		return isset(Cache::$local[$key]);
	}

	public static function clean($key) {
		if (strpos($key, '*') !== false)
		{
			$regexp = str_replace('\\*', '.*', preg_quote($key, '#'));
			foreach (array_keys(Cache::$local) as $key)
				if (preg_match('#^' . $regexp . '$#', $key))
					unset(Cache::$local[$key]);
		}
		else
			unset(Cache::$local[$key]);
	}
}

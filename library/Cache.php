<?php
class Cache {

    protected static $instance;

    public function __construct() {
        return CACHE_ENABLE && (self::$instance || self::connection());
    }

    public static function connection() {
        if(!self::$instance) {
            if (CACHE_ENABLE && extension_loaded('redis') && class_exists('Redis')) {
                self::$instance = new Redis();
            } else {
                throw new Exception('Redis server has gone away.');
            }
            $servers = self::getRedisServers();
            if (!$servers) return false;
            foreach ($servers as $server) {
                self::$instance->connect($server['host'], $server['port']);
            }
            self::$instance->select(Yaf_Registry::get('config')->cache->redis->selectDB);
        }
        return self::$instance;
    }

    public static function db($db_num)
    {
        self::connection()->select($db_num);
        return self::$instance;
    }

    public function __destruct() {
        self::$instance->close();
    }

    public static function set($key, $value, $ttl = 900) {
        $key = CACHE_KEY_PREFIX . $key;
        if(is_array($value)){
            $value =json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if($ttl>0){
            return self::connection()->setEx($key, $ttl, $value);
        }else{
            return self::connection()->set($key, $value);
        }
    }

    public static function get($key) {
        $key = CACHE_KEY_PREFIX . $key;

        $result =json_decode(self::connection()->get($key), TRUE);
        if($result){
            return $result;
        }else{
            return self::connection()->get($key);
        }
    }

    public static function incr($key) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->incr($key);
    }

    public static function expire($key, $ttl = 900) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->expire($key, $ttl);
    }

    public static function exists($key) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->exists($key);
    }

    public static function delete($key) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->delete($key);
    }

    public static function lpush($key, $value) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->lpush($key, $value);
    }

    public static function rpop($key) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->rpop($key);
    }

    public static function sadd($key, $value) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->sadd($key, $value);
    }

    public static function smembers($key) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->smembers($key);
    }

    public static function srem($key) {
        $key = CACHE_KEY_PREFIX . $key;

        return self::connection()->srem($key);
    }

    public static function flushdb() {
        return self::connection()->flushdb();
    }

    public static function close() {
        return self::connection()->close();
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

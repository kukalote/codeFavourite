<?php

/**
 * Class RedisLib
 * @author xun
 */

class RedisLib
{
    const DAY_TIME   = 86400;
    const WHILE_TIME = 180;

    /**
     * getCache 获取缓存
     * @param $key
     * @param $groupKey
     * @return mixed
     */
    public static function getCache($key, $groupKey)
    {
        $groupFlag = self::getGroupFlag($groupKey);
        $value     = static::cache()->get("{$groupKey}:{$key}:{$groupFlag}");
        return json_decode($value);
    }

    /**
     * setCache 缓存数据
     * @param $key
     * @param $groupKey
     * @param $value
     * @param int $expire
     * @return false|string|null
     */
    public static function setCache($key, $groupKey, $value, $expire = self::DAY_TIME)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $groupFlag = self::getGroupFlag($groupKey, $expire);

        // 如果 value 为空，则保存空值 10 秒，防止频繁查库
        $expire = is_null($value) ? self::WHILE_TIME : $expire;

        static::cache()->set("{$groupKey}:{$key}:{$groupFlag}", $value, $expire);
        return $value;
    }

    /**
     * getFuncCache 获取方法数据缓存
     * @param $key
     * @param $groupKey
     * @param $func
     * @param int $expire
     * @return mixed
     */
    public static function getFuncCache($key, $groupKey, $func, $expire = self::DAY_TIME)
    {
        $data = ($expire == 0) ? null : self::getCache($key, $groupKey);
        if (empty($data)) {
            $data = $func();
            self::setCache($key, $groupKey, $data, $expire);
        }
        return $data;
    }

    /**
     * delCache 删除缓存 key
     * @param $key
     * @param $groupKey
     * @return mixed
     */
    public static function delCache($key, $groupKey)
    {
        $groupFlag = self::getGroupFlag($groupKey);
        return self::cache()->del("{$groupKey}:{$key}:{$groupFlag}");
    }

    /**
     * flushCache   删除缓存 groupKey
     * @param $groupKey
     * @return mixed
     */
    public static function flushCache($groupKey)
    {
        return self::cache()->del($groupKey);
    }

    /**
     * getGroupFlag 获取 group 标识
     * @param $groupKey
     * @param int $expire
     * @return int
     */
    private static function getGroupFlag($groupKey, $expire = self::WHILE_TIME)
    {
        return static::cache()->get($groupKey) ?? self::makeGroupFlag($groupKey, $expire);
    }

    /**
     * makeGroupFlag 创建 group 标识
     * @param $groupKey
     * @param int $expire
     * @return int
     */
    private static function makeGroupFlag($groupKey, $expire = self::DAY_TIME)
    {
        $groupFlag = time();
        static::cache()->set($groupKey, $groupFlag, $expire);
        return $groupFlag;
    }

    public static function cache()
    {
        $config = ['host' => '127.0.0.1', 'port' => '6379'];
        $redis  = new Redis();
        $redis->connect($config['host'], $config['port']);
        return $redis;
    }
}

function getInfo($uid)
{
    $key      = "uid:{$uid}";
    $groupKey = 'admin';
    return RedisLib::getFuncCache(
        $key,
        $groupKey,
        function () use ($uid) {
            $ret = $dbInfo = [];
            return ($ret) ? $ret : [];
        }
    );
}

function modifyInfo($uid, $data)
{
    $key      = "uid:{$uid}";
    $groupKey = 'admin';
    $modifyDB = $data;
    RedisLib::delCache($key, $groupKey);
}

function modifyGroup($uid, $data)
{
    $groupKey = 'admin';
    $modifyDB = $data;
    RedisLib::flushCache($groupKey);
}
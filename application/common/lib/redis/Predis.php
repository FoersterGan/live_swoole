<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/11
 * Time: 10:13
 */
namespace  app\common\lib\redis;
class Predis{
    public $redis="";
    /**
     * 定义单粒模式的变量
     * @var null
     */
    private static $_instance=null;

    /**
     * 单粒模式的核心为使之自身调动自身
     * @return Predis|null
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)){
            self::$_instance=new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->redis= new \Redis();
        $result=$this->redis->connect(config('redis.host'),config('redis.port'),
            config('redis.timeOut'));
        if($result===false){
            throw new \Exception('redis connect error');
        }
    }

    /**
     * set redis
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key,$value,$time=0)
    {
        if(!$key) {
            return '';
        }
        if(is_array($value))
        {
            $value = json_encode($value);
        }
        if(!$time)
        {
            return $this->redis->set($key,$value);
        }
        return $this->redis->setex($key,$time,$value);//setex 代表过期时间
    }

    /**
     * redis get
     * @param $key
     * @return bool|string
     */
    public function get($key){
        if(!$key){
            return '';
        }
        return $this->redis->get($key);
    }

    /**
     * 添加有序集合
     * @param $key
     * @param $value
     * @return mixed
     */
    public function sAdd($key,$value)
    {
        return $this->redis->sAdd($key,$value);
    }

    public function sRem($key,$value)
    {
        return $this->redis->sRem($key,$value);
    }

    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    public function del($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 执行一个不存在的方法
     * @param $name            方法名
     * @param $arguments       参数值
     * @return string
     */
//    public function __call($name, $arguments)
//    {
//        // TODO: Implement __call() method.
//        if(count($arguments)!=2)
//        {
//            return '';
//        }
//        return $this->redis->$name($arguments[0],$arguments[1]);
//    }
}
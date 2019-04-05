<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/10
 * Time: 21:22
 */
namespace  app\common\lib;
//协程redis
class Redis{
    /**
     * 验证码前缀
     * @var string
     */
    public static $pre='sms_';
    /**
     * 用户user pre
     * @var string
     */
    public static $userPre='user_';
    public static function smsKey($phone)
    {
        return self::$pre.$phone;
    }

    /**
     * 用户key
     * @param $phone
     * @return string
     */
    public static function userKey($phone)
    {
        return self::$userPre.$phone;
    }

}
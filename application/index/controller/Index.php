<?php
namespace app\index\controller;
use app\common\lib\ali\Sms;
class Index
{
    public function index()
    {
        return '';
    }

    public function singwa()
    {
        echo time();
    }

    public function think_swoole()
    {
        echo 'thikn zhi fhi swoole123123';
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function sms()
    {
        echo 321;
//        die();
        try{
            Sms::sendSms(13883433455,12345);
        }catch(\Exception $e){
            //todo
        }
    }
}

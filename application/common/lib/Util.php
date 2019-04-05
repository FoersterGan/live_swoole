<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/10
 * Time: 9:30
 */
namespace app\common\lib;
class Util
{
    /***
     * API 格式输出
     * @param $status
     * @param string $message
     * @param array $data
     * @return string
     */
    public static function show($status,$message='',$data=[])
    {
        $return=[
            'status'=>$status,
            'message'=>$message,
            'data'=>$data,
        ];
        echo json_encode($return);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/23
 * Time: 22:37
 */
namespace app\admin\controller;
use app\common\lib\Util;

class Image
{
    public function index()
    {
       $file=request()->file('file');
       $info=$file->move('../public/static/upload');
       if($info)
       {
           $data=[
                'image'=>config('live.host').'/upload/'.$info->getSaveName(),
           ];
           Util::show(config('code.success'),'OK12',$data);
       }else{
           Util::show(config('code.error','error',''));
       }
    }
}
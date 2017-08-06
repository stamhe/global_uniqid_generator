<?php

/**
全局唯一id生产类
https://segmentfault.com/a/1190000007769660
基于Twitter的SnowFlake算法改造
twitter的结构：
64bit = 1bit为空缺 + 41bit毫秒时间戳 + 10bit机器id + 12bit自增id
当前结构
64bit = 1bit空缺 + 7bit业务编号(128项业务) + 39bit毫秒时间戳(与2017-06-01的差值) + 5bit机器id(32台) + 4bit一级随机数 + 4bit二级随机数 + 4bit三级随机数
*/

$machineid_file = "/data/machineid.txt";
if(file_exists($machineid_file)) {
    $machine_id = intval(file_get_contents($machineid_file));
    if($machine_id > 15 || $machine_id < 0) {
        $machine_id = 0;
    }
} else {
    $machine_id = 0;
}

define('MACHINE_ID', $machine_id);


class BusinessIdHelper
{
    public static $start_stamp = 1496246400000; // 2017-06-01 00:00:00的毫秒级时间戳
    public static $max_rand = 15;
    public static function generateId($business_id)
    {
        // 1bit - 头部空缺
        $head_bin = str_pad(decbin(0), 1, "0", STR_PAD_LEFT);
        
        // 7bit - 7bit业务编号(最多128项业务)
        $business_id_bin = str_pad(decbin($business_id), 7, "0", STR_PAD_LEFT);
        
        // 39bit - (now - $start_stamp)的毫秒级时间戳差值
        $now_stamp = floor(microtime(true) * 1000);
        $diff_stamp_bin = str_pad(decbin($now_stamp - self::$start_stamp), 39, "0", STR_PAD_LEFT);
        
        // 5bit - 32台机器
        $machine_id_bin = str_pad(decbin(MACHINE_ID), 5, "0", STR_PAD_LEFT);
        
        // 4bit - 4位随机数(0 ~ 15)
        // 一级
        $random1 = mt_rand(0, self::$max_rand);
        $random1_bin = str_pad(decbin($random1), 4, "0", STR_PAD_LEFT);
        // 二级
        $random2 = mt_rand(0, self::$max_rand);
        $random2_bin = str_pad(decbin($random2), 4, "0", STR_PAD_LEFT);
        // 三级
        $random3 = mt_rand(0, self::$max_rand);
        $random3_bin = str_pad(decbin($random3), 4, "0", STR_PAD_LEFT);
        
        // 链接二进制串
        $pack_data = $head_bin . $business_id_bin. $diff_stamp_bin . $machine_id_bin . $random1_bin . $random2_bin . $random3_bin;
        
        /*
        var_dump($business_id);
        var_dump($now_stamp);
        var_dump(MACHINE_ID);
        var_dump($pid);
        var_dump($pack_data);
        */
        
        return bindec($pack_data);
    }
    
    public static function parseId($id)
    {
        // 数据不满64位的，需要补齐
        $data_bin = str_pad(decbin($id), 64, "0", STR_PAD_LEFT);
        
        $head_bin = substr($data_bin, 0, 1);
        
        $business_id_bin    = substr($data_bin, 1, 7);
        $diff_stamp_bin     = substr($data_bin, 8, 39);
        $machine_id_bin     = substr($data_bin, 47, 5);
        $random1_bin        = substr($data_bin, 52, 4);
        $random2_bin        = substr($data_bin, 56, 4);
        $random3_bin        = substr($data_bin, 60, 4);
        
        $head           = bindec($head_bin);
        $business_id    = bindec($business_id_bin);
        $diff_stamp     = bindec($diff_stamp_bin);
        $machine_id     = bindec($machine_id_bin);
        $random1        = bindec($random1_bin);
        $random2        = bindec($random2_bin);
        $random3        = bindec($random3_bin);
        
        $stamp = self::$start_stamp + $diff_stamp;  // 毫秒
        
        $data = [
            'head'          => $head,
            'business_id'   => $business_id,
            'stamp'         => $stamp,  // 毫秒
            'machine_id'    => $machine_id,
            'random1'       => $random1,
            'random2'       => $random2,
            'random3'       => $random3,
        ];
        
        return $data;
    }
}

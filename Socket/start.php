<?php
use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Lib\Timer;
use PHPSocketIO\SocketIO;
use DiDom\Document;
use Vendor\Api;
use Vendor\Redis;

require_once __DIR__ . '/phpsocket.io/autoload.php';
require_once __DIR__ . '/workerman/Autoloader.php';

require '../vendor/autoload.php';
require 'vendor/api.php';
require 'vendor/redis.php';


$io = new SocketIO(2345);

// 4个进程
$io->count = 4;

$io->on('workerStart', function()use($io) {

    // 定时器 每50秒执行一次
    // $time_interval = 50;
    // Timer::add($time_interval, function(){
    //     echo "task run\n";
    // });

});

//链接以后的事件
$io->on('connection', function($socket){

    $socket->addedUser = false;

    // api
    $socket->on('api', function ($data)use($socket){

        //****文件锁 队列*************
        $fp = fopen('lock.lock', 'r');  
        flock($fp,LOCK_EX);  

            // start
                // redis缓存
                $redis = new Redis();
                $json='';
                if( is_null($redis->get($data)) ){
                    // 接口数据
                    $api = new Api();
                    $res = $api->api($data);
                    // 保存到redis
                    $redis->set($data,json_encode($res) );
                    $json=$redis->get($data);

                    echo " [redis ] : *** cache  is ok! ******************** \n";
                }else{
                    $json=$redis->get($data);
                }
            // end

        //****文件锁 释放*************    
        flock($fp , LOCK_UN);    
        fclose($fp);     



    	

        $socket->emit('api', $json);

        echo " [status] : *** request is ok! ******************** \n";
    });
   


});

Worker::runAll();



<?php
/**
 * 引入socket配置文件
 */
require "socket_config.php";
require "socket_functions.php";
/**
 * ***************************************
 *           绑定回调事件                *
 * ***************************************
 */
$ws = null;
//wss服务
$ws = new swoole_websocket_server($conf['listen']['host'], $conf['listen']['port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
$ws->set($conf['setting']);

/**
 * Server启动在主进程的主线程回调此函数
 * 在此事件之前Swoole Server已进行了如下操作
 * 已创建了manager进程
 * 已创建了worker子进程
 * 已监听所有TCP/UDP端口
 * 已监听了定时器
 * 在onStart中创建的全局资源对象不能在worker进程中被使用，因为发生onStart调用时，worker进程已经创建好了。新创建的对象在主进程内，worker进程无法访问到此内存区域。因此全局对象创建的代码需要放置在swoole_server_start之前
 */
$ws->on('start', function ($ws) {
    swoole_set_process_name(PROCESS_NAME.'_master');
});

/**
 * 与onStart回调在不同进程中并行执行的回调函数(不存在先后顺序)
 * @param: $ws swoole_websocket_server object
 * @param: $wid 创建该进程时swoole分配的id(不是进程id)
 * 注意点:
 * 1. 此事件在worker进程/task进程启动时发生。onWorkerStart/onStart是并发执行的，没有先后顺序,这里创建的对象可以在进程生命周期内使用
 * 2. swoole1.6.11之后task_worker中也会触发onWorkerStart,故而在下面的处理中，加入了判断业务类型$jobType是task还是work，如果是task则命名为****_Tasker_$id,如果是worker则命名为****_Worker_$id
 * 3. 发生PHP致命错误或者代码中主动调用exit时，Worker/Task进程会退出，管理进程会重新创建新的进程
 * 5. 如果想使用swoole_server_reload实现代码重载入，必须在workerStart中require你的业务文件，而不是在文件头部。在onWorkerStart调用之前已包含的文件，不会重新载入代码。
 * 6. 可以将公用的，不易变的php文件放置到onWorkerStart之前(例如上面的redis配置)。这样虽然不能重载入代码，但所有worker是共享的，不需要额外的内存来保存这些数据。
 * 7. onWorkerStart之后的代码每个worker都需要在内存中保存一份
 */
$ws->on('workerstart', function ($ws, $wid)use ($redisConf,$mysqlConf) {
    //获取redis全局对象
    redisStart($redisConf);
    //保存server对象到全局中以待使用
    $GLOBALS['ws'] = $ws;
    $jobType = $ws->taskworker ? 'Tasker' : 'Worker';
    swoole_set_process_name(PROCESS_NAME.'_'.$jobType.'_'.$wid);
    if ($jobType == 'Worker') { //在某个worker进程上绑定redis订阅进程
        echo "Worker {$wid} start!\n";
    }else{
        echo "Tasker {$wid} start!\n";
    }
});

/**
 * 管理进程启用时，调用该回调函数
 * 注意manager进程中不能添加定时器
 * manager进程中可以调用sendMessage接口向其他工作进程发送消息
 */
$ws->on('managerstart', function ($ws)use ($redisConf) {
    swoole_set_process_name(PROCESS_NAME.'_manage');
});

/**
 * swoole websocket服务特有的回调函数，此函数在websocket服务器中必须定义实现，否则websocket服务将无法启动
 * 当服务器收到来自客户端的数据帧时会回调此函数
 * @param: $ws为swoole_websocket_server对象，其结构在调试时可var_dump查看
 * @param: $frame为swoole_websocket_frame对象，包含了客户端发来的数据帧信息,包含以下四个属性：
 * @param: $frame->fd: 客户端的socket id，每个id对应一个客户端，推送消息的时候需要指定
 * @param: $frame->data: 数据内容，可以是文本内容或者是二进制数据(图片等)，可以通过opcode的值来判断。$data 如果是文本类型，编码格式必然是UTF-8，这是WebSocket协议规定的
 * @param: $frame->opcode: WebSocket的OpCode类型，可以参考WebSocket协议标准文档, WEBSOCKET_OPCODE_TEXT = 0x1 ，文本数据; WEBSOCKET_OPCODE_BINARY = 0x2 ，二进制数据
 * @param: $frame->finish: 表示数据帧是否完整，一个WebSocket请求可能会分成多个数据帧进行发送
 * 注意点: 客户端发送的ping帧不会触发onMessage，底层会自动回复pong包
 */
$ws->on('message', function ($ws, $frame) {
    //接收到客户端请求，并建立连接之后，进行相应业务的处理
//    $GLOBALS['ws']->task($frame);
    $msg = json_decode($frame->data,true);
    if (!empty($msg['type'])){
        switch ($msg['type']){
            case 'ping':            //心跳包
                $ws->push($frame->fd,json_encode(['type'=>'pong','msg'=>'pong',]));
                break;
            case 'c2c':             //个人信息
            case 'group':
                $ws->task($frame);
                break;
            case 'login':
                userLogin($ws,$frame,$msg);
                break;
        }
    }else{
        $ws->close($frame->fd);
    }
});

/**
 * 在task_worker进程内被调用。worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务(此处使用的是taskwait)
 * 当前的Task进程在调用onTask回调函数时会将进程状态切换为忙碌，这时将不再接收新的Task，当onTask函数返回时会将进程状态切换为空闲然后继续接收新的Task。
 * @param: $ws swoole_websocket_server object
 * @param: $tid task process id
 * @param: $wid from id 表示来自哪个Worker进程。$task_id和$wid组合起来才是全局唯一的，不同的worker进程投递的任务ID可能会有相同
 * @param: $data 需要执行的任务内容
 * 注意点: onTask函数执行时遇到致命错误退出，或者被外部进程强制kill，当前的任务会被丢弃，但不会影响其他正在排队的Task
 */
$ws->on('task', function ($ws, $tid, $wid, $frame) {
    $msg = json_decode($frame->data,true);
    switch ($msg['type']){
        case 'c2c':             //个人信息
            sendUserMsg($frame->fd,$msg,$frame->data);
            break;
    }
    return $frame;
});

/**
 * 当worker进程投递的任务在task_worker中完成时，task进程会通过$ws->finish()方法将任务处理的结果发送给worker进程。
 * @param: $ws swoole_websocket_server object
 * @param: $tid task_id
 * @param: $data 任务处理后的结果内容
 * 注意点: task进程的onTask事件中没有调用finish方法或者return结果，worker进程不会触发onFinish
 *        执行onFinish逻辑的worker进程与下发task任务的worker进程是同一个进程
 */
$ws->on('finish', function($ws, $tid, $data) {
//    $GLOBALS['ws']->close($data->fd);
});

/**
 * TCP客户端连接关闭后，在worker进程中回调此函数
 * 在函数中可以做一些类似于删除业务中与每个客户端交互时存放的数据的操作
 * @param: $ws swoole_websocket_server object
 * @param: $fd 已关闭的fd interger
 * @param: $rid(可选)，来自哪个reactor线程
 * 注意点:
 * 1. onClose回调函数如果发生了致命错误，会导致连接泄漏。通过netstat命令会看到大量CLOSE_WAIT状态的TCP连接
 * 2. 查看命令netstat -anopc | grep 端口号,可以查看到TCP接收和发送队列是否有堆积以及TCP连接的状态
 * 3. 无论由客户端发起close还是服务器端主动调用$serv->close()关闭连接，都会触发此事件。因此只要连接关闭，就一定会回调此函数
 * 4. 1.7.7+版本以后onClose中依然可以调用connection_info方法获取到连接信息，在onClose回调函数执行完毕后才会调用close关闭TCP连接
 * 5. 这里回调onClose时表示客户端连接已经关闭，所以无需执行$server->close($fd)。代码中执行$serv->close($fd)会抛出PHP错误告警。也就是在onclose中不能再$ws->close()了.
 * 6. swoole-1.9.7版本修改了$reactorId参数，当服务器主动关闭连接时，底层会设置此参数为-1，可以通过判断$reactorId < 0来分辨关闭是由服务器端还是客户端发起的(debug时可以使用)
 */
$ws->on('close', function ($ws, $fd) {
//    $redis = new Redis();
//    $redis->connect(REDIS_HOST, REDIS_PORT);
//    $redis->auth(REDIS_PWD);
//    $sArr = $redis->sMembers(REDIS_FD_S.$fd);
//    if (!empty($sArr)) {
//        foreach ((array)$sArr as $key => $sc) {
//            $res = $redis->sRem(REDIS_S_FD.$sc, $fd);
//            $num = $redis->sCard(REDIS_S_FD.$sc);
//            if ($num == '0') {
//                $redis->sRem(REDIS_S_KEY, $sc);
//                $redis->hDel(REDIS_ZS_KEY, $sc);
//            }
//        }
//    }
//    $redis->del(REDIS_FD_S.$fd);
//    $redis->close();
    $userId = $GLOBALS['redis']->hget('WsIdUser',$fd);
    if ($userId){
        $GLOBALS['redis']->hdel('WsIdUser',$fd);
        $GLOBALS['redis']->hdel('UserWsId',$userId);
    }
    echo "Client $fd has closed.\n";
});

/**
 * 开启swoole_websocket_server服务
 */
$ws->start();

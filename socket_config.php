<?php
/**
 * Created by PhpStorm.
 * User: pql-pc
 * Date: 18-6-16
 * Time: 下午10:29
 */
/**
 * ***************************************
 *            单进程保护                 *
 * ***************************************
 */
$phpSelf            = realpath($_SERVER['PHP_SELF']);
$lockFile           = $phpSelf.'.lock';
$lockFileHandle     = fopen($lockFile, "w");
if ($lockFileHandle == false) {
    exit("Can not create lock file $lockFile\n");
}
if (!flock($lockFileHandle, LOCK_EX + LOCK_NB)) {
    exit(date("Y-m-d H:i:s")."Process already exist.\n");
}
define("PROCESS_NAME","SOCKET");
$redisConf = [
    'host' => '127.0.0.1',
    'port' => 6379,
];

/**
 * ***************************************
 *     进入程序，定义相关配置            *
 * ***************************************
 */
//set_time_limit(0);
//socket会话的超时时间,根据业务场景设置，这里设置为永不超时
//如果设置了时间，则从socket建立=>传输=>关闭整个过程必须在定义的时间内完成，否则自动close该socket并抛出warning
//ini_set('default_socket_timeout', -1);
$conf = array(
    'listen'  => array('host' => '0.0.0.0','port' => 9420),
    'setting' => array(
        //程序允许的最大连接数，用以设置server最大允许维持多少个TCP连接，超过该数量后，新连接将被拒绝，默认为ulimit -n的值，如果设置大于ulimit -n则强制重置为ulimit- n，如果确实需要设置超过ulimit -n的值，请修改系统值 vim /etc/security/limits.conf 修改nofile的值
        "max_conn"          => 1024,
        //启用CPU亲和设置(在全异步非阻塞是可启用),在多核的服务器中，启用此特性会将swoole的reactor线程/worker进程绑定到固定的一个核上。可以避免进程/线程的运行时在多个核之间互相切换，提高CPU Cache的命中率,如何确定绑定在了哪个核上，请参考文档, 查看命令: taskset -p 进程id
        'open_cpu_affinity' => 0,
        //配置task进程数量,配置此参数后将会启用task功能。所以Server务必要注册onTask、onFinish2个事件回调函数。如果没有注册，服务器程序将无法启动.Task进程是同步阻塞的，配置方式与Worker同步模式一致。
        'task_worker_num'   => 20,
        //设置task进程的最大任务数。一个task进程在处理完超过此数值的任务后将自动退出。这个参数是为了防止PHP进程内存溢出。如果不希望进程自动退出可以设置为0, 默认是0
        'task_max_request'  => 1024,
        //设置task的数据临时目录，在swoole_server中，如果投递的数据超过8192字节，将启用临时文件来保存数据。这里的task_tmpdir就是用来设置临时文件保存的位置。
        'task_tmpdir'       => '/tmp/',
        //worker进程数量，根据业务代码的模式作调整，全异步非阻塞可设置为CPU核数的1-4倍;同步阻塞，请参考文档调整
        'worker_num'        => 8,
        //指定swoole错误日志文件
        'log_file'          => '/tmp/log/swoolelog.txt',
        //心跳包过期时间（s），超时关闭客户端链接
        'heartbeat_idle_time' => 10,
        //心跳包检测时间（s）
        'heartbeat_check_interval' => 3,
        //SSL公钥和私钥的位置，启用wss必须在编译swoole时加入--enable-openssl选项
        'ssl_cert_file'     => '/usr/local/nginx/conf/server.cer',
        'ssl_key_file'      => '/usr/local/nginx/conf/server.key',
    ),
);
/**
 * mysql配置参数
 */
$mysqlConf = array(
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'socket',
);
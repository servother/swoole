<?php 

class WebSocketServer
{
    private $_serv;
    
    public function __construct()
    {
        $this->_serv = new swoole_websocket_server("127.0.0.1", 9501);  //创建websocket服务端
        //server配置
        $this->_serv->set([
                'worke_num' => 1,
                'heartbeat_check_interval' => 10,  //每隔十秒检测一次心跳包
                'heartbeat_idle_time' => 30,  //超过三十秒没有收到心跳包，断开连接
        ]);
        //各个方法的回调
        $this->_serv->on('open', [$this, 'onOpen']);  //onOpen回调，客户端与服务端建立连接的时候触发该回调
        $this->_serv->on('message', [$this, 'onMessage']);  //服务端收到客户端的数据后触发该回调
        $this->_serv->on('close', [$this, 'onClose']);
    }
    
    /*
     * @param $serv
     * @param $request
     */
    public function onOpen($serv, $request)
    {
        echo "server handshake success with fd{$request->fd}.\n";
    }
    
    /*
     * @param $serv
     * @param $frame
     */
    public function onMessage($serv, $frame)
    {
        //$serv->push($frame->fd, "server receive data: {$frame->data}");
        // 循环当前的所有连接，并把接收到的客户端信息循环推送给所有连接的客户端
        //var_dump($frame->data);  //该值是个json字符串
        $tmp = json_decode($frame->data, true);
        if ($tmp['data'] == '*$%^%$*') {
            //echo "客户端心跳包！\n";  //心跳检测，以上字符串为心跳包
            return ;
        }
        
        foreach ($serv->connections as $fd) {
            $serv->push($fd, $frame->data);
        }
    }
    
    /*
     * @param $serv
     * @param $fd
     */
    public function onClose($serv, $fd)
    {
        date_default_timezone_set('Etc/GMT-8');
        $closeTime = date("Y-m-d H:i:s", time());
        echo "client {$fd} closed by $closeTime.\n";
    }
    
    public function start()
    {
        $this->_serv->start();
    }
}

    $server = new WebSocketServer;
    $server->start();

?>
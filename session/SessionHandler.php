<?php

/**
 * 重写session机制,redis
 * Class SessionHandler
 */
class SessionHandler implements SessionHandlerInterface
{

    private $reconnect = false; //是否重新连接
    /**
     * @var object
     * @see new Redis()
     */
    private $handle = '';
    private $auth = null; //是否有密码验证,不为null,需要验证
    private $prefix='session_PHPSESSIONID'; //session_name
    private $config = array(
        'SAVE_HANDLE'=>'Redis',
        'HOST'=>'127.0.0.1',
        'PORT'=>6379,
        'AUTH'=>null,    //是否有用户验证，默认无密码验证。如果不是为null，则为验证密码
        'TIMEOUT'=>0,   //连接超时
        'RESERVED'=>null,
        'RETRY_INTERVAL'=>100,  //单位是 ms 毫秒
        'RECONNECT'=>false, //连接超时是否重连  默认不重连
    );

    public function __construct($config=array())
    {
        if (!empty($config)) $this->config = array_merge($this->config,$config);
        $this->saveHandle = $this->config['SAVE_HANDLE'];
        $this->reconnect = $this->config['RECONNECT'];
        $this->auth = $this->config['AUTH'];
    }

    /**
     * Redis连接
     * @return object
     * @throws RedisException
     */
    public function redisConnect()
    {
        try{
            $this->handle = new Redis();
        }catch (RedisException $exception){
            throw $exception;
        }

        /**
         * 判断是否需要重连
         */
        if (!$this->reconnect){
            $this->handle->connect($this->config['HOST'],$this->config['PORT'],$this->config['TIMEOUT']);
        }else{
            $this->handle->connect($this->config['HOST'],$this->config['PORT'],$this->config['TIMEOUT'],$this->config['RESERVED'],$this->config['RETRY_INTERVAL']);
        }
        /**
         * 是否有密码验证
         */
        if (!is_null($this->auth)){
            $this->handle->auth($this->auth);
        }
        return true;
    }

    /**
     * session_start触发时调用此函数
     * @param string $savePath
     * @param string $name
     * @return bool
     * @see SessionHandlerInterface::open()
     */
    public function open($savePath,$name)
    {
        $this->redisConnect();
        return true;
    }

    /**
     * 关闭当前session
     * @return bool
     * @see SessionHandlerInterface::close()
     */
    public function close()
    {
        return true;
    }

    /**
     * 读取session数据
     * @param string $sessionID
     * @return mixed
     */
    public function read($sessionID)
    {
        //构造session键名
        $key = $this->prefix.':'.$sessionID;
        //获取sessionID的数据
        $result = $this->handle->hGet($key,'data');
        //更新时间
        $this->handle->hSet($key,'last_time',time());
        return $result;
    }

    /**
     * 写入session
     * @param string $sessionID
     * @param string $sessionData
     * @return bool
     */
    public function write($sessionID, $sessionData)
    {
        $key = $this->prefix.':'.$sessionID;
        //检查是否存在
        if (!$this->handle->exists($key)){
            $this->handle->hSet($key,'last_time',time());
        }else{
            $this->handle->hMset($key,array('last_time'=>time(),'data'=>$sessionData));
        }
        return true;
    }

    /**
     * 销毁session
     * @param string $sessionID
     */
    public function destroy($sessionID)
    {
        $key = $this->prefix.':'.$sessionID;
        $this->handle->hDel($key,'data');
    }

    /**
     * 清除session垃圾,也就是清除过期的session
     * 该函数是基于php.ini中的配置选项
     * session.gc_divisor, session.gc_probability 和 session.gc_lifetime所设置的值的
     * @param int $maxLifeTime
     */
    public function gc($maxLifeTime)
    {
        $keys = $this->handle->keys($this->prefix.'*');
        $now = time();
        foreach ($keys as $key) {
            $lastTime = $this->handle->hGet($key,'last_time');
            /*
             * 查看当前时间和最后的更新时间的时间差是否超过最大生命周期
             */
            if (($now-$lastTime) > $maxLifeTime){
                //超过了最大生命周期时间 则删除该key
                $this->handle->del($key);
            }
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Livfer
 * Desc:
 * Date: 2017/9/19
 * Time: 16:43
 */


class SessionFactory
{
    public $config;
    private $sessionHandler;

    public function __construct(SessionHandlerInterface $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
    }

    public function open($savePath,$name)
    {
        $this->sessionHandler->open($savePath,$name);
    }

    public function close()
    {
        $this->sessionHandler->close();
    }

    public function read($sessionID)
    {
        $this->sessionHandler->read($sessionID);
    }

    public function write($sessionID,$data)
    {
        
    }

    public function destroy($sessionID)
    {
        
    }

    public function gc($maxLifeTime)
    {
        
    }
}
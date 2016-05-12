<?php
namespace Druidvav\MemcacheBundle\Memcache;

use Clickalicious\Memcached\Client;

class LoggedClient extends Client
{
    protected $requests = [ ];

    public function send($command, $data = '')
    {
        $start = microtime(true);

        $result = parent::send($command, $data);
        $this->requests[] = [
            'start' => $start,
            'time' => microtime(true) - $start,
            'name' => $command,
            'arguments' => $data,
            'result' => $result
        ];
        return $result;
    }

    public function getLoggedCalls()
    {
        return $this->requests;
    }
}

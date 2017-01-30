<?php

namespace App\Components\ExternalServices;

use Liuggio\StatsdClient\Sender\SocketSender;
use Liuggio\StatsdClient\Service\StatsdService as SService;
use Liuggio\StatsdClient\StatsdClient;

class StatsdService
{

    public $hitsKey = 'hits';
    public $failedKey = 'failed';

    protected $prefix = '';

    protected $service = null;

    /**
     * RemoteSession constructor.
     */
    public function __construct()
    {
        $host = config('log.statsd.host');
        $port = config('log.statsd.port');
        $this->prefix = config('log.statsd.prefix');

        if ($host && $port) {
            $this->service = new SService(new StatsdClient(new SocketSender($host, $port)));
        }
    }

    /**
     * @param string $actionName
     */
    public function registerHit(string $actionName)
    {
        $this->registerAction($this->prefix . '_' . $this->hitsKey, $actionName);
    }

    /**
     * @param string $actionName
     */
    public function registerFailed(string $actionName)
    {
        $this->registerAction($this->prefix . '_' . $this->failedKey, $actionName);
    }

    /**
     * @param string $status
     * @param string $actionName
     */
    protected function registerAction(string $status, string $actionName)
    {
        list($controller, $method) = explode('@', $actionName);

        $controller = str_replace('\\', '.', $controller);

        if ($controller) {
            if ($method) {
                $this->incrementStatus($status, $controller . '.' . $method);
            }
        }

        if ($this->service) {
            $this->service->flush();
        }
    }

    /**
     * @param string $status
     * @param string $key
     */
    protected function incrementStatus(string $status, string $key)
    {
        if ($this->service) {
            $this->service->increment($status . '.' . $key);
        }
    }
}
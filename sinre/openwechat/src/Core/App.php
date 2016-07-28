<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/25
 * Time: 15:43
 */
namespace OpenWechat\Core;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use OpenWechat\Core\Helper\XmlHelper;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

class App extends Container
{
    /**
     * 初始化参数允许的数组键
     *
    */
    protected static $valid_config_key = [
        'appid',
        'token',
        'encodingAesKey',
        'cache_dir',
        'appsecret',
    ];
    /**
     * Service Providers.
     *
     * @var array
     */
    protected $providers = [
        ServiceProviders\ServerServiceProvider::class,
        ServiceProviders\AuthServiceProvider::class,
        ServiceProviders\AuthorizerServiceProvider::class,
    ];

    /**
     * Application constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct();

        $config = $this->filterConfig($config);
        $config['ticketKey'] = 'sinre.openwechat.verifyticket.';
        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        if ($this['config']['debug']) {
            error_reporting(E_ALL);
        }

        $this->registerProviders();
        $this->registerBase();

        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));
    }

    protected function filterConfig($config)
    {
        foreach($config as $key => $val){
            if(!in_array($key, self::$valid_config_key)){
                unset($config[$key]);
            }
        }
        return $config;
    }

    protected function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    protected function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };

        if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof CacheInterface) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache($this['config']->get('cache_dir', sys_get_temp_dir()));
            };
        }

        $this['xml'] = function(){
            return new XmlHelper();
        };

    }

    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/26
 * Time: 12:32
 */
namespace OpenWechat\Core;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use OpenWechat\Core\Exceptions\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    private $_http;
    protected $cache;

    protected function getCacheHandler()
    {
        if(!$this->cache instanceof Cache){
            $this->cache = new FilesystemCache(sys_get_temp_dir());
        }
        return $this->cache;
    }

    protected function getHttp()
    {
        if(!$this->_http){
            $this->_http = new Http();
        }
        //TODO:暂时注释，有时间研究一下
        /*
        if (count($this->_http->getMiddlewares()) === 0) {
            $this->registerHttpMiddlewares();
        }
        */

        return $this->_http;
    }

    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();

        $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));

        $this->checkAndThrow($contents);

        return new Collection($contents);
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     *
     * @throws \OpenWechat\Core\Exceptions\HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        if (isset($contents['errcode']) && 0 !== $contents['errcode']) {
            if (empty($contents['errmsg'])) {
                $contents['errmsg'] = 'Unknown';
            }

            throw new HttpException($contents['errmsg'], $contents['errcode']);
        }
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->_http->addMiddleware($this->retryMiddleware());
        // access token
        $this->_http->addMiddleware($this->accessTokenMiddleware());
    }

    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to 2
            if ($retries <= 2 && $response && $body = $response->getBody()) {
                // Retry on server errors
                if (stripos($body, 'errcode') && (stripos($body, '40001') || stripos($body, '42001'))) {
                    $field = $this->accessToken->getQueryName();
                    $token = $this->accessToken->getToken(true);

                    $request->withUri($newUri = Uri::withQueryValue($request->getUri(), $field, $token));
                    return true;
                }
            }

            return false;
        });
    }

    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->accessToken) {
                    return $handler($request, $options);
                }

                $field = $this->accessToken->getQueryName();
                $token = $this->accessToken->getToken();

                $request = $request->withUri(Uri::withQueryValue($request->getUri(), $field, $token));

                return $handler($request, $options);
            };
        };
    }
}
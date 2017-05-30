<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_APIBoost
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\APIBoost\Model;

use Magento\Framework\App\RequestInterface;
use MSP\APIBoost\Api\CacheManagementInterface;
use Magento\Framework\Webapi\Rest\Response;
use MSP\APIBoost\Api\TagProcessorInterface;
use Zend\Http\Headers;

class CacheManagement implements CacheManagementInterface
{
    const CACHE_TTL = 86400;
    const BASE_PATH = '/rest';

    /**
     * @var CacheType
     */
    private $cacheType;

    /**
     * @var array
     */
    private $paths;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var array
     */
    private $ttls;

    protected $matchedCode = null;

    public function __construct(
        Response $response,
        CacheType $cacheType,
        array $paths = [],
        array $ttls = [],
        array $tags = []
    ) {
        $this->cacheType = $cacheType;
        $this->paths = $paths;
        $this->response = $response;
        $this->tags = $tags;
        $this->ttls = $ttls;
    }

    /**
     * Return true if can cache this request
     * @param RequestInterface $request
     * @return bool
     */
    public function canCacheRequest(\Magento\Framework\App\RequestInterface $request)
    {
        // Make sure it is a rest-API call (at this level we cannot rely on detected area)
        $uriPath = $request->getRequestUri();
        if (strpos($uriPath, static::BASE_PATH . '/') !== 0) {
            return false;
        }

        // Remove /rest prefix
        $uriPath = substr($uriPath, strlen(static::BASE_PATH));

        // Not GET calls should not be cached
        if (strtoupper($request->getMethod()) != 'GET') {
            return false;
        }

        foreach ($this->paths as $code => $path) {
            if (preg_match('/' . $path . '/i', $uriPath)) {
                $this->matchedCode = $code;
                return true;
            }
        }

        return false;
    }

    /**
     * Get cache key from request
     * @param RequestInterface $request
     * @return string
     */
    protected function getCacheKey(\Magento\Framework\App\RequestInterface $request)
    {
        return md5(serialize([
            $request->getMethod(),
            $request->getRequestUri(),
            [
                $request->getHeader('Content-Type'),
                $request->getHeader('Authorization'),
            ]
        ]));
    }

    /**
     * Get a cache response or false if not existing
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|false
     */
    public function getCacheResult(\Magento\Framework\App\RequestInterface $request)
    {
        $cacheKey = $this->getCacheKey($request);

        if (!$this->cacheType->test($cacheKey)) {
            return false;
        }

        $cacheData = unserialize($this->cacheType->load($cacheKey));

        $this->response->setHttpResponseCode($cacheData['code']);
        $this->response->setHeaders(Headers::fromString($cacheData['headers']));
        $this->response->setBody($cacheData['body']);

        return $this->response;
    }

    /**
     * Set a cache response for a request
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function setCacheResult(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $cacheKey = $this->getCacheKey($request);

        $responseBody = $response->getBody();
        $responseCode = $response->getStatusCode();
        $responseHeaders = $response->getHeaders()->toString();

        $cacheData = [
            'code' => $responseCode,
            'headers' => $responseHeaders,
            'body' => $responseBody,
        ];

        $tags = [CacheType::CACHE_TAG];
        foreach ($this->tags as $code => $tag) {
            /** @var $tag TagProcessorInterface */
            $newTags = $tag->getTags($request);
            foreach ($newTags as $newTag) {
                $tags[] = $newTag;
            }
        }

        $tags = array_unique($tags);

        $ttl = intval(isset($this->ttls[$this->matchedCode]) ? $this->ttls[$this->matchedCode] : static::CACHE_TTL);

        $this->cacheType->save(serialize($cacheData), $cacheKey, $tags, $ttl);
    }
}

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

namespace MSP\APIBoost\Plugin;

use Magento\Framework\App\RequestInterface;
use MSP\APIBoost\Api\CacheManagementInterface;

class AppInterfacePlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CacheManagementInterface
     */
    private $cacheManagement;

    public function __construct(
        CacheManagementInterface $cacheManagement,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->cacheManagement = $cacheManagement;
    }

    public function aroundLaunch(\Magento\Framework\AppInterface $subject, \Closure $proceed)
    {
        $request = $this->request;

        if ($this->cacheManagement->canCacheRequest($request)) {
            $response = $this->cacheManagement->getCacheResult($request);
            if (!$response) {
                /** @var \Magento\Framework\App\ResponseInterface $response */
                $response = $proceed();
                $this->cacheManagement->setCacheResult($request, $response);
            }
        } else {
            /** @var \Magento\Framework\App\ResponseInterface $response */
            $response = $proceed($request);
        }

        return $response;
    }
}

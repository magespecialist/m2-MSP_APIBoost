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

namespace MSP\APIBoost\Api;

interface CacheManagementInterface
{
    /**
     * Return true if can cache this request
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function canCacheRequest(\Magento\Framework\App\RequestInterface $request);

    /**
     * Get a cache response or false if not existing
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|false
     */
    public function getCacheResult(\Magento\Framework\App\RequestInterface $request);

    /**
     * Set a cache response for a request
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function setCacheResult(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    );
}

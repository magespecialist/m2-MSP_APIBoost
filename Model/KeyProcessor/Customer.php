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

namespace MSP\APIBoost\Model\KeyProcessor;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Integration\Model\Oauth\TokenFactory;
use MSP\APIBoost\Api\KeyProcessorInterface;

class Customer implements KeyProcessorInterface
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        TokenFactory $tokenFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Return a list of cache keys for a request
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array
     */
    public function getKeys(\Magento\Framework\App\RequestInterface $request)
    {
        $authHeader = $request->getHeader('Authorization');
        if (preg_match('/^bearer\s+\"?(.+?)\"?\s*$/i', $authHeader, $matches)) {
            $token = $this->tokenFactory->create()->loadByToken($matches[1]);

            if ($token->getId() && !$token->getRevoked()) {
                $customer = $this->customerRepository->getById($token->getCustomerId());

                return [$customer->getGroupId()];
            }
        }

        return [0];
    }
}

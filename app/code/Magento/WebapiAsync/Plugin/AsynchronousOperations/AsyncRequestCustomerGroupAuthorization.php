<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\AsynchronousOperations;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\AsynchronousOperations\Model\MassSchedule;

/**
 * Plugin to validate anonymous request for asynchronous operations containing group id.
 */
class AsyncRequestCustomerGroupAuthorization
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     *
     * @param AuthorizationInterface|null $authorization
     */
    public function __construct(
        AuthorizationInterface $authorization = null
    ) {
        $objectManager = ObjectManager::getInstance();
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
    }

    /**
     * Validate groupId for anonymous request
     *
     * @param MassSchedule $massSchedule
     * @param string $topic
     * @param array $entitiesArray
     * @param string|null $groupId
     * @param string|null $userId
     * @return void
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePublishMass(
        MassSchedule $massSchedule,
        string       $topic,
        array        $entitiesArray,
        string       $groupId = null,
        string       $userId = null
    ): void {
        foreach ($entitiesArray as $entityParams) {
            foreach ($entityParams as $customer) {
                if (is_object($customer)) {
                    $groupId = $customer->getGroupId();
                    if (isset($groupId) && !$this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
                        $params = ['resources' => self::ADMIN_RESOURCE];
                        throw new AuthorizationException(
                            __("The consumer isn't authorized to access %resources.", $params)
                        );
                    }
                }
            }
        }
    }
}

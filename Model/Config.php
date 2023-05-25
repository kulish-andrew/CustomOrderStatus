<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config
{
    private const XML_PATH_ORDER_STATUS_ALLOWED_USERS = 'order_status/general/allowed_users';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getAllowedUsersId(): array
    {
        $allowedUsers = $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_STATUS_ALLOWED_USERS,
            ScopeInterface::SCOPE_STORE
        );

        if ($allowedUsers) {
            return explode(',', $allowedUsers);
        }

        return [];
    }
}

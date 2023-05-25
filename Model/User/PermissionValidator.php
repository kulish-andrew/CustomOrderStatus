<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Model\User;

use Magento\Backend\Model\Auth\Session;
use OpenTag\CustomOrderStatus\Model\Config;

/**
 * Class PermissionValidator
 */
class PermissionValidator
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Session
     */
    private Session $authSession;

    /**
     * @param Session $authSession
     * @param Config $config
     */
    public function __construct(
        Session $authSession,
        Config  $config
    ) {
        $this->authSession = $authSession;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isUserHasPermission(): bool
    {
        $adminUser = $this->authSession->getUser();
        if ($adminUser) {
            $allowedUsersId = $this->config->getAllowedUsersId();
            $adminUserId = $adminUser->getId();

            return in_array($adminUserId, $allowedUsersId, true);
        }

        return false;
    }
}

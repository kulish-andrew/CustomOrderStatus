<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

/**
 * Class UsersList
 */
class UsersList implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $userCollectionFactory;

    /**
     * @param CollectionFactory $userCollectionFactory
     */
    public function __construct(CollectionFactory $userCollectionFactory)
    {
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $adminUsers = [];
        $userCollection = $this->userCollectionFactory->create();
        foreach ($userCollection as $user) {
            $adminUsers[] = [
                'value' => $user->getId(),
                'label' => $user->getName()
            ];
        }

        return $adminUsers;
    }
}

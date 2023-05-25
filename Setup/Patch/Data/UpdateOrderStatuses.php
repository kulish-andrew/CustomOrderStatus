<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Setup\Patch\Data;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateOrderStatuses
 */
class UpdateOrderStatuses implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * AddPaypalOrderStates constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', ['to' => date("Y-m-d")], 'lt')
            ->create();
        $ordersList = $this->orderRepository->getList($searchCriteria);

        try {
            foreach ($ordersList->getItems() as $order){
                $order->setStatus(AddOpenTagOrderStatuses::STATUS_CODE);
                $this->orderRepository->save($order);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddOpenTagOrderStatuses::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

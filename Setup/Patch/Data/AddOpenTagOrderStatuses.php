<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddOpenTagOrderStatuses
 */
class AddOpenTagOrderStatuses implements DataPatchInterface
{
    public const STATUS_CODE = 'opentag';
    public const STATUS_LABEL = 'OpenTag';
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * AddPaypalOrderStates constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $data[] = ['status' => self::STATUS_CODE, 'label' => self::STATUS_LABEL];
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            $data
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

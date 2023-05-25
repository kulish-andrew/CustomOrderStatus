<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Backend\Model\UrlInterface;
use OpenTag\CustomOrderStatus\Model\User\PermissionValidator;

/**
 * CLass ViewPlugin
 */
class ViewPlugin
{
    private const SKIPPED_ORDER_STATES = [
        'canceled' => true,
        'closed' => true,
        'complete' => true
    ];

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var PermissionValidator
     */
    private PermissionValidator $permissionValidator;

    /**
     * @param UrlInterface $urlBuilder
     * @param PermissionValidator $permissionValidator
     */
    public function __construct(
        UrlInterface        $urlBuilder,
        PermissionValidator $permissionValidator
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->permissionValidator = $permissionValidator;
    }

    /**
     * @param OrderView $subject
     * @return void
     */
    public function beforeSetLayout(OrderView $subject): void
    {
        if ($this->isButtonVisible($subject)) {
            $subject->addButton(
                'order_custom_button',
                [
                    'label' => __('Set Opentag Order Status'),
                    'class' => __('opentag-status action-default scalable action-secondary'),
                    'id' => 'order-view-opentag-status-button',
                    'onclick' => 'setLocation(\'' . $this->getActionUrl($subject) . '\')'
                ]
            );
        }
    }

    /**
     * @param OrderView $subject
     * @return bool
     */
    private function isButtonVisible(OrderView $subject): bool
    {
        $orderState = $subject->getOrder()->getState();
        return $this->permissionValidator->isUserHasPermission() && $this->isOrderFinished($orderState);
    }

    /**
     * @param OrderView $subject
     * @return string
     */
    private function getActionUrl(OrderView $subject): string
    {
        return $this->urlBuilder->getRouteUrl(
            'sales/order/view',
            [
                'order_id' => $subject->getOrderId(),
                'status_update' => true,
                'key' => $this->urlBuilder->getSecretKey('sales', 'order', 'view'),
            ]
        );
    }

    /**
     * @param string $orderState
     * @return bool
     */
    private function isOrderFinished(string $orderState): bool
    {
        $skippedOrderStates = $this->getSkippedOrderStates();
        return !isset($skippedOrderStates[$orderState]) || !$skippedOrderStates[$orderState];
    }

    /**
     * @return true[]
     */
    private function getSkippedOrderStates(): array
    {
        return self::SKIPPED_ORDER_STATES;
    }
}

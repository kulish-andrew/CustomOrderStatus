<?php
declare(strict_types=1);

namespace OpenTag\CustomOrderStatus\Plugin\Sales\Controller\Adminhtml\Order;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Magento\Framework\Controller\ResultFactory;
use OpenTag\CustomOrderStatus\Model\User\PermissionValidator;
use OpenTag\CustomOrderStatus\Setup\Patch\Data\AddOpenTagOrderStatuses;
use Psr\Log\LoggerInterface;

/**
 * Class ViewPlugin
 */
class ViewPlugin
{
    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @var PermissionValidator
     */
    private PermissionValidator $permissionValidator;

    /**
     * @param ResultFactory $resultFactory
     * @param UrlInterface $urlBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param PermissionValidator $permissionValidator
     */
    public function __construct(
        ResultFactory            $resultFactory,
        UrlInterface             $urlBuilder,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface         $messageManager,
        LoggerInterface          $logger,
        Registry                 $coreRegistry,
        PermissionValidator      $permissionValidator
    ) {
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->coreRegistry = $coreRegistry;
        $this->permissionValidator = $permissionValidator;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return ResultInterface
     */
    public function aroundExecute(View $subject, callable $proceed): ResultInterface
    {
        $orderId = (int)$subject->getRequest()->getParam('order_id');
        $statusUpdate = $subject->getRequest()->getParam('status_update');
        if ($statusUpdate && $orderId) {
            $this->updateOrderStatus($orderId);
        }

        $result = $proceed();

        $order = $this->coreRegistry->registry('current_order');
        $orderStatus = $order->getStatus();
        if (
            $orderStatus === AddOpenTagOrderStatuses::STATUS_CODE
            && !$this->permissionValidator->isUserHasPermission()
        ) {
            return $this->getRedirect();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->urlBuilder->getRouteUrl(
            'sales/order/index',
            [
                'key' => $this->urlBuilder->getSecretKey('sales', 'order', 'index'),
            ]
        );
    }

    /**
     * @param int $orderId
     * @return void
     */
    private function updateOrderStatus(int $orderId): void
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $order->setStatus(AddOpenTagOrderStatuses::STATUS_CODE);
            $this->orderRepository->save($order);
            $this->messageManager->addSuccessMessage(__('Order status was updated'));
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @return ResultInterface
     */
    private function getRedirect(): ResultInterface
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($this->getRedirectUrl());
        $this->messageManager->addErrorMessage(__('You don\'t have permission to see this order details.'));
        return $redirect;
    }
}

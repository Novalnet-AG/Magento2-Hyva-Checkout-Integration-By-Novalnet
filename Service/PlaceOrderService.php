<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Novalnet\HyvaCheckout\Service;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Novalnet\Payment\Logger\NovalnetLogger;
use Novalnet\Payment\Helper\Data;
use Magento\Sales\Model\Order;

/**
 * Service class to place and redirect orders for Novalnet payment methods.
 */
class PlaceOrderService extends AbstractPlaceOrderService
{
    /** @var UrlInterface */
    private UrlInterface $url;

    /** @var NovalnetLogger */
    private NovalnetLogger $novalnetLogger;

    /** @var Data */
    protected Data $novalnetHelper;

    /** @var Order  */
    private Order $salesOrderModel;

    /**
     * Constructor
     *
     * @param CartManagementInterface $cartManagement
     * @param UrlInterface            $url
     * @param NovalnetLogger          $novalnetLogger
     * @param Data                    $novalnetHelper
     * @param Order                   $salesOrderModel
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        UrlInterface $url,
        NovalnetLogger $novalnetLogger,
        Data $novalnetHelper,
        Order $salesOrderModel
    ) {
        parent::__construct($cartManagement);
        $this->url             = $url;
        $this->novalnetLogger  = $novalnetLogger;
        $this->novalnetHelper  = $novalnetHelper;
        $this->salesOrderModel = $salesOrderModel;
    }

    /**
     * Determine if the order can be placed.
     *
     * @return bool
     */
    public function canPlaceOrder(): bool
    {
        return true;
    }

    /**
     * Determine if the payment method supports redirection.
     *
     * @return bool
     */
    public function canRedirect(): bool
    {
        return true;
    }

    /**
     * Get the URL to redirect the customer after placing the order.
     *
     * @param Quote    $quote
     * @param int|null $orderId
     * @return string
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $this->novalnetLogger->notice('Novalnet HyvaCheckout place order init with quote ID: ' . $quote->getId());
        $order   = $this->salesOrderModel->load($orderId);
        $payment = $order->getPayment();

        $additionalData = $this->novalnetHelper->getPaymentAdditionalData(
            $payment->getAdditionalData()
        );

        $this->novalnetLogger->notice('Order ID retrieved: ' . $orderId);
        $this->novalnetLogger->notice('Order loaded successfully: ' . $order->getIncrementId());

        if (!empty($additionalData) && $additionalData['NnPaymentProcessMode'] !== 'redirect') {
            $this->novalnetLogger->notice('Novalnet HyvaCheckout order processed as direct with order ID: ' . $orderId);
            return parent::getRedirectUrl($quote, $orderId);
        }

        if (!empty($additionalData['NnRedirectURL'])) {
            $this->novalnetLogger->notice('Novalnet HyvaCheckout order processed as re-direct with order ID: ' . $orderId);
            // Set the order status to pending_payment before redirecting
            $order->setState(Order::STATE_PENDING_PAYMENT)
                ->setStatus(Order::STATE_PENDING_PAYMENT)
                ->save();
            $order->addStatusHistoryComment(__('Customer was redirected to Novalnet'))
                ->save();

            $this->novalnetLogger->notice('Order status and comments updated successfully');

            return $additionalData['NnRedirectURL'];
        }

        return $this->url->getUrl('checkout/cart');
    }
}

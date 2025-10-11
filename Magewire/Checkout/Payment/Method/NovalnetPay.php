<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Novalnet\HyvaCheckout\Magewire\Checkout\Payment\Method;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magewirephp\Magewire\Component\Form;
use Novalnet\Payment\Model\NovalnetRepository;
use Novalnet\Payment\Helper\Data as NovalnetHelper;
use Novalnet\Payment\Model\NNConfig;

class NovalnetPay extends Form
{
    /** @var CheckoutSession */
    private CheckoutSession $checkoutSession;

    /** @var NovalnetRepository */
    private NovalnetRepository $novalnetRepository;

    /** @var NovalnetHelper */
    private NovalnetHelper $novalnetHelper;

    /** @var NNConfig */
    private NNConfig $novalnetConfig;

    /** @var string URL for the Novalnet payment iframe */
    public string $iframeUrl = '';

    /** @var array<string,string> Line items to display in the widget */
    public array $lineItems = [];

    /** @var string Currently chosen payment code */
    public string $NNSelectedPayment = '';

    /** @var array<string,mixed> Holds updated quote data (addresses, amount) */
    public array $updatedData = [];

    /** @var bool Whether to show payment method icons */
    public bool $showIcons = true;

    /**
     * @param CheckoutSession         $checkoutSession
     * @param NovalnetRepository      $novalnetRepository
     * @param NovalnetHelper          $novalnetHelper
     * @param NNConfig                $novalnetConfig
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        NovalnetRepository $novalnetRepository,
        NovalnetHelper $novalnetHelper,
        NNConfig $novalnetConfig
    ) {
        $this->checkoutSession    = $checkoutSession;
        $this->novalnetRepository = $novalnetRepository;
        $this->novalnetHelper     = $novalnetHelper;
        $this->novalnetConfig     = $novalnetConfig;
    }

    /**
     * Boot lifecycle method: reads config for showing icons.
     */
    public function boot(): void
    {
        $this->showIcons = (bool) $this->novalnetConfig->getHyvaCheckoutConfig(
            'component/payment/show_method_icons'
        );
    }

    /**
     * Save the currently selected payment method into session.
     *
     * @param string $payment The payment code to save
     */
    public function setCurrentPayment(string $payment): void
    {
        if ($this->checkoutSession->getNNSelectedPayment() !== null) {
            $this->checkoutSession->unsNNSelectedPayment();
        }
        $this->checkoutSession->setNNSelectedPayment($payment);
    }

    /**
     * Fetch and format quote details (addresses, amount) for JS.
     */
    public function getQuoteDetails(): void
    {
        $quote           = $this->checkoutSession->getQuote();
        $billingAddress  = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        $data = [];

        if ($billingAddress) {
            $data['amount'] = $this->novalnetHelper->getFormattedAmount(
                $quote->getBaseGrandTotal()
            );
            $data['billing_address'] = [
                'street'       => $this->novalnetHelper->getStreet($billingAddress),
                'city'         => $billingAddress->getCity(),
                'zip'          => $billingAddress->getPostcode(),
                'country_code' => $billingAddress->getCountryId(),
            ];
        }

        if ($shippingAddress) {
            $billingStreet  = $this->novalnetHelper->getStreet($billingAddress);
            $shippingStreet = $this->novalnetHelper->getStreet($shippingAddress);

            if ($quote->isVirtual()
                || (
                    $billingStreet === $shippingStreet
                    && $billingAddress->getCity() === $shippingAddress->getCity()
                    && $billingAddress->getPostcode() === $shippingAddress->getPostcode()
                    && $billingAddress->getCountryId() === $shippingAddress->getCountryId()
                )
            ) {
                $data['same_as_billing'] = 1;
            } else {
                $data['shipping_address'] = [
                    'street'       => $shippingStreet,
                    'city'         => $shippingAddress->getCity(),
                    'zip'          => $shippingAddress->getPostcode(),
                    'country_code' => $shippingAddress->getCountryId(),
                ];
            }
        }

        // Remove any empty array keys
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (empty($subValue)) {
                        unset($data[$key][$subKey]);
                    }
                }
                if (empty($data[$key])) {
                    unset($data[$key]);
                }
            } elseif (empty($value)) {
                unset($data[$key]);
            }
        }

        $this->updatedData = $data;
    }

    /**
     * Build an array of line items (subtotal, tax, discount, shipping) for JS.
     *
     * @return array<string,string>
     */
    public function getLineItems(): array
    {
        $quote     = $this->checkoutSession->getQuote();
        $lineItems = [];
        $discount  = 0;

        foreach ($quote->getAllVisibleItems() as $item) {
            $qty       = (int) $item->getQty();
            $unitPrice = (float) $item->getBasePrice();
            $rowTotal  = (float) $item->getBaseRowTotal();
            $discount  = (float) $item->getBaseDiscountAmount();

            $lineItems[] = [
                'label'  => sprintf('%s (%d x %.2F)', $item->getName(), $qty, $unitPrice),
                'type'   => 'SUBTOTAL',
                'amount' =>  round($rowTotal * 100),
            ];
        }

        $taxAmount = $quote->getShippingAddress()->getBaseTaxAmount();
        if ($taxAmount > 0) {
            $lineItems[] = [
                'label'  => 'Tax',
                'type'   => 'SUBTOTAL',
                'amount' =>  round($taxAmount * 100),
            ];
        }

        if ($discount > 0) {
            $lineItems[] = [
                'label'  => 'Discount',
                'type'   => 'SUBTOTAL',
                'amount' => '-' .  round(abs($discount) * 100),
            ];
        }

        if (!$quote->getIsVirtual()) {
            $shippingAmount = (float) $quote->getShippingAddress()->getBaseShippingAmount();
            if ($shippingAmount > 0) {
                $lineItems[] = [
                    'label'  => 'Shipping',
                    'type'   => 'SUBTOTAL',
                    'amount' => round($shippingAmount * 100),
                ];
            }
        }

        $this->lineItems = $lineItems;
        return $lineItems;
    }

    /**
     * Fetch the Novalnet iframe URL and currently chosen payment.
     */
    public function getIframeUrl(): void
    {
        $quoteId = (int) $this->checkoutSession->getQuoteId();
        if ($quoteId) {
            $result                  = $this->novalnetRepository->buildPayBylinkRequest($quoteId);
            $data                    = json_decode($result, true);
            $this->iframeUrl         = $data['result']['redirect_url'] ?? '';
            $this->NNSelectedPayment  = (string) $this->checkoutSession->getNNSelectedPayment();
        }
    }

    /**
     * Save the Novalnet payment response into the quote’s payment additional data.
     * @param mixed $data
     */
    public function setPaymentData($data): void
    {
        $quote   = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('novalnetPay_payment_data', $data);
        $this->checkoutSession->unsNNSelectedPayment();
        $quote->save();
    }
}

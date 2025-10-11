<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Novalnet\HyvaCheckout\Observer\SalesQuotePaymentImportDataBefore;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\PaymentInterface;

class SetAdditionalData implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var PaymentInterface $payment */
        $payment = $observer->getData('payment');
        /** @var DataObject $input */
        $input = $observer->getData('input');
        /** @var array $additionalData */
        $additionalData = $input->getData('additional_data');

        $additionalData['novalnetPay_payment_data'] = $payment->getAdditionalInformation('novalnetPay_payment_data');
        $input->setData('additional_data', $additionalData);
    }
}

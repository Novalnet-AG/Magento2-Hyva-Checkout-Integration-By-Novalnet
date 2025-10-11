<p align="center">
    <img src="https://www.novalnet.de/images/nn-logo-200x65.png" />
</p>
<h1 align="center">Magento 2 Hyvä Checkout Integration By Novalnet </h1>

# About Novalnet Payments

<a href="https://www.novalnet.de/"> Novalnet </a> is a globally recognized full-service payment provider headquartered in Germany, offering a comprehensive payment platform that supports international and local payment methods. With a strong focus on automation, security, and compliance, Novalnet simplifies the entire payment process - from checkout to settlement - for online merchants and marketplaces.

## Key Highlights:
* Supports over 150 international and local payment methods including credit/debit cards, SEPA direct debit, PayPal, Apple Pay, Google Pay, and more.
* Fully compliant with PCI DSS, PSD2, and GDPR regulations.
* Provides automated fraud prevention, risk management, real-time transaction monitoring, and intelligent payment routing.
* Seamless integration and reporting with automated invoicing, settlement, and reconciliation tools.
* Trusted by businesses across Europe for secure, scalable, and compliant payment processing.

# About this repository
This repository provides the Novalnet Hyvä Checkout Integration for Magento 2, enabling Novalnet Payment Gateway methods within the Hyvä Checkout plugin.

It requires the following dependencies:

- **The base Novalnet Payment Gateway module**
- **The Hyvä Checkout plugin**

For details on the base functionality of Magento 2 payment gateway. refer to https://github.com/Novalnet-AG/magento2-payment-integration-novalnet

# Requirements
The extension has been tested on a Magento environment with
* Magento 13.4.0
* PHP 8.2, 8.3 and 8.4
* Magento 2.4.8-p2
* Hyvä Theme 1.3.15, 1.3.16 and 1.3.17
* Hyvä Checkout 1.1.3

# Integrated payment methods
- Direct Debit SEPA
- Direct Debit ACH
- Credit/Debit Cards 
- Apple Pay
- Google Pay
- Invoice 
- Prepayment
- Invoice with payment guarantee
- Direct Debit SEPA with payment guarantee
- iDEAL
- Przelewy24
- eps
- Instalment by Invoice
- Instalment by Direct Debit SEPA
- PayPal
- PostFinance Card
- PostFinance E-Finance
- Bancontact
- Multibanco
- Online bank transfer
- Alipay
- WeChat Pay
- Trustly
- Blik
- Payconiq
- TWINT

## Installation via Composer

#### Follow the below steps and run each command from the shop root directory
 ##### 1. Run the below command to install the payment module
 ```
 composer require novalnet/module-payment
 ```
 ##### 2. Run the below command to upgrade the payment module
 ```
 php bin/magento setup:upgrade
 ```
 ##### 3. Run the below command to re-compile the payment module
 ```
 php bin/magento setup:di:compile
 ```
 ##### 4. Run the below command to deploy static-content files like (images, CSS, templates and js files)
 ```
 php bin/magento setup:static-content:deploy -f
 ```

## Documentation & Support
For more information about the Magento 2 Hyvä Integration by Novalnet, please get in touch with us: <a href="mailto:sales@novalnet.de"> sales@novalnet.de </a> or +49 89 9230683-20<br>

Novalnet AG<br>
Zahlungsinstitut (ZAG)<br>
Gutenbergstraße 7<br>
D-85748 Garching<br>
Deutschland<br>
E-mail: sales@novalnet.de<br>
Tel: +49 89 9230683-20<br>
Web: www.novalnet.de

## Who is Novalnet AG?
<p>Novalnet AG is a <a href="https://www.novalnet.de/zahlungsinstitut"> leading financial service institution </a> offering payment gateways for processing online payments. Operating in the market as a full payment service provider Novalnet AG provides online merchants user-friendly payment integration with all major shop systems and self-programmed sites.</p> 
<p>Accept, manage and monitor payments all on one platform with one single contract!</p>
<p>Our SaaS engine is <a href="https://www.novalnet.de/pci-dss-zertifizierung"> PCI DSS </a> certified and designed to enable real-time risk management, secured payments via escrow accounts, efficient receivables management, dynamic member and subscription management, customized payment solutions for various business models (e.g. marketplaces, affiliate programs etc.) etc.</p>

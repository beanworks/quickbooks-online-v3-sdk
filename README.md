# PHP SDK for QuickBooks V3

[![Build Status](https://api.travis-ci.org/beanworks/quickbooks-online-v3-sdk.svg?branch=master)](https://travis-ci.org/beanworks/quickbooks-online-v3-sdk)

The PHP SDK for QuickBooks v3 is set of PHP classes that make it easier to call QuickBooks APIs.  Some of the features included in this SDK are as follows:

- Ability to perform single and batch processing of CRUD operations on all supported QuickBooks entities.
- Support for XML Request and Response format.
- Ability to configure app settings in the configuration file requiring no additional code change.
- Support for Gzip and Deflate compression formats to improve performance of QuickBooks API calls.
- Logging mechanisms for trace and request/response.
- Query Filters that enable you to retrieve QuickBooks entities whose properties meet specified criteria.
- Sparse Update to update writable properties specified in a request and leave the others unchanged.
- Change data that enables you to retrieve a list of entities modified during specified time points.

## Getting Started

#### Clone with Git

```shell
$ git clone git@github.com:beanworks/quickbooks-online-v3-sdk.git
```

#### Install with [Composer](https://getcomposer.org/)

```shell
$ wget http://getcomposer.org/composer.phar
# Install [beanworks/quickbooks-online-v3-sdk] from master branch
$ php composer.phar require beanworks/quickbooks-online-v3-sdk:dev-master
# Install Beanworks patched version
$ php composer.phar require beanworks/quickbooks-online-v3-sdk:3.0.0
# Install Intuit's original version
$ php composer.phar require beanworks/quickbooks-online-v3-sdk:2.0.5
```

#### Unit Test

```shell
$ git clone git@github.com:beanworks/quickbooks-online-v3-sdk.git
$ cd quickbooks-online-v3-sdk
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
$ ./phpunit
```

## BeanworksAP

[BeanworksAP](https://beanworks.com) proudly integrates with QuickBooks Online and QuickBooks Desktop.

## Beanworks Solutions Inc.

**Automate your Accounts Payable with Beanworks**

BeanWorksAP is a cloud-based, automated accounts payable solution that you use via a secure Internet connection. There is no need to commit to and purchase a software package, have it configured, installed, and maintained by an IT department — we offer simple, subscription-based pricing and easy setup with no long term contract to sign.

- Work anywhere with access to your data from any device
- Regular seamless upgrades with new features
- Information is safe, secure and backed-up regularly
- Super-responsive and knowledgeable customer support

Copyright &copy; 2015 [Beanworks Solutions Inc.](https://beanworks.com/)

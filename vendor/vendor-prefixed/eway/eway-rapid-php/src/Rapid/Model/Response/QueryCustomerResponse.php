<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Response;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Customer;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\HasCustomersTrait;

/**
 * Class QueryCustomerResponse.
 *
 * @property Customer[] Customers
 * @property array      Errors
 */
class QueryCustomerResponse extends AbstractResponse
{
    use HasCustomersTrait;

    protected $fillable = [
        'Customers',
        'Errors',
    ];
}

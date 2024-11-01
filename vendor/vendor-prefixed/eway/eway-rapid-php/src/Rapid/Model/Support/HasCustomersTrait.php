<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Customer;

/**
 * Trait HasCustomersTrait
 */
trait HasCustomersTrait
{
    /**
     * @param array $customers
     *
     * @return $this
     */
    public function setCustomersAttribute($customers)
    {
        if (!is_array($customers)) {
            throw new \InvalidArgumentException('Customers must be an array');
        }

        foreach ($customers as $key => $customer) {
            if (!($customer instanceof Customer)) {
                $customers[$key] = new Customer($customer);
            }
        }

        $this->attributes['Customers'] = $customers;

        return $this;
    }
}

<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait HasCustomerTrait.
 *
 * @property Customer $Customer Customer details (name address token etc)
 */
trait HasCustomerTrait
{
    /**
     * @param mixed $customer
     *
     * @return $this
     */
    public function setCustomerAttribute($customer)
    {
        $this->validateInstance('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Customer', 'Customer', $customer);

        return $this;
    }
}

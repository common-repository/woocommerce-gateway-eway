<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait HasPaymentTrait.
 *
 * @property Payment $Payment Payment details (amount, currency and invoice information)
 */
trait HasPaymentTrait
{
    /**
     * @param mixed $payment
     *
     * @return $this
     */
    public function setPaymentAttribute($payment)
    {
        $this->validateInstance('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Payment', 'Payment', $payment);

        return $this;
    }
}

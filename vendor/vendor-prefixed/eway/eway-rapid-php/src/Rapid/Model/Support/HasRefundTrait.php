<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait HasRefundTrait.
 */
trait HasRefundTrait
{
    /**
     * @param mixed $refund
     *
     * @return $this
     */
    public function setRefundAttribute($refund)
    {
        $this->validateInstance('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\RefundDetails', 'Refund', $refund);

        return $this;
    }
}

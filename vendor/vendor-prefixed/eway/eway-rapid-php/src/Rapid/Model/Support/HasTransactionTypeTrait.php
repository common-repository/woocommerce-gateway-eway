<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

/**
 * Trait HasTransactionTypeTrait.
 */
trait HasTransactionTypeTrait
{
    /**
     * @param string $transactionType
     *
     * @return $this
     */
    public function setTransactionTypeAttribute($transactionType)
    {
        // Handle version 40 and error response values
        if (!is_int($transactionType) && $transactionType != 'Unknown') {
            $this->validateEnum('Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Enum\TransactionType', 'TransactionType', $transactionType);
        }

        return $this;
    }
}

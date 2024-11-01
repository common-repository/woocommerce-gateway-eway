<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Transaction;

/**
 * Trait HasTransactionsTrait
 */
trait HasTransactionsTrait
{
    /**
     * @param array $transactions
     *
     * @return $this
     */
    public function setTransactionsAttribute($transactions)
    {
        if (!is_array($transactions)) {
            throw new \InvalidArgumentException('Transactions must be an array');
        }

        foreach ($transactions as $key => $transaction) {
            if (!($transaction instanceof Transaction)) {
                $transactions[$key] = new Transaction($transaction);
            }
        }

        $this->attributes['Transactions'] = $transactions;

        return $this;
    }
}

<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Response;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\HasTransactionsTrait;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Transaction;

/**
 * This response simply wraps the TransactionStatus type with the additional common fields required by a return type.
 *
 * @property string        $Errors       A comma separated list of any error encountered,
 *                                      these can be looked up using Rapid::getMessage().
 * @property Transaction[] $Transactions All transactions found
 */
class QueryTransactionResponse extends AbstractResponse
{
    use HasTransactionsTrait;

    protected $fillable = [
        'Transactions',
        'Errors',
        'Message',
    ];
}

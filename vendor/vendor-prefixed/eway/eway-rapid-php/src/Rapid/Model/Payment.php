<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model;

/**
 * Class Payment.
 *
 * @property int    $TotalAmount        The total amount to charge the card holder in
 *                                       this transaction in cents. e.g. 1000 = $10.00
 * @property string $InvoiceNumber      The merchant's invoice number
 * @property string $InvoiceDescription The merchant's invoice description
 * @property string $InvoiceReference   The merchant's invoice reference
 * @property string $CurrencyCode       The merchant's currency (e.g. AUD)
 */
class Payment extends AbstractModel
{
    protected $fillable = [
        'TotalAmount',
        'InvoiceNumber',
        'InvoiceDescription',
        'InvoiceReference',
        'CurrencyCode',
    ];
}

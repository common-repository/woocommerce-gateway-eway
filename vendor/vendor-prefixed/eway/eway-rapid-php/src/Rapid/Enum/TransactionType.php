<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Enum;

/**
 * This defines the type of the transaction, it is a very close mapping to the
 * bank accepted types, note the types Refund and Auth are missing as they are
 * handled using dedicated requests.
 */
abstract class TransactionType extends AbstractEnum
{
    const PURCHASE = 'Purchase';
    const RECURRING = 'Recurring';
    const MOTO = 'MOTO';
}

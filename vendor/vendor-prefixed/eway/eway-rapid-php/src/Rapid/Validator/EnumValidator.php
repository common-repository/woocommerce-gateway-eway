<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Validator;

use InvalidArgumentException;

/**
 * Class EnumValidator.
 */
abstract class EnumValidator
{
    /**
     * @param $class
     * @param $field
     * @param $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function validate($class, $field, $value)
    {
        $abstractEnum = 'Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Enum\AbstractEnum';
        if (!is_subclass_of($class, $abstractEnum)) {
            throw new InvalidArgumentException(sprintf('%s must extends %s', $class, $abstractEnum));
        }

        if (!call_user_func($class.'::isValidValue', $value, true)) {
            throw new InvalidArgumentException(call_user_func($class.'::getValidationMessage', $field));
        }

        return $value;
    }
}

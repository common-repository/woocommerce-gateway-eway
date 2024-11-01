<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 16-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model;

use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Contract\Arrayable;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\CanGetClassTrait;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\CanValidateEnumTrait;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\CanValidateInstanceTrait;
use Automattic\WooCommerce\Eway\Vendors\Eway\Rapid\Model\Support\HasAttributesTrait;

/**
 * Class AbstractModel.
 */
abstract class AbstractModel implements Arrayable
{
    use HasAttributesTrait, CanValidateInstanceTrait, CanValidateEnumTrait, CanGetClassTrait;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}

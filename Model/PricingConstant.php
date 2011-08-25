<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PricingConstantInterface;
use Vespolina\PricingBundle\Model\PricingElementInterface;
/**
 * PricingConstant represents a global constant which is used by the price calculation process.
 * From a functional perspective it is a global constant which can be used to easily
 * apply global discounts, increase all net values by a given factor, ..  If a pricing execution step
 * uses a pricing constant, modifying the pricing constant will have a direct impact on determined prices
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class PricingConstant implements  PricingConstantInterface
{
    protected $name;

    function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {

        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {

        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
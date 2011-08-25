<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PricingContextContainerInterface;
/**
 * @author Daniel Kucharski <daniel@xerias.be>
 */
interface PricingConstantInterface
{
    /**
     * Get name of the pricing element
     *
     * @abstract
     * @return void
     */
    function getName();

    /**
     * Get name of the pricing element
     *
     * @abstract
     * @return void
     */
    function getValue();

    /**
     * Set name of the pricing constant
     *
     * @abstract
     * @param $name
     * @return void
     */
    function setName($name);

    /**
     * Get value of the pricing element
     *
     * @abstract
     * @param  $value
     * @return void
     */
    function setValue($value);

}
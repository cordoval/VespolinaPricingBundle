<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PricingSetInterface;

/**
 * PricingDimensionInterface is a generic interface for handling pricing dimensions
 *
 * An example of a pricing dimension is "time"(period) or (product)"volume"
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
interface PricingDimensionInterface
{
    /**
     * Add dimension parameter
     *
     * @param  $name Dimension name
     * @param  $value Dimension value
     * @return void
     */
    function addParameter($name, $value);
    
    /**
     * Return the name of this pricing dimension
     * @return
     */
    function getName();

    /**
     * Get all the parameter names for this dimension
     *
     * @return array
     */
    function getParameterNames();

    /**
     * Return all parameters names and associated values
     *
     * @return array
     */
    function getParameters();

    /**
     * Get parameter value
     *
     * @param  $name
     * @return array
     */
    function getParameter($name);

    /**
     * Set default values
     *
     * @abstract
     * @param PricingSetInterface $pricingSet
     * @return void
     */
    function setDefaultParametersForPricingSet(PricingSetInterface $pricingSet);
}

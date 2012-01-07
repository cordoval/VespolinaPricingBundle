<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PriceableInterface;
use Vespolina\PricingBundle\Model\PricingContextContainerInterface;
use Vespolina\PricingBundle\Model\PricingConfigurationInterface;
use Vespolina\PricingBundle\Model\PricingConstantInterface;
use Vespolina\PricingBundle\Model\PricingSetInterface;

/**
 * @author Daniel Kucharski <daniel@xerias.be>
 */
interface PricingManagerInterface
{

    /**
     * Add a pricing constant (a global constant )
     *
     * @abstract
     * @param \Vespolina\PricingBundle\Model\PricingConstantInterface $pricingConstant
     * @return void
     */
    function addPricingConstant(PricingConstantInterface $pricingConstant);

     /**
     * Build / calculate the necessary pricing values based on the pricing set,
     *  a given runtime pricing context container and possible some options.
     *
     * @param PricingSetInterface $pricingSet
     * @param PricingContextContainerInterface $container
     * @param array $options Possible
     *    Possible options:
     *      - execution_event ( all | context_independent | context_dependent )
      * @return void
     */
    function buildPricingSet(PricingSetInterface $pricingSet,
                             PricingContextContainerInterface $container,
                             $options = array());


    /**
     * Create a pricing set for this pricing configuration
     *
     * @param pricingConfiguration
     * @return void
     */
    function createPricingSet($priceConfigurationName);

    /**
     * Create a pricing context container and set pricing element values to the ones in
     * the pricing context container values
     *
     * @abstract
     * @param PricingSetInterface $pricingSet
     * @return void
     */
    function createPricingContextContainerFromPricingSet(PricingSetInterface $pricingSet);

    /**
     * Create a pricing element
     *
     * @abstract
     * @param $name
     */
    function createPricingElement($name);

    /**
     * Create a new pricing context container
     *
     * @return \Vespolina\PricingBundle\Model\PricingContextContainerInterface
     */
    function createPricingContextContainer(PricingConfigurationInterface $pricingConfiguration);

    /**
     * Get a pricing configuration
     *
     * @param  $name    Pricing configuration name
     * @return \Vespolina\PricingBundle\Model\PricingConfiguration
     */
    function getPricingConfiguration($name);

    /**
     * Get a pricing constant for a given name
     *
     * @abstract
     * @param $name
     * @return PricingConstantInterface
     */
    function getPricingConstant($name);


    function updatePricingSet(PricingSetInterface $pricingSet, $andFlush = true);
}

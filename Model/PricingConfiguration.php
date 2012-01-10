<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vespolina\PricingBundle\Model\PricingContextContainer;
use Vespolina\PricingBundle\Model\PricingSet;
use Vespolina\PricingBundle\Model\PricingSetInterface;

/**
 * PricingConfiguration wraps the main pricing configuration which consists of
 *  - a pricing set configuration
 *  - pricing meta data (such as active/non-active, version, ...)
 * 
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class PricingConfiguration implements PricingConfigurationInterface
{
    protected $determinationSequence;
    protected $name;
    protected $pricingSetConfiguration;

    /**
     * Constructor
     *
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    public function createPricingContextContainer()
    {

        return new PricingContextContainer();
    }

    /**
     * Create a new pricing set container from an existing pricing set,
     * copying necessary pricing element values to the pricing set container
     *
     * @param PricingSetInterface $pricingSet
     * @return PricingContextContainer
     */
    public function createPricingContextContainerFromPricingSet(PricingSetInterface $pricingSet)
    {
        $pricingContextContainer = new PricingContextContainer();

        foreach ($pricingSet->getPricingElements() as $pricingElement) {
            
            $pricingContextContainer->set($pricingElement->getName(), $pricingElement->getValue());
        }

        return $pricingContextContainer;
    }

    /**
     * Return the pricing set configuration associated to this pricing configuration
     *
     * @return
     */
    public function getPricingSetConfiguration()
    {

        return $this->pricingSetConfiguration;
    }

    public function setPricingSetConfiguration($pricingSetConfiguration)
    {

        $this->pricingSetConfiguration = $pricingSetConfiguration;
    }

    /**
     * Retrieve the pricing dimensions associated to this pricing set
     *
     * @return
     */
    protected function getPricingDimensions()
    {

        return $this->pricingDimensions;
    }


    public function getName()
    {

        return $this->name;
    }

}
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
 * PriceableInterface is a generic interface which a class should comply if would need to be priced
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
interface PriceableInterface
{
    /**
     * Attach the supplied pricing set to an priceable document
     *
     * @abstract
     * @param PricingSetInterface $pricingSet
     * @return void
     */
    public function addPricingSet(PricingSetInterface $pricingSet);

    /**
     * Retrieve all pricing sets
     *
     * @abstract
     * @return array
     */
    public function getPricingSets();

    /**
     * Set a collection of pricing sets
     * 
     * @abstract
     * @param  array $pricingSets
     * @return void
     */
    public function setPricingSets($pricingSets);

}

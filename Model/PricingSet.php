<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PricingSetInterface;

class PricingSet implements PricingSetInterface
{
    protected $createdAt;
    protected $dimensionsKey;
    protected $pricingConfigurationName;
    protected $pricingDimensionParameters;
    protected $pricingElements;
    protected $owner;
    protected $updatedAt;

    public function __construct()
    {
        $this->pricingDimensionParameters = array();
        $this->pricingElements = array();
    }

    public function addPricingElement(PricingElementInterface $pricingElement)
    {
        $this->pricingElements[$pricingElement->getName()] = $pricingElement;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getDimensionsKey()
    {
        if (!$this->dimensionsKey) {

            //Construct the unique pricing set key based on the supplied dimensions parameters
            foreach ($this->pricingDimensionParameters as $pricingDimensionParameter) {
                foreach ($pricingDimensionParameter as $parameter) {
                    if (is_object($parameter) && get_class($parameter) == 'DateTime') {
                        $this->key .= '_' . $parameter->getTimestamp();
                    } else {
                        $this->key .= '_' . $parameter;
                    }
                }
            }

            if (!$this->dimensionsKey) {
                $this->dimensionsKey = 'default';
            }
        }

        return $this->dimensionsKey;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getPricingConfigurationName()
    {
        return $this->pricingConfigurationName;
    }

    public function getPricingElement($name)
    {
        if (array_key_exists($name, $this->pricingElements)) {

            return $this->pricingElements[$name];
        }
    }

    public function getPricingElements()
    {
        return $this->pricingElements;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function incrementCreatedAt()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }
        $this->updatedAt = new \DateTime();
    }

    /**
     * @inheritdoc
     */
    public function incrementUpdatedAt()
    {
        $this->updatedAt = new \DateTime();
    }

    public function setPricingConfigurationName($pricingConfigurationName)
    {
        $this->pricingConfigurationName = $pricingConfigurationName;
    }

    public function setPricingDimensionParameters($name, $parameters)
    {
        $this->pricingDimensionParameters[$name] = $parameters;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }
}
<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PricingElementConfigurationInterface;
/**
 * PricingElement is the basic entity needed to determine prices
 * An example of pricing element is 'net_value' of 'sales_tax_percentage'
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class PricingElementConfiguration implements PricingElementConfigurationInterface
{
    protected $class;
    protected $executionEvent;
    protected $name;
    protected $options;

    function __construct($name, $class, $executionEvent, $options = array())
    {
        $this->name = $name;
        $this->executionEvent = $executionEvent;
        $this->class = $class;
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function getExecutionEvent()
    {
        return $this->executionEvent;
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
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function setIsDetermined($isDetermined)
    {

        return $this->isDetermined = $isDetermined;
    }
    
    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
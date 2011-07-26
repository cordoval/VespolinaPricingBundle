<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model;

use Vespolina\PricingBundle\Model\PricingElementInterface;

abstract class PricingExecutionStep implements PricingExecutionStepInterface
{
    protected $name;
    protected $options;
    protected $pricingContextContainer;

    /**
     * Constructor
     *
     * @param $options
     */
    public function __construct($name, $options = array())
    {
        $this->name = $name;
        $this->options = $options;

    }

    /**
     * Initialize this pricing execution step (eg. init cache )
     *
     * @param PricingContextContainerInterface $pricingContextContainer
     * @return void
     */
    public function init(PricingContextContainerInterface $pricingContextContainer)
    {
        $this->pricingContextContainer = $pricingContextContainer;
    }

    public function getName()
    {

        return $this->name;
    }
    /**
     * Get option value
     *
     * @param  $name
     * @param string $default
     * @return array|string
     */
    protected function getOption($name, $default = '')
    {
        if (array_key_exists($name, $this->options)) {

            return $this->options[$name];

        } else {
            
            return $default;
        }
    }

    function getHandlerClass()
    {
    }
}
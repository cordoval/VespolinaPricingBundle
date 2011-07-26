<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model\PricingExecutionStep;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Vespolina\PricingBundle\Model\PricingExecutionStep;
use Vespolina\PricingBundle\Model\PricingContextContainerInterface;

/**
 * SetContainerValue is a pricing execution step which sets the given target value from a source value
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class SetContainerValue extends PricingExecutionStep
{
    public function SetContainerValue($options = array())
    {
        parent::BasePricingExecutionStep($options);
    }

    public function execute(ContainerInterface $container)
    {
        $source = $this->getOption('source');

        //Is the supplied value numeric?
        if (is_numeric($source)) {
            $value = $source;
        } else {
            //It is not numeric -> it must be the name of a container value
            $value = $this->pricingContextContainer->get($source);
        }

        $this->pricingContextContainer->set(
            $this->getOption('target'),
            $value);
    }
}

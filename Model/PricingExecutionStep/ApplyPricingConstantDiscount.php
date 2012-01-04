<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Model\PricingExecutionStep;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Vespolina\PricingBundle\Model\PricingContextContainerInterface;
use Vespolina\PricingBundle\Model\PricingExecutionStep;

/**
 * ApplyPricingConstantDiscount Apply on <source> the discount as defined by <pricing_constant>
 * and store it in <target>.  If the discount is empty, <target> = <source>
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class ApplyPricingConstantDiscount extends PricingExecutionStep
{
    public function execute(ContainerInterface $container)
    {

        $value = $this->pricingContextContainer->get(
                    $this->getOption('target'));

        $pricingConstantName = $this->getOption('source');

        //Load the pricing constant
        $pricingConstant = $container->get('vespolina.pricing_manager')->getPricingConstant($pricingConstantName);
        //Do the mumbo jumbo
        if ($pricingConstant && $pricingConstantValue = $pricingConstant->getValue() ) {

            $value = $value - ($value * $pricingConstantValue / 100);
        }

        $this->pricingContextContainer->set(
            $this->getOption('target'),
            $value);
    }
}
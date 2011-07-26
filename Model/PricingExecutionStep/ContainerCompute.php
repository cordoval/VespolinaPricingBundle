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
 * ContainerCompute calculates the given expression within the pricing context container.
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class ContainerCompute extends PricingExecutionStep
{
    public function execute(ContainerInterface $container)
    {
        $total = 0;

        $expression = $this->getOption('source');
        $variables = str_word_count($expression, 1, '_');

        foreach ($variables as $variable) {
            $value = $this->pricingContextContainer->get($variable);
            if (!$value) {
                $value = 0;
            }
            $expression = str_replace($variable, $value, $expression);
        }

        $expression = '$total = ' . $expression . ';';
        eval($expression);

        $this->pricingContextContainer->set(
            $this->getOption('target'),
            $total);
    }
}
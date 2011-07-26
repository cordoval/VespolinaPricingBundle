<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
 
namespace Vespolina\PricingBundle\Service;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vespolina\PricingBundle\Model\PriceableInterface;
use Vespolina\PricingBundle\Model\PricingConfiguration;
use Vespolina\PricingBundle\Model\PricingConfigurationInterface;
use Vespolina\PricingBundle\Model\PricingContextContainerInterface;
use Vespolina\PricingBundle\Model\PricingContextContainer;
use Vespolina\PricingBundle\Model\PricingSetInterface;
use Vespolina\PricingBundle\Loader\XmlFileLoader;

use Vespolina\PricingBundle\Service\PricingServiceInterface;

/**
 * PricingService handles the overall pricing proces
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class PricingService extends ContainerAware implements PricingServiceInterface
{

    protected $pricingConfigurations = null;

    /**
     * Constructor
     */
    function __construct()
    {

    }

    /**
     * @inheritdoc
     */
    public function buildPricingSet(PricingSetInterface $pricingSet,
                                    PricingContextContainerInterface $container,
                                    $options = array())
    {

        $pricingConfigurationName = $pricingSet->getPricingConfigurationName();

        $pricingConfiguration = $this->getPricingConfiguration($pricingConfigurationName);

        if (!$pricingConfiguration) {

            throw new \InvalidArgumentException(sprintf('Could not load pricing configuration "%s"', $pricingConfigurationName));

        }


        if (array_key_exists('execution_event', $options)) {
            $executionEvent = $options['execution_event'];
        } else {
            $executionEvent = 'all';
        }
        //Init all pricing executions steps
        foreach ($pricingConfiguration->getPricingSetConfiguration()->getPricingExecutionSteps($executionEvent) as $pricingExecutionStep) {
            $pricingExecutionStep->init($container);    //Pricing context container
        }

        //Execute all execution steps
        foreach ($pricingConfiguration->getPricingSetConfiguration()->getPricingExecutionSteps($executionEvent) as $pricingExecutionStep) {
            $pricingExecutionStep->execute($this->container);   //DI container
        }

        //The pricing context container is nicely filled. For now we expect that the name of the pricing element is exactly
        //like the name in the pricing context container

        foreach ($pricingConfiguration->getPricingSetConfiguration()->getPricingElements($executionEvent) as $pricingElement) {
            $pricingElement->setValue($container->get($pricingElement->getName()));
            $pricingSet->addPricingElement($pricingElement);
        }

        return $pricingSet;

    }

    /**
     * @inheritdoc
     */
    public function createPricingSet(PricingConfigurationInterface $pricingConfiguration)
    {

        if ($pricingConfiguration){

            return $pricingConfiguration->createPricingSet();
        }
    }

    /**
     * @inheritdoc
     */
    public function createPricingContextContainerFromPricingSet(PricingSetInterface $pricingSet)
    {

        $pricingConfigurationName = $pricingSet->getPricingConfigurationName();

         $pricingConfiguration = $this->getPricingConfiguration($pricingConfigurationName);

         if (!$pricingConfiguration) {

            throw new \RuntimeException(sprintf('Could not load pricing configuration "%s"', $pricingConfigurationName));
         }

         return $pricingConfiguration->createPricingContextContainerFromPricingSet($pricingSet);
    }

    /**
     * @inheritdoc
     */
    public function createPricingContextContainer(PricingConfigurationInterface $pricingConfiguration)
    {

        return new PricingContextContainer();
    }
  
    /**
     * @inheritdoc
     */
    public function getPricingConfiguration($name)
    {

        return $this->pricingConfigurations->get($name);
    }

    /**
     * @inheritdoc
     */
    public function loadPricingConfigurationFile($dir, $file)
    {

        $loader = new XmlFileLoader(new FileLocator(array($dir)));

        $pricingConfigurations = $loader->load($file);

        //TODO: merge multiple pricing configuration files

        $this->pricingConfigurations = $pricingConfigurations;



    }
}

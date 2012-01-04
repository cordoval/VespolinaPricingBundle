<?php

namespace Vespolina\PricingBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Vespolina\PricingBundle\Model\PricingConstant;
use Vespolina\PricingBundle\Model\Pricing;

class SimpleProductPricingTest extends WebTestCase
{
    protected $client;

    public function setUp()
    {
        $this->client = $this->createClient();
    }

    public function getKernel(array $options = array())
    {
        if (!self::$kernel) {
            self::$kernel = $this->createKernel($options);
            self::$kernel->boot();
        }

        return self::$kernel;
    }



    public function testA1LoadPricingConfigurations()
    {
        $c = array();
        $c['pricingService'] = $this->getKernel()->getContainer()->get('vespolina.pricing_manager');

        $c['pricingService']->loadPricingConfigurationFile(__DIR__.'/config','pricing.xml');

        //Assert that pricing configuration 'default_product' exists
        $pricingConfiguration = $c['pricingService']->getPricingConfiguration('default_product');

        $this->assertEquals($pricingConfiguration->getName(), 'default_product');

        //Are all pricing elements present?
        $this->assertGreaterThanOrEqual(3, count($pricingConfiguration->getPricingSetConfiguration()->getPricingElements()));


        return $c;

    }

    /**
     * @depends testA1LoadPricingConfigurations
     */
    public function testA2CalculatePricingSets($c)
    {

        $today = new \DateTime('now');
        $nextMonth = new \DateTime('first day of next month');
        

        $pricingConfiguration = $c['pricingService']->getPricingConfiguration('default_product');
        $this->assertGreaterThanOrEqual(3, count($pricingConfiguration->getPricingSetConfiguration()->getPricingElements()));

         $this->assertEquals($pricingConfiguration->getName(), 'default_product');
            
        /**
         *  Test case 1: Create a pricing set for a product with a net value of 100 euro for today till first of next month.
         *               The packaging cost is 5% of the net value
         *  The price is valid if the ordered quantity volume is 99 or less.
            if less than 10
         */


        //Retrieve pricing meta data
        $pricingConfiguration = $c['pricingService']->getPricingConfiguration('default_product');

        //Create a pricing context container which is only used for runtime/execution purposes
        $pricingContextContainer = $c['pricingService']->createPricingContextContainer($pricingConfiguration);

        //Pricing Configuration already knows that net value is expressed in euro, so we just need to set a value
        $pricingContextContainer->set('net_value', '100');  

        //The difference between a price set and pricing context container is the fact that the latter
        //can contain more temporary runtime data which doesn't need to be stored at all

        $pricingSet = $c['pricingService']->createPricingSet($pricingConfiguration);


        //1st dimension parameter: the price set is available only from today till next month
        $pricingSet->setPricingDimensionParameters( 'period', 
                                                    array('from' => $today, 
                                                          'to' =>   $nextMonth));
                                                   
        //2nd dimension parameter: the price set is only available for volumes between 1 and 99
        $pricingSet->setPricingDimensionParameters( 'volume', 
                                                    array('from' => 1, 
                                                          'to' =>  99));    

        $c['pricingService']->buildPricingSet(
            $pricingSet, 
            $pricingContextContainer, 
            array('execution_event' => 'context_independent'));

        //Normally here we save everything to the database

        //Some time late we retrieve the product and associated active pricing set,
        //We now need to update the pricing set and add context dependent calculation ( add customer = TODO)

        //$pricingContextContainer->setValue('customer', blb);
            
        $c['pricingService']->buildPricingSet(
            $pricingSet,
            $pricingContextContainer,
            array('execution_event' => 'context_dependent'));

        // Assertions

        foreach ($pricingSet->getPricingElements() as $pricingElement) {

            switch ($pricingElement->getName()){

                case 'net_value':
                    $this->assertEquals($pricingElement->getValue(), '100');
                    break;
                case 'packaging_cost':
                    $this->assertEquals($pricingElement->getValue(), '5');
                    break;
                case 'total_excl_vat':
                    $this->assertEquals($pricingElement->getValue(), '105');
                    break;
            }

        }
          
        /** Test case 2: Update the product so the net_value is 120 euro (starting next month, 
         *  no matter what the ordered quantity is).  
         *  The packaging cost is linked and should therefore be recalculated
         */
        
        //Create pricing context container from the existing pricing set
    
        $pricingSetTwo = $c['pricingService']->createPricingSet($pricingConfiguration);

        $pricingSetTwo->setPricingDimensionParameters( 'period', 
                                                        array('from' => $nextMonth));
        
        $pricingContextContainerTwo = $c['pricingService']->createPricingContextContainerFromPricingSet($pricingSetTwo);
        $pricingContextContainerTwo->set('net_value', '120');  
   
        $c['pricingService']->buildPricingSet(
            $pricingSetTwo,
            $pricingContextContainerTwo, 
            array('execution_event' => 'all'));
        
        foreach ($pricingSetTwo->getPricingElements() as $pricingElementTwo) {
            
            switch ($pricingElementTwo->getName()){
                
                case 'net_value':
                    $this->assertEquals($pricingElementTwo->getValue(), '120');
                    break;
                case 'packaging_cost':
                    $this->assertEquals($pricingElementTwo->getValue(), '6');
                    break;
            }
            
        }

        return $c;
    }

   /**
     * @depends testA2CalculatePricingSets
     */
    public function testA3CalculateWithPricingConstant($c) {

        //Create a new pricing constant to store the global download discount rate
        $pricingConstant = new PricingConstant();
        $pricingConstant->setName('global_download_discount_rate');
        $pricingConstant->setValue(10); //10% discount
        $c['pricingService']->addPricingConstant($pricingConstant);

        $pricingConfiguration = $c['pricingService']->getPricingConfiguration('downloadable_product');
        $pricingContextContainer = $c['pricingService']->createPricingContextContainer($pricingConfiguration);
        $pricingSet = $c['pricingService']->createPricingSet($pricingConfiguration);

        $pricingContextContainer->set('net_value', '500');

        $c['pricingService']->buildPricingSet(
            $pricingSet,
            $pricingContextContainer,
            array('execution_event' => 'all'));

        $this->assertEquals($pricingSet->getPricingElement('net_value')->getValue(), 450);

    }
}
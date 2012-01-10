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
        $c['pricingManager'] = $this->getKernel()->getContainer()->get('vespolina.pricing_manager');

        $c['pricingManager']->loadPricingConfigurationFile(__DIR__.'/config','pricing.xml');

        //Assert that pricing configuration 'default_product' exists
        $pricingConfiguration = $c['pricingManager']->getPricingConfiguration('default_product');

        $this->assertEquals($pricingConfiguration->getName(), 'default_product');

        //Are all pricing elements present?
        $this->assertGreaterThanOrEqual(3, count($pricingConfiguration->getPricingSetConfiguration()->getPricingElementConfigurations()));


        return $c;

    }

    /**
     * @depends testA1LoadPricingConfigurations
     */
    public function testA2CalculatePricingSets($c)
    {

        $today = new \DateTime('now');
        $nextMonth = new \DateTime('first day of next month');
        

        $pricingConfiguration = $c['pricingManager']->getPricingConfiguration('default_product');
        $this->assertGreaterThanOrEqual(3, count($pricingConfiguration->getPricingSetConfiguration()->getPricingElementConfigurations()));

         $this->assertEquals($pricingConfiguration->getName(), 'default_product');
            
        /**
         *  Test case 1: Create a pricing set for a product with a net value of 100 euro for today till first of next month.
         *               The packaging cost is 5% of the net value
         *  The price is valid if the ordered quantity volume is 99 or less.
            if less than 10
         */


        //Retrieve pricing meta data
        $pricingConfiguration = $c['pricingManager']->getPricingConfiguration('default_product');

        //Create a pricing context container which is only used for runtime/execution purposes
        $pricingContextContainer = $c['pricingManager']->createPricingContextContainer($pricingConfiguration);

        //Pricing Configuration already knows that net value is expressed in euro, so we just need to set a value
        $pricingContextContainer->set('net_value', '100');  

        //The difference between a price set and pricing context container is the fact that the latter
        //can contain more temporary runtime data which doesn't need to be stored at all

        $pricingSet = $c['pricingManager']->createPricingSet('default_product');


        //1st dimension parameter: the price set is available only from today till next month
        $pricingSet->setPricingDimensionParameters( 'period', 
                                                    array('from' => $today, 
                                                          'to' =>   $nextMonth));
                                                   
        //2nd dimension parameter: the price set is only available for volumes between 1 and 99
        $pricingSet->setPricingDimensionParameters( 'volume', 
                                                    array('from' => 1, 
                                                          'to' =>  99));    

        $c['pricingManager']->buildPricingSet(
            $pricingSet, 
            $pricingContextContainer, 
            array('execution_event' => 'context_independent'));

        //Normally here we save everything to the database

        //Some time late we retrieve the product and associated active pricing set,
        //We now need to update the pricing set and add context dependent calculation ( add customer = TODO)

        //$pricingContextContainer->setValue('customer', blb);
            
        $c['pricingManager']->buildPricingSet(
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
    
        $pricingSetTwo = $c['pricingManager']->createPricingSet('default_product');

        $pricingSetTwo->setPricingDimensionParameters( 'period', 
                                                        array('from' => $nextMonth));
        
        $pricingContextContainerTwo = $c['pricingManager']->createPricingContextContainerFromPricingSet($pricingSetTwo);
        $pricingContextContainerTwo->set('net_value', '120');  
   
        $c['pricingManager']->buildPricingSet(
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
        $c['pricingManager']->addPricingConstant($pricingConstant);

        $pricingConfiguration = $c['pricingManager']->getPricingConfiguration('downloadable_product');
        $pricingContextContainer = $c['pricingManager']->createPricingContextContainer($pricingConfiguration);
        $pricingSet = $c['pricingManager']->createPricingSet('downloadable_product');

        $pricingContextContainer->set('net_value', '500');

        $c['pricingManager']->buildPricingSet(
            $pricingSet,
            $pricingContextContainer,
            array('execution_event' => 'all'));

        $this->assertEquals($pricingSet->getPricingElement('net_value')->getValue(), 450);

        $c['pricingSetA3'] = $pricingSet;

        return $c;
    }

    /**
      * @depends testA3CalculateWithPricingConstant
      */
     public function testA4PersistPricingSet($c) {

         //Set the owner of this pricing set, in this case a product having ID "IPAD-2011"
         $c['pricingSetA3']->setOwner('IPAD-2011');

         $c['pricingManager']->updatePricingSet($c['pricingSetA3']);
     }
}
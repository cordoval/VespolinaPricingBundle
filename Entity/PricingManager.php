<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\PricingBundle\Entity;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManager;

use Vespolina\PricingBundle\Model\PricingManager as BasePricingManager;
use Vespolina\PricingBundle\Model\PricingSetInterface;
/**
 * @author Daniel Kucharski <daniel@xerias.be>
 * @author Richard Shank <develop@zestic.com>
 */
class PricingManager extends BasePricingManager
{
    protected $pricingSetClass;
    protected $pricingSetRepo;
    protected $em;
    protected $primaryIdentifier;

    public function __construct(Container $container, EntityManager $em, $pricingElementClass, $pricingSetClass)
    {
        $this->em = $em;

        $this->pricingElementClass = $pricingElementClass;
        $this->pricingSetClass = $pricingSetClass;
        $this->pricingSetRepo = $this->em->getRepository($pricingSetClass);

        parent::__construct($container, $pricingElementClass, $pricingSetClass);
    }


    /**
     * @inheritdoc
     */
    public function createPricingSet($pricingConfigurationName)
    {

        $pricingConfiguration = $this->getPricingConfiguration($pricingConfigurationName);

        if ($pricingConfiguration) {

            $pricingSet = new $this->pricingSetClass();
            $pricingSet->setPricingConfigurationName($pricingConfigurationName);

            $this->initPricingSet($pricingSet, $pricingConfiguration);

        return $pricingSet;
        }

    }

    public function findPricingSetById($id)
    {

        if ($id) {

            return $this->em->createQueryBuilder($this->prcingSetClass)
                        ->field('id')->equals($id)
                        ->getQuery()
                        ->getSingleResult();
        }

    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->pricingSetRepo->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @inheritdoc
     */
    public function findPricingById($id)
    {
        return $this->pricingSetRepo->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findPricingByIdentifier($name, $code)
    {

           return;
    }

    /**
     * @inheritdoc
     */
    public function updatePricingSet(PricingSetInterface $pricingSet, $andFlush = true)
    {
        $this->em->persist($pricingSet);
        if ($andFlush) {
            $this->em->flush();
        }
    }
}

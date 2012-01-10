<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\PricingBundle\Document;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\ODM\MongoDB\DocumentManager;

use Vespolina\PricingBundle\Model\PricingSetInterface;
use Vespolina\PricingBundle\Model\PricingManager as BasePricingManager;
/**
 * @author Daniel Kucharski <daniel@xerias.be>
 * @author Richard Shank <develop@zestic.com>
 */
class PricingManager extends BasePricingManager
{
    protected $pricingSetClass;
    protected $pricingSetRepo;
    protected $dm;
    protected $primaryIdentifier;

    public function __construct(Container $container, DocumentManager $dm, $pricingElementClass, $pricingSetClass)
    {
        $this->dm = $dm;

        $this->pricingElementClass = $pricingElementClass;
        $this->pricingSetClass = $pricingSetClass;
        $this->pricingSetRepo = $this->dm->getRepository($pricingSetClass);

        parent::__construct($container, $pricingElementClass, $pricingSetClass);
    }


    public function findPricingSetById($id)
    {

        if ($id) {

            return $this->dm->createQueryBuilder($this->prcingSetClass)
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
        $this->dm->persist($pricingSet);
        if ($andFlush) {
            $this->dm->flush();
        }
    }
}

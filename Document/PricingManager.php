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

use Vespolina\PricingBundle\Document\Pricing;
use Vespolina\PricingBundle\Model\PricingableItemInterface;
use Vespolina\PricingBundle\Model\PricingInterface;
use Vespolina\PricingBundle\Model\PricingItemInterface;
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

    public function __construct(Container $container, DocumentManager $dm, $pricingSetClass)
    {
        $this->dm = $dm;

        $this->pricingSetClass = $pricingSetClass;
        $this->pricingSetRepo = $this->dm->getRepository($pricingSetClass);

        parent::__construct($container);
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
    public function updatePricing(PricingInterface $cart, $andFlush = true)
    {
        $this->dm->persist($cart);
        if ($andFlush) {
            $this->dm->flush();
        }
    }
}

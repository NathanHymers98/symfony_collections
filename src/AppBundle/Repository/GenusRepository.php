<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Genus;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class GenusRepository extends EntityRepository
{
    /**
     * @return Genus[]
     */
    public function findAllPublishedOrderedByRecentlyActive()
    {
        return $this->createQueryBuilder('genus')
            ->andWhere('genus.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->leftJoin('genus.notes', 'genus_note')
            ->orderBy('genus_note.createdAt', 'DESC')
//            ->leftJoin('genus.genusScientists', 'genusScientist')
//            ->addSelect('genusScientist')
            ->getQuery()
            ->execute();
    }

    /**
     * @return Genus[]
     */
    public function findAllExperts()
    {
        return $this->createQueryBuilder('genus')
            ->addCriteria(self::createExpertCriteria())
            ->getQuery()
            ->execute();
    }

    static public function createExpertCriteria() // This criteria describes how we want to filter the expert scientists
    {
        return Criteria::create() // Firstly, using the create function inside the Criteria class
            ->andWhere(Criteria::expr()->gt('yearsStudied', 20)) // this is the actual filter, it is filtering for any GenusScientist objects which have a value greater than 20 set to their property 'yearsStudied'
            ->orderBy(['yearsStudied', 'DESC']); // Order it in descending order
    }
}

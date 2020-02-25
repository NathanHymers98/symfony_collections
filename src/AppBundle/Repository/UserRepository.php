<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function createIsScientistQueryBuilder() // This doesn't return the query, it only creates the query builder, which we will call later on to use the query
    {
        return $this->createQueryBuilder('user') // Calling the query builder to select all the data from the user table/class
            ->andWhere('user.isScientist = :isScientist') // where the user class's property isScientist is equal to the isScientist parameter
            ->setParameter('isScientist', true); // Setting the isScientist parameter to true.
    }
}

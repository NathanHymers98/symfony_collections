<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


// The UniqueEntity makes it so that in the form where a new GenusScientist can be created, certain fields have to be unique, in this case if the properties "genus" and "user" are the same as another GenusScientist object in the array
// Then it will not allow for the new GenusScientist object to be created.
// the errorPath="user" is basically telling symfony to put the error message close to the offending field, which in this case is the user field. If this is not in place then the error would show at the top of the main form.
// This will make it show at the correct field in the embedded form.
/**
 * @ORM\Entity
 * @ORM\Table(name="genus_scientist")
 * @UniqueEntity(
 *     fields={"genus", "user"},
 *     message="This user is already studying this genus",
 *     errorPath="user"
 * )
 */
class GenusScientist // When you have a many to many relationship and it needs to have its own fields in the database, you will have to manually create the join table, and make the other entity classes that previously
                    // had a many to many relationship have a one to many relationship with the manually created join table, which is this class here in this case.
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    // Has a Many to one relationship with the entity class Genus. This is the owning side of the relationship, so it is inversed by the genusScientists property in the Genus class
    // This has a Join column instead of a join table because technically this entity class itself is the join table, it relates both Genus and User together in this table under the properties $genus and $user
    /**
     * @ORM\ManyToOne(targetEntity="Genus", inversedBy="genusScientists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $genus;

    // This is the same as the above relationship, however this time it is with the entity class User.
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="studiedGenuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $yearsStudied;

    public function getId()
    {
        return $this->id;
    }

    public function getGenus()
    {
        return $this->genus;
    }

    public function setGenus($genus)
    {
        $this->genus = $genus;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getYearsStudied()
    {
        return $this->yearsStudied;
    }

    public function setYearsStudied($yearsStudied)
    {
        $this->yearsStudied = $yearsStudied;
    }
}
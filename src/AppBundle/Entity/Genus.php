<?php

namespace AppBundle\Entity;

use AppBundle\Repository\GenusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GenusRepository")
 * @ORM\Table(name="genus")
 */
class Genus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SubFamily")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subFamily;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=0, minMessage="Negative species! Come on...")
     * @ORM\Column(type="integer")
     */
    private $speciesCount;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $funFact;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = true;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="date")
     */
    private $firstDiscoveredAt;

    /**
     * @ORM\OneToMany(targetEntity="GenusNote", mappedBy="genus")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $notes;

    // This has a One to Many relationship with the GenusScientist entity class, this is the inverse side of the relationship so it is mapped by the $genus property inside GenusScientist class
    // The @Assert\Valid() adds validation to GenusScientists in the form, meaning that fields cannot be left blank. However, you still need to add the @Assert\NotBlank annotation above the property that you don't want to be blank
    // We have to add it here because we can only validate top level objects, and in this case Genus is the top level object.
    // The cascade={"persist"} says that when we persist a Genus object automatically call the method persist on each of the GenusScientist objects in this array
    /**
     * @ORM\OneToMany(
     *     targetEntity="GenusScientist",
     *     mappedBy="genus",
     *     fetch="EXTRA_LAZY",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     * @Assert\Valid()
     */
    private $genusScientists; // This will hold an array of User objects that are linked to this Genus

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->genusScientists = new ArrayCollection(); // Whenever you have a doctrine relationship where your property is an array of items, you need to initalize that property in the constructor method
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return SubFamily
     */
    public function getSubFamily()
    {
        return $this->subFamily;
    }

    public function setSubFamily(SubFamily $subFamily = null)
    {
        $this->subFamily = $subFamily;
    }

    public function getSpeciesCount()
    {
        return $this->speciesCount;
    }

    public function setSpeciesCount($speciesCount)
    {
        $this->speciesCount = $speciesCount;
    }

    public function getFunFact()
    {
        return $this->funFact;
    }

    public function setFunFact($funFact)
    {
        $this->funFact = $funFact;
    }

    public function getUpdatedAt()
    {
        return new \DateTime('-'.rand(0, 100).' days');
    }

    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * @return ArrayCollection|GenusNote[]
     */
    public function getNotes()
    {
        return $this->notes;
    }

    public function getFirstDiscoveredAt()
    {
        return $this->firstDiscoveredAt;
    }

    public function setFirstDiscoveredAt(\DateTime $firstDiscoveredAt = null)
    {
        $this->firstDiscoveredAt = $firstDiscoveredAt;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    // Creating this so that other classes can add data to the $genusScientist property, if a genusScientist object tried to get passed here and it already exists, then it will just return that object.
    public function addGenusScientist(GenusScientist $genusScientist)
    {
        if ($this->genusScientists->contains($genusScientist)) { // If the genusScientists array contains a genusScientist object, then simply return it. We can use the contains() method because genusScientists is an ArrayCollection
            return;
        }

        $this->genusScientists[] = $genusScientist; // If it doesn't contain the object, then create it and add it to the array
        // needed to update the owning side of the relationship and keep both sides in sync
        $genusScientist->setGenus($this); // And set the genusScientist object to a genus
    }

    public function removeGenusScientist(GenusScientist $genusScientist)
    {
        if (!$this->genusScientists->contains($genusScientist)) { // if the genusScientist object is not the in genusScientists array, then do nothing
            return;
        }

        $this->genusScientists->removeElement($genusScientist); // If it is in the array, then remove it
        // needed to update the owning side of the relationship and keep both sides in sync
        $genusScientist->setGenus(null); // After removing the genusScientist object, unset the genus property
    }

    // This annotation says that this function returns an array of GenusScientst objects
    /**
     * @return ArrayCollection|GenusScientist[]
     */
    public function getGenusScientists()
    {
        return $this->genusScientists;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|GenusScientist[]
     */
    public function getExpertScientists()
    {
        return $this->getGenusScientists()->matching(
            GenusRepository::createExpertCriteria()
        );
    }
}

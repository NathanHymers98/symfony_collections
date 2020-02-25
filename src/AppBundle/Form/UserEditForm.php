<?php

namespace AppBundle\Form;

use AppBundle\Entity\Genus;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditForm extends AbstractType // This is the form that allows the admin to edit a users details
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder // This builds the form, each add() method takes a property from the User class that we want to show on the form, so that it can be edited for an existing user or it can be used when creating a new user.
            ->add('email', EmailType::class)
            ->add('isScientist')
            ->add('firstName')
            ->add('lastName')
            ->add('universityName')
            ->add('studiedGenuses', EntityType::class, [ // Adding the studiedGenuses property to the form. Giving it an entity type of class and then setting the options
                'class' => Genus::class, // Setting the class to genus class, because we want to use this field in the form to edit what genuses a genusScientist us studying. This create the genus checkboxes
                'multiple' => true, // Multiple is set to true because there is going to be multiple selected genuses for one user
                'expanded' => true,
                'choice_label' => 'name' // Displays the genus property name in the form so that it gets the names of the genuses for us
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

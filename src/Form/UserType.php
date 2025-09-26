<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Utilisateur' => 'ROLE_USER',
                    'Admin'       => 'ROLE_ADMIN',
                    'Manager'     => 'ROLE_MANAGER',
                ],
                'multiple' => true,   
                'expanded' => true,  
                'required' => false,
            ])
            ->add('password')
          
            ->add('firstName')
            ->add('lastName')
            ->add('phone')
            ->add('address')
            ->add('dateOfBirth', null, [
                'widget' => 'single_text'
            ])
            ->add('pseudo')
            
            
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

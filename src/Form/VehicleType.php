<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('plateNumber')
            ->add('firstRegistrationAt', null, [
                'widget' => 'single_text'
            ])
            ->add('brand')
            ->add('model')
            ->add('seats')
            ->add('isElectric')
            ->add('isActive')

            ->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $u) => $u->getPseudo() ?: $u->getEmail(),
                'placeholder' => 'Choisir un propriétaire',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}

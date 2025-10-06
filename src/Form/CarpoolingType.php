<?php

namespace App\Form;

use App\Entity\Carpooling;
use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CarpoolingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Conducteur
            ->add('driver', EntityType::class, [
                'class'        => User::class,
                'label'        => 'Conducteur',
                'choice_label' => static function (User $u) {
                    // adapte selon tes champs (pseudo / email)
                    return $u->getPseudo() ? $u->getPseudo() . ' (' . $u->getEmail() . ')' : $u->getEmail();
                },
                'placeholder'  => '— Sélectionner un conducteur —',
                'attr'         => ['class' => 'form-control form-control-user'],
                'constraints'  => [new Assert\NotNull(message: 'Le conducteur est requis.')],
            ])

            // Véhicule
            ->add('vehicle', EntityType::class, [
                'class'        => Vehicle::class,
                'label'        => 'Véhicule',
                'choice_label' => static function (Vehicle $v) {
                    // si tu as marque/modèle, utilise-les ici
                    return '#' . $v->getId();
                },
                'placeholder'  => '— Sélectionner un véhicule —',
                'attr'         => ['class' => 'form-control form-control-user'],
                'constraints'  => [new Assert\NotNull(message: 'Le véhicule est requis.')],
            ])

            // Villes
            ->add('deparatureCity', TextType::class, [
                'label'       => 'Ville de départ',
                'attr'        => [
                    'placeholder' => 'Ex : Lille',
                    'maxlength'   => 50,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La ville de départ est requise.'),
                    new Assert\Length(max: 50, maxMessage: '50 caractères maximum.'),
                ],
            ])
            ->add('arrivalCity', TextType::class, [
                'label'       => 'Ville d’arrivée',
                'attr'        => [
                    'placeholder' => 'Ex : Paris',
                    'maxlength'   => 50,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La ville d’arrivée est requise.'),
                    new Assert\Length(max: 50, maxMessage: '50 caractères maximum.'),
                ],
            ])

            // Dates/horaires
            ->add('deparatureAt', DateTimeType::class, [
                'label'       => 'Départ le',
                'widget'      => 'single_text',
                'attr'        => ['class' => 'form-control form-control-user'],
                'constraints' => [new Assert\NotNull(message: 'La date/heure de départ est requise.')],
            ])
            ->add('arrivalAt', DateTimeType::class, [
                'label'       => 'Arrivée le',
                'widget'      => 'single_text',
                'attr'        => ['class' => 'form-control form-control-user'],
                'constraints' => [new Assert\NotNull(message: 'La date/heure d’arrivée est requise.')],
            ])

            // Capacités
            ->add('seatsTotal', IntegerType::class, [
                'label'       => 'Places totales',
                'attr'        => ['min' => 1, 'max' => 8, 'class' => 'form-control form-control-user'],
                'constraints' => [
                    new Assert\NotNull(message: 'Nombre de places total requis.'),
                    new Assert\Positive(message: 'Doit être supérieur à 0.'),
                ],
            ])
            ->add('seatsAvaible', IntegerType::class, [
                'label'       => 'Places disponibles',
                'required'    => false,
                'attr'        => ['min' => 0, 'max' => 8, 'class' => 'form-control form-control-user'],
            ])

            // Prix
            ->add('price', MoneyType::class, [
                'label'       => 'Prix',
                'currency'    => 'EUR',
                'attr'        => ['class' => 'form-control form-control-user'],
                'constraints' => [
                    new Assert\NotNull(message: 'Le prix est requis.'),
                    new Assert\PositiveOrZero(message: 'Le prix doit être positif.'),
                ],
            ])

            // Statut
            ->add('status', ChoiceType::class, [
                'label'       => 'Statut',
                'choices'     => [
                    'Brouillon'  => 'draft',
                    'Publié'     => 'published',
                    'Annulé'     => 'cancelled',
                ],
                'placeholder' => '— Sélectionner un statut —',
                'attr'        => ['class' => 'form-control form-control-user'],
                'constraints' => [new Assert\NotBlank(message: 'Le statut est requis.')],
            ])

            // Éco-tag
            ->add('ecoTag', CheckboxType::class, [
                'label'    => 'Trajet éco-responsable',
                'required' => false,
            ]);

   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Carpooling::class,
        ]);
    }
}

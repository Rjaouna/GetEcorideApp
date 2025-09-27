<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Types
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

// Validation
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Immatriculation (FR, ex: AA-123-AA)
            ->add('plateNumber', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Immatriculation (ex: AA-123-AA)',
                    'maxlength'   => 12,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'L’immatriculation est requise.'),
                    new Assert\Length(max: 12, maxMessage: '12 caractères maximum.'),
                    // accepte lettres/chiffres avec séparateurs -, espace ou rien
                    new Assert\Regex(
                        pattern: '/^[A-Z0-9]{2}[- ]?[0-9]{3}[- ]?[A-Z0-9]{2}$/i',
                        message: 'Format d’immatriculation invalide.'
                    ),
                ],
            ])

            // 1ère mise en circulation
            ->add('firstRegistrationAt', DateType::class, [
                'widget'   => 'single_text',
                'html5'    => true,
                'required' => false,
                'input'    => 'datetime_immutable', // si ta propriété est en DateTimeImmutable
                'attr'     => [
                    'placeholder' => '1ère mise en circulation',
                    'class'       => 'form-control form-control-user',
                    'max'         => (new \DateTime())->format('Y-m-d'), // pas de futur
                ],
                'constraints' => [
                    new Assert\LessThanOrEqual(['value' => 'today', 'message' => 'La date doit être dans le passé.']),
                    new Assert\GreaterThan(['value' => '1900-01-01', 'message' => 'Date trop ancienne.']),
                ],
            ])

            // Marque
            ->add('brand', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Marque',
                    'maxlength'   => 50,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La marque est requise.'),
                    new Assert\Length(max: 50, maxMessage: '50 caractères maximum.'),
                ],
            ])

            // Modèle
            ->add('model', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Modèle',
                    'maxlength'   => 50,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le modèle est requis.'),
                    new Assert\Length(max: 50, maxMessage: '50 caractères maximum.'),
                ],
            ])

            // Nombre de sièges
            ->add('seats', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nombre de sièges',
                    'min'         => 1,
                    'max'         => 9,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nombre de sièges est requis.'),
                    new Assert\Positive(message: 'Le nombre de sièges doit être positif.'),
                    new Assert\Range(min: 1, max: 9, notInRangeMessage: 'Entre {{ min }} et {{ max }} sièges.'),
                ],
            ])

            // Électrique
            ->add('isElectric', CheckboxType::class, [
                'required' => false,
                'label'    => 'Électrique',
            ])

            // Actif
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label'    => 'Actif',
        ])

            // Propriétaire
            ->add('owner', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => fn(User $u) => $u->getPseudo() ?: $u->getEmail(),
                'placeholder'   => 'Choisir un propriétaire',
                'required'      => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.pseudo', 'ASC')
                        ->addOrderBy('u.email', 'ASC');
                },
                'constraints' => [
                    new Assert\NotNull(message: 'Le propriétaire est requis.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
                'attr' => [
                    'placeholder' => '6 lettres min, sans espace ni caractères spéciaux',
                    'pattern' => '[A-Za-z]{6,}',
                    'maxlength' => 30,
                    'spellcheck' => 'false',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le pseudo est obligatoire.'),
                    new Assert\Length(
                        min: 6,
                        max: 30,
                        minMessage: 'Ton pseudo doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Ton pseudo ne peut pas dépasser {{ limit }} caractères.'
                    ),
                    // Seulement des lettres A–Z/a–z, donc :
                    new Assert\Regex(
                        pattern: '/^[A-Za-z]+$/',
                        message: 'Utilise uniquement des lettres (A–Z), sans espace ni caractère spécial.'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'exemple@gmail.com',
                    'class' => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'L’email est requis.'),
                    new Assert\Email(message: 'Format d’email invalide.'),
                    new Assert\Length(
                        max: 180,
                        maxMessage: '180 caractères maximum.'
                    ),
                ],
            ])
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
            ->add('firstName', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Jean',
                    'maxlength'   => 20,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le prénom est requis.'),
                    new Assert\Length(
                        max: 20,
                        maxMessage: '20 caractères maximum.'
                    ),
                    new Assert\Regex('/^[\p{L} \'-]+$/u', 'Caractères non autorisés.'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Dupont',
                    'maxlength'   => 20,
                    'class'       => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom est requis.'),
                    new Assert\Length(
                        max: 20,
                        maxMessage: '20 caractères maximum.'
                    ),
                    new Assert\Regex('/^[\p{L} \'-]+$/u', 'Caractères non autorisés.'),
                ],
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => '06 12 34 56 78',
                    'maxlength' => 20,
                    'class' => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le téléphone est requis.'),
                    new Assert\Length(max: 20, maxMessage: '20 caractères maximum.'),
                    new Assert\Regex(
                        pattern: '/^(?:\+33|0)[1-9](?:[ .-]?\d{2}){4}$/',
                        message: 'Numéro français invalide.'
                    ),
                ],
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder'  => 'Adresse',
                    'maxlength'    => 255,
                    'class'        => 'form-control form-control-user',
                ],
                'constraints' => [
                    new Assert\Length(max: 255, maxMessage: '255 caractères maximum.'),
                ],
            ])
            ->add('dateOfBirth', DateType::class, [
                'required'      => false,
                'input'         => 'datetime_immutable',
                'attr' => [
                    'max'          => (new \DateTime())->format('Y-m-d'),
                    'placeholder'  => 'Date de naissance',
                ],
                'constraints' => [
                    new Assert\LessThanOrEqual(['value' => 'today', 'message' => 'La date doit être dans le passé.']),
                    // âge mini 16 ans (change à 18 si besoin)
                    new Assert\LessThanOrEqual([
                        'value' => (new \DateTimeImmutable('-16 years')),
                        'message' => 'Vous devez avoir au moins 16 ans.',
                    ]),
                    // borne basse raisonnable
                    new Assert\GreaterThan(['value' => '1900-01-01', 'message' => 'Date trop ancienne.']),
                ],
            ]);

        if ($options['include_password']) {
            $builder->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder'  => 'Mot de passe',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le mot de passe est requis.'),
                    new Assert\Length(min: 8, minMessage: 'Au moins {{ limit }} caractères.'),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'       => User::class,
            'include_password' => true,
        ]);
    }
}

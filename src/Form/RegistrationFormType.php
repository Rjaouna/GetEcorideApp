<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\{
    NotBlank,
    Length,
    Email as EmailAssert,
    IsTrue,
    Regex,
    LessThanOrEqual,
    Date as DateAssert
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    EmailType,
    TextType,
    TelType,
    TextareaType,
    DateType,
    PasswordType,
    CheckboxType,
    FileType
};

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Identité
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire.']),
                    new Length(['max' => 50]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Length(['max' => 50]),
                ],
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le pseudo est obligatoire.']),
                    new Length(['min' => 3, 'max' => 20]),
                ],
            ])

            // Contact
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'L’email est obligatoire.']),
                    new EmailAssert(['message' => 'Email invalide.']),
                    new Length(['max' => 180]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s.\-()]{6,20}$/',
                        'message' => 'Numéro de téléphone invalide.',
                    ]),
                ],
            ])

            // Infos perso
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être dans le passé.',
                    ]),
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['rows' => 3],
            ])

          

            // Sécurité
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de saisir un mot de passe.']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J’accepte les CGU',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new IsTrue(['message' => 'Vous devez accepter les conditions.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}

<?php

namespace App\Form\pages\contact;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
	public function buildForm(FormBuilderInterface $b, array $options): void
	{
		$b
			->add('fullName', TextType::class, [
				'label' => 'Nom complet',
				'constraints' => [
					new Assert\NotBlank(message: 'Nom requis'),
					new Assert\Length(max: 100),
				],
				'attr' => ['placeholder' => 'Votre nom complet', 'class' => 'form-control'],
			])
			->add('email', EmailType::class, [
				'label' => 'Email',
				'constraints' => [
					new Assert\NotBlank(message: 'Email requis'),
					new Assert\Email(message: 'Email invalide'),
					new Assert\Length(max: 180),
				],
				'attr' => ['placeholder' => 'vous@exemple.com', 'class' => 'form-control'],
			])
			->add('message', TextareaType::class, [
				'label' => 'Message',
				'constraints' => [
					new Assert\NotBlank(message: 'Message requis'),
				new Assert\Length(min: 1, max: 2000),
				],
				'attr' => ['rows' => 6, 'placeholder' => 'Votre message…', 'class' => 'form-control'],
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		// CSRF auto via forms (token dans _token)
		$resolver->setDefaults([
			'csrf_protection' => true,
			'csrf_field_name' => '_token',
			'csrf_token_id'   => 'contact',
		]);
	}
}

<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex; 

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            
            ->add('accountType', ChoiceType::class, [
                'mapped' => false, 
                'choices'  => [
                    'Customer' => 'ROLE_CUSTOMER',
                    'Seller' => 'ROLE_SELLER',
                ],
                'label' => 'I want to register as a:',
                'expanded' => false,
                'multiple' => false,
            ])
            
            ->add('securityPin', PasswordType::class, [
                'label' => '4-Digit Security PIN (Required for Checkout)',
                'attr' => [
                    'maxlength' => 4,
                    'pattern' => '\d{4}',
                    'inputmode' => 'numeric',
                    'title' => 'Please enter exactly 4 digits (e.g. 1234)'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'A security PIN is required.'
                    ),
                    new Length(
                        min: 4,
                        max: 4,
                        exactMessage: 'Your security PIN must be exactly {{ limit }} digits long.'
                    ),
                    new Regex(
                        pattern: '/^\d{4}$/',
                        message: 'Invalid input. Your PIN can only contain numbers, no letters or symbols.'
                    ),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'You should agree to our terms.'
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password'
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'Your password should be at least {{ limit }} characters',
                        max: 4096
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
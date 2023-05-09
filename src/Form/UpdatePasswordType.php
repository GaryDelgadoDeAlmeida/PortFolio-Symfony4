<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("oldPassword", PasswordType::class, [
                "label" => "Current password",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add("newPassword", PasswordType::class, [
                "label" => "New password",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add("confirmNewPassword", PasswordType::class, [
                "label" => "Confirm the new password",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add("submit", SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-primary"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', null, [
                "required" => true,
                'placeholder' => "Votre Nom"
            ])
            ->add('email', EmailType::class, [
                "required" => true,
                'placeholder' => "Votre Email"
            ])
            ->add('subject', null, [
                "required" => true,
                'placeholder' => "Sujet"
            ])
            ->add('contentEmail', TextareaType::class, [
                "required" => true,
                'placeholder' => "Message"
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

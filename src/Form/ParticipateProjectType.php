<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ParticipateProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("link", UrlType::class, [
                "label" => "Link of the project",
                "required" => false,
                "attr" => [
                    "maxLenght" => 255,
                ]
            ])
            ->add("title", TextType::class, [
                "label" => "Project name",
                "required" => true,
                "attr" => [
                    "maxLenght" => 255
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

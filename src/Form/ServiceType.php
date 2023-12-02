<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                "attr" => [
                    "maxLenght" => 255,
                    "placeholder" => "Title"
                ]
            ])
            ->add('icon', ChoiceType::class, [
                "choices" => [
                    "Select an icon" => null,
                    "Lightbulb" => "lightbulb",
                    "Code" => "code",
                    "Wrench" => "wrench",
                    "Gear" => "gear",
                    "Desktop - mobile" => "desktop-mobile",
                    "Square check" => "square-check"
                ]
            ])
            ->add('description', TinymceType::class, [
                "attr" => [
                    "placeholder" => "Description of the service",
                    "toolbar" => "bold italic underline | bullist numlist"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}

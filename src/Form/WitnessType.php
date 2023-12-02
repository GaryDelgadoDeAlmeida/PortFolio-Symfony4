<?php

namespace App\Form;

use App\Entity\Witness;
use Symfony\Component\Form\AbstractType;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class WitnessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', null, [
                "label" => "Prénom",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add('lastname', null, [
                "label" => "Nom de famille",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add('company', null, [
                "label" => "Société",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add('comment', TinymceType::class, [
                "label" => "Commentaire",
                "required" => true,
                "attr" => [
                    "toolbar" => "bold italic underline | bullist numlist"
                ]
            ])
            ->add("submit", SubmitType::class, [
                "attr" => [
                    "value" => "Enregistrer"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Witness::class,
        ]);
    }
}

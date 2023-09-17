<?php

namespace App\Form;

use App\Entity\Price;
use App\Form\PriceDetailType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                "attr" => [
                    "placeholder" => "Title",
                    "maxLength" => 255
                ]
            ])
            ->add('subTitle', null, [
                "attr" => [
                    "placeholder" => "Sub title",
                    "maxLength" => 150
                ]
            ])
            ->add('price', NumberType::class, [
                "html5" => true,
                "attr" => [
                    "min" => 0
                ]
            ])
            ->add("frequency", ChoiceType::class, [
                "label" => "Frequency",
                "choices" => [
                    'Make your choice, please' => null,
                    "Daily" => "daily",
                    "Mounthly" => "mounthly",
                    "Yearly" => "yearly",
                    "One shot" => "one_shot"
                ],
                "required" => true
            ])
            ->add("priceDetails", CollectionType::class, [
                "entry_type" => PriceDetailType::class,
                "entry_options" => [
                    "label" => false
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\PriceDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PriceDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', null, [
                "attr" => [
                    "maxLength" => 255,
                    "placeholder" => "Label"
                ],
                "required" => true
            ])
            ->add('description', TextareaType::class, [
                "attr" => [
                    "placeholder" => "Description"
                ],
                "required" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriceDetail::class,
        ]);
    }
}

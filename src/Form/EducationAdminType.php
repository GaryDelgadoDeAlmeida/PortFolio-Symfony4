<?php

namespace App\Form;

use App\Entity\Education;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EducationAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Job Name',
                "required" => true
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Start Date',
                "required" => true
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'End Date',
                "required" => false
            ])
            ->add('inProgress', null, [
                'label' => 'Status',
                "required" => false
            ])
            ->add('corporationName', null, [
                'label' => 'Corporation Name',
                "required" => true
            ])
            ->add('description', TextareaType::class, [
                "required" => true,
                'label' => "Description"
            ])
            ->add('category', ChoiceType::class, [
                "choices" => [
                    'Make your choice, please' => null,
                    "Formation" => "formation",
                    "Education" => "education"
                ],
                "label" => "Category",
                "required" => true
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Education::class,
        ]);
    }
}

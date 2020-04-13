<?php

namespace App\Form;

use App\Entity\Education;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('startDate', DateType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
                'required' => true
            ])
            ->add('endDate', DateType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                "required" => false
            ])
            ->add('inProgress', null, [
                'label' => 'In Progress',
                "required" => false
            ])
            ->add('corporationName', null, [
                'label' => 'Corporation Name',
                "required" => true
            ])
            ->add('description', TextareaType::class, [
                "required" => false,
                'label' => "Description"
            ])
            ->add('category', ChoiceType::class, [
                "label" => "Category",
                "choices" => [
                    'Make your choice, please' => null,
                    "Formation" => "formation",
                    "Education" => "education"
                ],
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

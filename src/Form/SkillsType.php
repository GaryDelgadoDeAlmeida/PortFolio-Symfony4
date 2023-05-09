<?php

namespace App\Form;

use App\Entity\Skills;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skill', null, [
                "attr" => [
                    "placeholder" => "Insert a tech name"
                ],
                "required" => true
            ])
            ->add('type', ChoiceType::class, [
                "choices" => [
                    "Frontend" => "frontend",
                    "Backend" => "backend",
                    "Tools" => "tools"
                ],
                "attr" => [
                    "hidden" => true
                ],
                "required" => true
            ])
            ->add("submit", SubmitType::class, [
                "label" => "Valider",
                "attr" => [
                    "class" => "btn btn-primary"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skills::class,
        ]);
    }
}

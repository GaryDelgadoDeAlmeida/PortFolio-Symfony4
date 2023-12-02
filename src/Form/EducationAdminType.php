<?php

namespace App\Form;

use App\Entity\Skills;
use App\Entity\Education;
use App\Form\ParticipateProjectType;
use Symfony\Component\Form\AbstractType;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EducationAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', ChoiceType::class, [
                "label" => "Category",
                "choices" => [
                    'Make your choice, please' => null,
                    "Formation" => "formation",
                    "Experience" => "experience"
                ],
                "required" => true
            ])
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
                'label' => 'Corporation name',
                "required" => true
            ])
            ->add('corporationSite', UrlType::class, [
                "label" => "Corporation site",
                "required" => true
            ])
            ->add("contractType", ChoiceType::class, [
                'label' => 'Contract type',
                'choices' => [
                    'Make your choice, please' => null,
                    'CDD' => 'cdd',
                    'CDI' => 'cdi',
                    'Freelance' => 'freelance',
                    'Stage' => 'internship',
                    'Alternance' => 'study_contract',
                ],
                "required" => true
            ])
            ->add("description", TinymceType::class, [
                "label" => "Description",
                "attr" => [
                    "toolbar" => "bold italic underline | bullist numlist"
                ],
                "required" => true,
            ])
            ->add("skills", EntityType::class, [
                "label" => "Skills",
                "class" => Skills::class,
                "choice_label" => "skill",
                "multiple" => true,
                "required" => true,
            ])
            ->add("participateProjects", CollectionType::class, [
                "entry_type" => ParticipateProjectType::class,
                "entry_options" => [
                    "label" => false
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false
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

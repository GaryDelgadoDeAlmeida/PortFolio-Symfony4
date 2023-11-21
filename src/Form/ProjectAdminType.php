<?php

namespace App\Form;

use App\Entity\Skills;
use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProjectAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                "required" => true,
                "attr" => [
                    "minLenght" => 4,
                    "maxLenght" => 255,
                ]
            ])
            ->add("description", TextareaType::class, [
                "attr" => [
                    "class" => "h-150px"
                ]
            ])
            ->add('imgPath', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image document',
                    ])
                ]
            ])
            ->add("date", DateType::class, [
                'widget' => 'single_text'
            ])
            ->add("goLiveDate", DateType::class, [
                'widget' => 'single_text',
                "required" => false
            ])
            ->add('githubLink', UrlType::class, [
                "required" => false
            ])
            ->add("siteLink", UrlType::class, [
                "required" => false
            ])
            ->add("clientName", null, [
                "required" => false,
                "attr" => [
                    "minLength" => 4,
                    "maxLenght" => 255,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Make your choice, please' => null,
                    'Website' => 'Website',
                    'Web App' => 'Web App',
                    'API' => 'API',
                    'App' => 'App',
                    'Mobile App' => 'Mobile App',
                ],
                "required" => true
            ])
            ->add("skills", EntityType::class, [
                "class" => Skills::class,
                "choice_label" => "skill",
                "multiple" => true,
                // "expanded" => true,
                "required" => true,
            ])
            ->add("version", NumberType::class, [
                "required" => true,
                "html5" => true,
                "attr" => [
                    "min" => 1,
                    "value" => 1
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}

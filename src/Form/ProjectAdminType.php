<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Skills;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'label' => 'Title',
                "required" => true,
                "attr" => [
                    "minLenght" => 4,
                    "maxLenght" => 255,
                ]
            ])
            ->add("description", TextareaType::class, [
                "label" => "Description"
            ])
            ->add('imgPath', FileType::class, [
                'label' => 'Image',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => true,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
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
            ->add('githubLink', UrlType::class, [
                'label' => "Link GitHub",
                "required" => false
            ])
            ->add("siteLink", UrlType::class, [
                "label" => "Production Link",
                "required" => false
            ])
            ->add("clientName", null, [
                "label" => "Client name",
                "required" => false,
                "attr" => [
                    "minLength" => 4,
                    "maxLenght" => 255,
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
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
                "required" => true,
            ])
            ->add("version", NumberType::class, [
                "label" => "Version",
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

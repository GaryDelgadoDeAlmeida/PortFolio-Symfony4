<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProfileAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("imgPath", FileType::class, [
                "label" => false,
                'multiple' => false,
                'attr' => [
                    'accept' => 'image/*',
                    "hidden" => true,
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5Mi',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid file image.',
                    ])
                ],
                'mapped' => false,
                'required' => false,
            ])
            ->add("lastName", null, [
                "label" => "Lastname",
                "attr" => [
                    "maxLength" => 100
                ],
                "required" => true,
            ])
            ->add("firstName", null, [
                "label" => "Firstname",
                "attr" => [
                    "maxLength" => 100
                ],
                "required" => true
            ])
            ->add("address", null, [
                "label" => "Address",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add("postalCode", NumberType::class, [
                "label" => "Zip Code",
                "attr" => [
                    "maxLength" => 5
                ],
                "required" => true
            ])
            ->add("city", null, [
                "label" => "City",
                "attr" => [
                    "maxLength" => 255
                ],
                "required" => true
            ])
            ->add("phoneNumber", TelType::class, [
                "label" => "Mobile Phone",
                "attr" => [
                    "maxLength" => 10,
                ],
                "required" => true
            ])
            ->add("email", EmailType::class, [
                "label" => "Email",
                "attr" => [
                    "maxLength" => 255,
                ]
            ])
            ->add("description", TextareaType::class, [
                "label" => "Presentation",
                "required" => true,
                "attr" => [
                    "class" => "h-150px"
                ]
            ])
            ->add("submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => User::class,
        ]);
    }
}

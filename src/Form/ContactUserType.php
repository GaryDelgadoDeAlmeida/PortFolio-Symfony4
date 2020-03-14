<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('senderFullName', null, [
                "required" => true,
                "attr" => [
                    'placeholder' => "Votre Nom"
                ]
            ])
            ->add('senderEmail', EmailType::class, [
                "required" => true,
                "attr" => [
                    'placeholder' => "Votre Email"
                ]
            ])
            ->add('emailSubject', null, [
                "required" => true,
                "attr" => [
                    'placeholder' => "Sujet"
                ]
            ])
            ->add('emailContent', TextareaType::class, [
                "required" => true,
                "attr" => [
                    'placeholder' => "Message"
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}

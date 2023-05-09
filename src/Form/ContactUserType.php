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
            ->add("senderFullName", null, [
                "required" => true,
                "attr" => [
                    "placeholder" => "Votre Nom",
                    "maxLength" => 100
                ]
            ])
            ->add("senderEmail", EmailType::class, [
                "required" => true,
                "attr" => [
                    "placeholder" => "Votre Email",
                    "maxLength" => 255
                ]
            ])
            ->add("emailSubject", null, [
                "required" => true,
                "attr" => [
                    "placeholder" => "Sujet",
                    "maxLength" => 100
                ]
            ])
            ->add("emailContent", TextareaType::class, [
                "required" => true,
                "attr" => [
                    "placeholder" => "Message",
                    "class" => "h-180px",
                    "maxLength" => 1000
                ]
            ])
            ->add("submit", SubmitType::class, [
                "label" => "Envoyer",
                "attr" => [
                    "class" => "btn btn-green"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}

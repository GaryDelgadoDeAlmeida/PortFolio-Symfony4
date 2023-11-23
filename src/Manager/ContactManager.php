<?php

namespace App\Manager;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactManager extends AbstractController {

    private ContactRepository $contactRepository;

    function __construct(ContactRepository $contactRepository) {
        $this->contactRepository = $contactRepository;
    }
    
    public function sendMail($senderEmail, $senderSubject, $senderMessage)
    {
        $error = false;
        $yrEmail = htmlspecialchars($senderEmail);
        $subject = htmlspecialchars($senderSubject);
        $message_txt = htmlspecialchars($senderMessage);
        $headers = [
            "X-Mailer" => "PHP/" . phpversion(),
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=iso-8859-1",
            "From: no-reply@garry-almeida.com",
            "Reply-To: no-reply@garry-almeida.com",
        ];

        $message_html = $this->renderView("modele/mail.html.twig", [
            "sender" => $senderEmail,
            "content" => $senderMessage
        ]);
        
        try {
            if(!mail("gary.almeida.work@gmail.com", $subject, $message_html, implode("\r\n", $headers))) {
                return [
                    "answer" => false,
                    "response" => [
                        "class" => "danger",
                        "message" => "Votre message n'a pu être envoyé, veuillez réessayer un peu plus tard."
                    ]
                ];
            }
        } catch(\Exception $e) {
            return [
                "answer" => false,
                "response" => [
                    "class" => "danger",
                    // "message" => "Une erreur interne a été rencontrée, veuillez réessayer ultérieurement."
                    "message" => $e->getMessage()
                ]
            ];
        }

        return [
            "answer" => true,
            "response" => [
                "class" => "success",
                "message" => "Votre message a bien été envoyé. Je vous remercie d'avoir pris contact avec moi."
            ]
        ];
    }

    /**
     * @param Contact contact
     * @return Contact
     */
    public function setEmailToRead(Contact $contact) 
    {
        if(!$contact->getIsRead()) {
            $contact->setIsRead(true);
            $this->contactRepository->save($contact, true);
        }

        return $contact;
    }
}
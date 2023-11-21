<?php

namespace App\Manager;

use App\Entity\Contact;
use App\Repository\ContactRepository;

class ContactManager {

    private ContactRepository $contactRepository;

    function __construct(ContactRepository $contactRepository) {
        $this->contactRepository = $contactRepository;
    }
    
    public function sendMail($senderFullName, $senderEmail, $senderSubject, $senderMessage)
    {
        header("Content-type:aplication/json;charset=utf8");

        $error = false;

        // Le mail de récéption
        $rptEmail = 'gary.almeida.work@gmail.com';
        $name = htmlspecialchars($senderFullName);

        // Le mail a l'origine de l'envoie
        $yrEmail = htmlspecialchars($senderEmail);
        
        //=====Définition du sujet.
        $subject = htmlspecialchars($senderSubject);
        
        //=========
        $message_txt = htmlspecialchars($senderMessage);

        if (empty($name) || empty($yrEmail) || empty($subject) || empty($message_txt)) {
            $error = true;
        } else {

            // On filtre les serveurs qui rencontrent des bogues.
            if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $yrEmail)) {
                $passage_ligne = "\r\n";
            } else {
                $passage_ligne = "\n";
            }

            //=====Déclaration des messages au format texte et au format HTML.
            $message_html = "<html><head></head><body>{$message_txt}</body></html>";
            
            //=====Création de la boundary
            $boundary = "-----=".md5(rand());
            
            //=====Création du header de l'e-mail.
            $header = "From: \"{$name}\"<no-reply@garry-almeida.com>{$passage_ligne}";
            $header.= "Reply-to: \"Garry Almeida\" <{$rptEmail}>{$passage_ligne}";
            $header.= "MIME-Version: 1.0{$passage_ligne}";
            $header.= "Content-Type: multipart/alternative;{$passage_ligne} boundary=\"{$boundary}\"{$passage_ligne}";
            
            //=====Création du message.
            $message = "{$passage_ligne}--{$boundary}{$passage_ligne}";
            
            //=====Ajout du message au format texte.
            $message.= "Content-Type: text/plain; charset=\"ISO-8859-1\"{$passage_ligne}";
            $message.= "Content-Transfer-Encoding: 8bit{$passage_ligne}";
            $message.= $passage_ligne.$message_txt.$passage_ligne;
            
            $message.= "{$passage_ligne}--{$boundary}{$passage_ligne}";
            //=====Ajout du message au format HTML
            $message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
            $message.= "Content-Transfer-Encoding: 8bit{$passage_ligne}";
            $message.= $passage_ligne.$message_html.$passage_ligne;

            $message.= "{$passage_ligne}--{$boundary}--{$passage_ligne}";
            $message.= "{$passage_ligne}--{$boundary}--{$passage_ligne}";

            
            try {
                //=====Envoi de l'e-mail.
                if(mail($rptEmail, $subject, $message, $header)) {
                    $error = true;
                }
            } catch(\Exception $e) {
                return [
                    "answer" => false,
                    "response" => [
                        "class" => "danger",
                        "message" => "Une erreur interne a été rencontrée, veuillez réessayer ultérieurement."
                    ]
                ];
            }
        }

        $response = [
            "answer" => true,
            "response" => [
                "class" => "success",
                "message" => "Votre message a été envoyé."
            ]
        ];
        
        // if an error has been found
        if( $error ) {
            $response = [
                "answer" => false,
                "response" => [
                    "class" => "danger",
                    "message" => "Votre message n'a pu être envoyé, veuillez remplir tous les champs."
                ]
            ];
        }

        return $response;
    }

    /**
     * @param Contact contact
     * @return Contact
     */
    public function setEmailToRead(Contact $contact) 
    {
        if(!$contact->getIsRead()) {
            $contact->setIsRead(true);
            $this->contactRepository->add($contact, true);
        }

        return $contact;
    }
}
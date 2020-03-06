<?php

namespace App\Manager;

class ContactManager {
    
    function sendMail($senderFullName, $senderEmail, $senderSubject, $senderMessage)
    {
        header("Content-type:aplication/json;charset=utf8");

        $error = false;

        $rptEmail = 'gary.almeida.work@gmail.com'; // Le mail de récéption
        $name = htmlspecialchars($senderFullName);
        $yrEmail = htmlspecialchars($senderEmail); // Le mail a l'origine de l'envoie
        //=====Définition du sujet.
        $subject = htmlspecialchars($senderSubject);
        //=========
        $message_txt = htmlspecialchars($senderMessage);

        if (empty($name) || empty($yrEmail) || empty($subject) || empty($message_txt)) {
            # code...
            $error = true;
        }
        else
        {
            if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $yrEmail)) // On filtre les serveurs qui rencontrent des bogues.
            {
                $passage_ligne = "\r\n";
            }
            else
            {
                $passage_ligne = "\n";
            }

            //=====Déclaration des messages au format texte et au format HTML.
            $message_html = "<html><head></head><body>". $message_txt ."</body></html>";
            //==========
            
            //=====Création de la boundary
            $boundary = "-----=".md5(rand());
            //==========
            
            //=====Création du header de l'e-mail.
            $header = "From: \"$name\"<".$yrEmail.">".$passage_ligne;
            $header.= "Reply-to: \"Garry Almeida\" <".$rptEmail.">".$passage_ligne;
            $header.= "MIME-Version: 1.0".$passage_ligne;
            $header.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne;
            //==========
            
            //=====Création du message.
            $message = $passage_ligne."--".$boundary.$passage_ligne;
            //=====Ajout du message au format texte.
            $message.= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
            $message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
            $message.= $passage_ligne.$message_txt.$passage_ligne;
            //==========
            $message.= $passage_ligne."--".$boundary.$passage_ligne;
            //=====Ajout du message au format HTML
            $message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
            $message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
            $message.= $passage_ligne.$message_html.$passage_ligne;
            //==========
            $message.= $passage_ligne."--".$boundary."--".$passage_ligne;
            $message.= $passage_ligne."--".$boundary."--".$passage_ligne;
            //==========
            
            //=====Envoi de l'e-mail.
            if(mail($rptEmail, $subject, $message, $header) != true) {
                $error = true;
            }
        }

        if ($error == false) {
            # code...
            $data['answer'] = true;
            $data['content'] = "Votre message a été envoyé.";
        }
        else
        {
            $data['answer'] = false;
            $data['content'] = "Votre message n'a pu être envoyé, veuillez remplir tous les champs.";	
        }

        return $data;
    }
}
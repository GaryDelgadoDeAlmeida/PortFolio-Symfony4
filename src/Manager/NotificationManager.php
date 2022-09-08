<?php

namespace App\Manager;

class NotificationManager {

    public function returnNotification(string $notificationType, string $message)
    {
        $response = [
            "class" => $notificationType,
            "icon" => "",
            "content" => $message
        ];
        
        if($notificationType == "success") {
            $response["icon"] = "/content/images/svg/checkmark-green.svg";
        } elseif($notificationType == "information") {
            $response["icon"] = "/content/images/svg/informationmark-gray.svg";
        } elseif($notificationType == "warning") {
            $response["icon"] = "/content/images/svg/questionmark-yellow.svg";
        } elseif($notificationType == "danger") {
            $response["icon"] = "/content/images/svg/closemark-red.svg";
        }

        return $response;
    }
}
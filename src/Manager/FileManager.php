<?php

namespace App\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager {

    /**
     * @param UploadedFile The uploaded file by a form
     * @param string The repo who will be saved the file
     * @return string Return the path
     */
    public function moveFileToDestinationRepository(UploadedFile $uploadedFile, string $newFilename, string $destinationRepository, string $relativeDestinationRepo)
    {
        $response = [];

        try {
            
            // Check if in the destination repo, a file with the same name exist and delete it.
            if(array_search("{$relativeDestinationRepo}{$newFilename}", glob("{$relativeDestinationRepo}*.{$uploadedFile->guessExtension()}"))) {
                unlink("{$relativeDestinationRepo}{$newFilename}");   
            }
            
            // Move the file to the directory where brochures are stored
            $uploadedFile->move($destinationRepository, $newFilename);

            // Return a response to the user
            $response = [
                "response" => true,
                "path" => "{$relativeDestinationRepo}{$newFilename}"
            ];
        } catch(\Exception $e) {
            $response = [
                "response" => false,
                "content" => $e->getMessage()
            ];
        } finally {}

        return $response;
    }
}
<?php

namespace App\Controller\Backoffice;

use App\Entity\User;
use App\Manager\FileManager;
use App\Form\ProfileAdminType;
use App\Form\UpdatePasswordType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/profile", name="admin_profile_")
 */
class ProfileController extends AbstractController
{
    private User $user;
    private FileManager $fileManager;
    private UserRepository $userRepository;

    function __construct(
        Security $security, 
        FileManager $fileManager,
        UserRepository $userRepository
    ) {
        $this->user = $security->getUser();
        $this->fileManager = $fileManager;
        $this->userRepository = $userRepository;
    }
    
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $updatePwdMessage = $response = [];

        // ProfileAdminType
        $formProfile = $this->createForm(ProfileAdminType::class, $this->user);
        $formProfile->handleRequest($request);

        // UpdatePasswordType
        $formUpdatePwd = $this->createForm(UpdatePasswordType::class);
        $formUpdatePwd->handleRequest($request);

        // ProfileAdminType
        if($formProfile->isSubmitted()) {
            if($formProfile->isValid()) {
                try {
                    // Check if a file image need to be treated
                    $fileImage = $formProfile["imgPath"]->getData();
                    if(!empty($fileImage)) {
                        $responseTreatImg = $this->fileManager->moveFileToDestinationRepository($fileImage, "me.{$fileImage->guessExtension()}", "{$this->getParameter("public_dir")}content/profile/", "./content/profile/");
                        if($responseTreatImg["response"]) {
                            $this->user->setImgPath($responseTreatImg["path"]);
                        }
                    }
                    
                    // Persist & save all changes
                    $this->userRepository->save($this->user, true);
    
                    // Return a message to the user
                    $response = [
                        "class" => "success",
                        "message" => "La mise à jour s'est correctement éffectuée."
                    ];
                } catch(\Exeception $e) {
                    $response = [
                        "class" => "danger",
                        "message" => $e->getMessage()
                    ];
                } finally {}
            } else {
                $response = [
                    "class" => "danger",
                    "message" => "Une erreur a été rencontrée avec un ou plusieurs champs."
                ];
            }
        }

        // UpdatePasswordType
        if($formUpdatePwd->isSubmitted() && $formUpdatePwd->isValid()) {
            $formUpdatePwdData = $formUpdatePwd-getData();

            // Check if the password is the same of the old one
            if($this->user->getPassword() == $formUpdatePwdData["oldPassword"]) {

                // Check if the old password and the new one have diffirent value
                if($this->user->getPassword() != $formUpdatePwdData["newPassword"]) {
                    
                    // Check if the new password and the confirmation field have the exact same value
                    if($formUpdatePwdData["newPassword"] === $formUpdatePwdData["confirmNewPassword"]) {

                        // Check if the new password have the minimum requierement
                        if(
                            preg_match(RegexService::SECURE_PASSWORD, $formUpdatePwdData["newPassword"]) && 
                            preg_match(RegexService::ONLY_NUMERIC, $formUpdatePwdData["newPassword"])
                        ) {
                            try {
                                // Persist & save all changes
                                $this->userRepository->save($this->user, true);
                                
                                // Return a message to the user
                                $updatePwdMessage = [
                                    "class" => "success",
                                    "message" => "La mise à jour s'est correctement éffectuée."
                                ];
                            } catch(\Exeception $e) {
                                $updatePwdMessage = [
                                    "class" => "danger",
                                    "message" => $e->getMessage()
                                ];
                            } finally {}
                        } else {
                            $updatePwdMessage = [
                                "class" => "danger",
                                "message" => "Le mot de passe doit contenir des caractères spéciaux et des nombres en plus de sa longue minimale"
                            ];
                        }
                    }
                }
            }
        }

        return $this->render('admin/profile/index.html.twig', [
            "user" => $this->user,
            "form" => $formProfile->createView(),
            "pwdForm" => $formUpdatePwd->createView(),
            "response" => $response,
            "updatePwdMessage" => $updatePwdMessage,
        ]);
    }
}

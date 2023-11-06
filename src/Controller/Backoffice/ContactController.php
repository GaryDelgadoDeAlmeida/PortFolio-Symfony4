<?php

namespace App\Controller\Backoffice;

use App\Entity\Contact;
use App\Manager\ContactManager;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/contact", name="admin_contact_")
 */
class ContactController extends AbstractController
{
    private ContactManager $contactManager;
    private ContactRepository $contactRepository;

    function __construct(ContactManager $contactManager, ContactRepository $contactRepository) {
        $this->contactManager = $contactManager;
        $this->contactRepository = $contactRepository;
    }
    
    /**
     * @Route("/", name="index")
     * @Route("/page/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="index_by_page")
     */
    public function index(Request $request, int $page = 1)
    {
        $limit = 20;
        $page = $page >= 1 ? $page : 1;

        return $this->render("Admin/Contact/index.html.twig", [
            "title" => "Contact",
            "offset" => $page,
            "list_mail" => $this->contactRepository->getMails($page, $limit),
            "total_page" => ceil($this->contactRepository->countContact() / $limit)
        ]);
    }

    /**
     * @Route("/remove-all", name="remove_all")
     */
    public function remove_all_mail(Request $request) 
    {
        // Get find all contacts
        $contacts = $this->contactRepository->findAll();

        // Remove all contact into database
        foreach($contacts as $contact) {
            $this->contactRepository->remove($contact, true);
        }

        // Redirect the user the specified route (but the button linked to the route is on the redirect route => recharge / reload the page)
        return $this->redirectToRoute("admin_contact_index");
    }

    /**
     * @Route("/{id}", requirements={"id" = "^\d+(?:\d+)?$"}, name="show")
     */
    public function show_mail(int $id)
    {
        $contact = $this->contactRepository->find($id);
        if(empty($contact)) {
            return $this->redirectToRoute("admin_contact_index");
        }
        
        return $this->render("Admin/Contact/read.html.twig", [
            "title" => "Contact",
            "mail" => $this->contactManager->setEmailToRead($contact)
        ]);
    }

    /**
     * @Route("/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="remove", methods={"DELETE"})
     */
    public function remove_mail(Request $request, int $id): JsonResponse
    {
        // START TEMPOTAIRE : Check the email id is a positive integer
        $id = 0;
        if($id < 1) {
            return $this->json([
                "response" => [
                    "class" => "danger",
                    "message" => "An error has been found with the email id. The removal process has been cancelled."
                ]
            ], Response::HTTP_FORBIDDEN);
        }
        // END TEMPOTAIRE : Check the email id is a positive integer

        // Get the mail in the database
        $email = $this->contactRepository->find($id);
        if(empty($email)) {
            return $this->json([
                "response" => [
                    "class" => "danger",
                    "message" => "An error has been found with the email id. The removal process has been cancelled."
                ]
            ]);
        }

        try {
            // Remove the email and save the changes in the database
            $this->contactRepository->remove($email, true);

            // Return a response to the user
            return $this->json([
                "response" => [
                    "class" => "success",
                    "message" => "The email sended by {$email->getSenderEmail()} has been successfully removed."
                ]
            ]);
        } catch(Exception $e) {
            return $this->json([
                "response" => [
                    "class" => "danger",
                    "message" => $e->getMesage()
                ]
            ]);
        }
    }
}

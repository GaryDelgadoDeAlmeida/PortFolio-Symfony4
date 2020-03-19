<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Contact;
use App\Entity\Project;
use App\Form\LoginAdminType;
use App\Form\ContactUserType;
use App\Form\RegisterAdminType;
use App\Manager\ContactManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('User/home.html.twig');
    }

    /**
     * @Route("/about", name="aboutme")
     */
    public function about_me()
    {
        return $this->render('User/about.html.twig', [
            'lastestProject' => $this->getDoctrine()->getRepository(Project::class)->getLastestProject(),
            'userFullName' => $this->getDoctrine()->getRepository(User::class)->getFullName()
        ]);
    }

    /**
     * @Route("/portfolio", name="portfolio")
     */
    public function portfolio()
    {
        return $this->render('User/portFolio.html.twig', [
            "portfolio" => $this->getDoctrine()->getRepository(Project::class)->getProject()
        ]);
    }

    /**
     * @Route("/contact", name="contactme")
     */
    public function contact_me(Request $request, EntityManagerInterface $manager)
    {
        $newSend = new Contact();
        $formContact = $this->createForm(ContactUserType::class, $newSend);
        $formContact->handleRequest($request);

        if($formContact->isSubmitted() && $formContact->isValid()) {
            $response = ContactManager::sendMail($newSend->getSenderFullName(), $newSend->getSenderEmail(), $newSend->getEmailSubject(), $newSend->getEmailContent());
            if(isset($response["answer"]) && $response["answer"] == true) {
                $manager->persist($user);
                $manager->flush();
            }
        }

        return $this->render('User/contact.html.twig', [
            "contactForm" => $formContact->createView(),
            "response" => isset($response) ? $response : ""
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('User/login.html.twig', [
            "formLogin" => $this->createForm(LoginAdminType::class)->createView()
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $formRegister = $this->createForm(RegisterAdminType::class, $user);
        $formRegister->handleRequest($request);

        if($formRegister->isSubmitted() && $formRegister->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            $user->setRoles(['ROLE_ADMIN']);
            $user->setCreatedAt(new \Datetime());
            $manager->persist($user);
            $manager->flush();
            
            return $this->redirectToRoute('login');
        }

        return $this->render('User/register.html.twig', [
            "formRegister" => $formRegister->createView()
        ]);
    }
}

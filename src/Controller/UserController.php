<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\About;
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
        $userFullName = $this->getDoctrine()->getRepository(User::class)->getFullName();
        return $this->render('User/about.html.twig', [
            'userFullName' => $userFullName,
            'userIntro' => $this->getDoctrine()->getRepository(About::class)->getIntroByName($userFullName["firstName"], $userFullName["lastName"]),
            'lastestProject' => $this->getDoctrine()->getRepository(Project::class)->getLastestProject()
        ]);
    }

    /**
     * @Route("/portfolio", name="portfolio")
     * @Route("/portfolio/{page}", requirements={"id" = "^\d+(?:\d+)?$"}, name="portfolioPage")
     */
    public function portfolio($page = 0)
    {
        return $this->render('User/portFolio.html.twig', [
            "portfolio" => $this->getDoctrine()->getRepository(Project::class)->getProject($page, 15)
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
                $newSend->setEmailContent(json_encode($newSend->getEmailContent()));
                $newSend->setIsRead(false);
                $manager->persist($newSend);
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

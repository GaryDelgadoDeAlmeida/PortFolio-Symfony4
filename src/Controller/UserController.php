<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginAdminType;
use App\Form\RegisterAdminType;
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
        return $this->render('User/about.html.twig');
    }

    /**
     * @Route("/about/2", name="aboutme_2")
     */
    public function about_me_2()
    {
        return $this->render('User/about_2.html.twig');
    }

    /**
     * @Route("/portfolio", name="portfolio")
     */
    public function portfolio()
    {
        return $this->render('User/portFolio.html.twig', [
            "portfolio" => []
        ]);
    }

    /**
     * @Route("/contact", name="contactme")
     */
    public function contact_me()
    {
        return $this->render('User/contact.html.twig');
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

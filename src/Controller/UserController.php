<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
}

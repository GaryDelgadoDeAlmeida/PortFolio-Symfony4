<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin()
    {
        return $this->render('Admin/home.html.twig');
    }

    /**
     * @Route("/admin/about", name="adminAbout")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_about()
    {
        return $this->render('Admin/about.html.twig');
    }

    /**
     * @Route("/admin/portfolio", name="adminPortfolio")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_portfolio()
    {
        return $this->render('Admin/portfolio.html.twig');
    }

    /**
     * @Route("/admin/contact", name="adminContact")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_contact()
    {
        return $this->render('Admin/contact.html.twig');
    }

    /**
     * @Route("/admin/news", name="adminNews")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_news()
    {
        return $this->render('Admin/news.html.twig');
    }

    /**
     * @Route("/admin/logout", name="adminLogout")
     * @IsGranted("ROLE_ADMIN")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin_logout()
    {
        return $this->render('User/home.html.twig');
    }
}

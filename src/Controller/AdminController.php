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
     * @IsGranted("ROLES_ADMIN")
     * @Security("is_granted('ROLES_ADMIN')")
     */
    public function admin()
    {
        return $this->render('Admin/home.html.twig');
    }

    /**
     * @Route("/admin/about", name="adminAbout")
     * @IsGranted("ROLES_ADMIN")
     * @Security("is_granted('ROLES_ADMIN')")
     */
    public function admin_about()
    {
        return $this->render('Admin/about.html.twig');
    }

    /**
     * @Route("/admin/portfolio", name="adminPortfolio")
     * @IsGranted("ROLES_ADMIN")
     * @Security("is_granted('ROLES_ADMIN')")
     */
    public function admin_portfolio()
    {
        return $this->render('Admin/portfolio.html.twig');
    }

    /**
     * @Route("/admin/contact", name="adminContact")
     * @IsGranted("ROLES_ADMIN")
     * @Security("is_granted('ROLES_ADMIN')")
     */
    public function admin_contact()
    {
        return $this->render('Admin/contact.html.twig');
    }

    /**
     * @Route("/admin/news", name="adminNews")
     * @IsGranted("ROLES_ADMIN")
     * @Security("is_granted('ROLES_ADMIN')")
     */
    public function admin_news()
    {
        return $this->render('Admin/news.html.twig');
    }

    /**
     * @Route("/admin/login", name="adminLogin")
     */
    public function admin_login()
    {
        return $this->render('Admin/login.html.twig');
    }

    /**
     * @Route("/admin/logout", name="adminLogout")
     * @IsGranted("ROLES_ADMIN")
     * @Security("is_granted('ROLES_ADMIN')")
     */
    public function admin_logout()
    {
        return $this->render('User/home.html.twig');
    }
}

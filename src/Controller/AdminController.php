<?php

namespace App\Controller;

use App\Manager\ExperienceManager;
use App\Repository\ContactRepository;
use App\Repository\ProjectRepository;
use App\Repository\EducationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * 
 * @IsGranted("ROLE_ADMIN")
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    private ExperienceManager $experienceManager;
    private ProjectRepository $projectRepository;
    private ContactRepository $contactRepository;
    private EducationRepository $educationRepository;

    function __construct(
        ExperienceManager $experienceManager,
        ContactRepository $contactRepository,
        ProjectRepository $projectRepository,
        EducationRepository $educationRepository
    ) {
        $this->experienceManager = $experienceManager;
        $this->projectRepository = $projectRepository;
        $this->contactRepository = $contactRepository;
        $this->educationRepository = $educationRepository;
    }
    
    /**
     * @Route("/", name="home")
     */
    public function admin()
    {
        return $this->render('admin/home.html.twig', [
            "nbrVisitors" => 1,
            "nbrProjects" => $this->projectRepository->countProject(),
            "workExp" => $this->experienceManager->countYearEXP(),
            "latestWork" => $this->projectRepository->getLastestProject(),
            "latestEmail" => $this->contactRepository->getLatestMails(),
            "latestExp" => $this->educationRepository->getLatestEducationFromCategory("experience")
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function admin_logout()
    {
        return $this->render('user/home.html.twig');
    }
}

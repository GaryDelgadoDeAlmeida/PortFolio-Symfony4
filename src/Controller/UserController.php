<?php

namespace App\Controller;

use App\Manager\ContactManager;
use App\Form\{
    ContactUserType, 
    LoginAdminType
};
use App\Entity\{
    Contact, 
    Education, 
    Project, 
    Skills
};
use App\Repository\{
    ContactRepository, 
    UserRepository, 
    PriceRepository,
    SkillsRepository, 
    ProjectRepository, 
    ServiceRepository, 
    EducationRepository
};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private EntityManagerInterface $em;
    private ContactManager $contactManager;
    private UserRepository $userRepository;
    private PriceRepository $priceRepository;
    private SkillsRepository $skillsRepository;
    private ProjectRepository $projectRepository;
    private ServiceRepository $serviceRepository;
    private ContactRepository $contactRepository;
    private EducationRepository $educationRepository;

    function __construct(
        EntityManagerInterface $em, 
        ContactManager $contactManager,
        UserRepository $userRepository,
        PriceRepository $priceRepository,
        SkillsRepository $skillsRepository,
        ProjectRepository $projectRepository,
        ServiceRepository $serviceRepository,
        ContactRepository $contactRepository,
        EducationRepository $educationRepository
    ) {
        $this->em = $em;
        $this->contactManager = $contactManager;
        $this->userRepository = $userRepository;
        $this->priceRepository = $priceRepository;
        $this->skillsRepository = $skillsRepository;
        $this->projectRepository = $projectRepository;
        $this->serviceRepository = $serviceRepository;
        $this->contactRepository = $contactRepository;
        $this->educationRepository = $educationRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        $response = $orderedSkills = [];
        $formContact = $this->createForm(ContactUserType::class, $contact = new Contact());
        $formContact->handleRequest($request);
        $captchat = [
            "question" => "Combien fait 2 x 2 ?",
            "answer" => 4
        ];

        $skills = $this->skillsRepository->getSkillsOrderedByCategory();
        foreach($skills as $skill) {
            $orderedSkills[$skill->getType()][] = $skill;
        }

        // If form is submitted
        if($formContact->isSubmitted()) {

            // If all field is valid
            if($formContact->isValid()) {
                
                // If captchat has been filled and the answer if correct => Prevent mail spamming
                if($request->request->get("captchat") == $captchat["answer"]) {
                    
                    // Send an email to the admin
                    ["answer" => $answer, "response" => $response] = $this->contactManager->sendMail(
                        $contact->getSenderEmail(), // Sender fullname
                        $contact->getSenderEmail(), // Sender email
                        $contact->getEmailSubject(), // Email subject
                        $contact->getEmailContent() // Email content
                    );
                    
                    // If the email has been send then, save the data into database
                    if($answer) {
                        $contact
                            ->setSenderFullName($contact->getEmailContent())
                            ->setEmailContent($contact->getEmailContent())
                            ->setIsRead(false)
                            ->setCreatedAt(new \DateTimeImmutable())
                        ;
                        
                        // Save into database
                        $this->contactRepository->persist($contact, true);
                    }
                } else {
                    $response = [
                        "class" => "danger",
                        "message" => "Le captchat est incorrect."
                    ];
                }
            } else {
                $response = [
                    "class" => "danger",
                    "message" => "Une erreur a été rencontrée avec l'un des champs. Veuillez vérifier que tous les champs soit bien rempli."
                ];
            }
        }

        return $this->render("user/home.html.twig", [
            "response" => $response,
            "user" => $this->userRepository->getUserByID(),
            "services" => $this->serviceRepository->findAll(),
            "skillCategories" => $orderedSkills,
            "portfolios" => $this->projectRepository->getLastestProject(6),
            "educations" => $this->educationRepository->getLatestEducationFromCategory("experience", 5),
            "priceServices" => $this->priceRepository->findAll(),
            "contactForm" => $formContact->createView(),
            "captchat" => $captchat
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Security $security)
    {
        if($security->getUser()) {
            return $this->redirectToRoute("admin_home");
        }

        return $this->render("User/login.html.twig", [
            "formLogin" => $this->createForm(LoginAdminType::class)->createView()
        ]);
    }
}

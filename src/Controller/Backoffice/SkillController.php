<?php

namespace App\Controller\Backoffice;

use App\Entity\Skills;
use App\Form\SkillsType;
use App\Manager\NotificationManager;
use App\Repository\SkillsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/skill", name="admin_skill_")
 */
class SkillController extends AbstractController
{
    private NotificationManager $notificationManager;
    private SkillsRepository $skillsRepository;
    
    function __construct(NotificationManager $notificationManager, SkillsRepository $skillsRepository) {
        $this->notificationManager = $notificationManager;
        $this->skillsRepository = $skillsRepository;
    }
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $print = !empty($request->get("print")) ? $request->get("print") : "frontend";
        $skill = new Skills();
        $skill->setType($print);
        $formSkills = $this->createForm(SkillsType::class, $skill);
        $formSkills->handleRequest($request);
        $response = !empty($request->get("response")) ? json_decode(urldecode($request->get("response")), true) : [];

        if($formSkills->isSubmitted() && $formSkills->isValid()) {

            // If the skill to add already still no exist in the category, then add it. Else do nothing
            if(empty($this->skillsRepository->searchSkill($skill->getSkill(), $skill->getType()))) {
                try {
                    $skill->setCreatedAt(new \DateTimeImmutable());
                    $this->skillsRepository->add($skill, true);

                    $response = $this->notificationManager->returnNotification("success", "The skill '{$skill->getSkill()}' has been successfully added to the '{$skill->getType()}' category");
                } catch(\Exception $e) {
                    $response = $this->notificationManager->returnNotification("danger", "An error has been encountered : {$e->getMessage()}");
                } finally {}
            } else {
                $response = $this->notificationManager->returnNotification("warning", "The skill '{$skill->getSkill()}' already exist in the '{$skill->getType()}' category.");
            }

            // Re-instenciate the form to clear all fields
            $formSkills = $this->createForm(SkillsType::class, (new Skills())->setType($print));
        }

        return $this->render("Admin/Profile/skill.html.twig", [
            "print" => $print,
            "skills" => $this->skillsRepository->getSkillsByCategory($print),
            "formSkills" => $formSkills->createView(),
            "response" => $response,
        ]);
    }

    /**
     * @Route("/{id}/remove", requirements={"id" = "^\d+(?:\d+)?$"}, name="remove", methods={"GET", "DELETE"})
     */
    public function delete_skill(int $id)
    {
        // Search the skill
        $skill = $this->skillsRepository->find($id);
        if(empty($skill)) {
            return $this->redirectToRoute("admin_skill_index", [
                "response" => urlencode(
                    json_encode(
                        $this->notificationManager->returnNotification(
                            "warning", 
                            "The skill hasn't been found."
                        ), 
                    JSON_UNESCAPED_UNICODE)
                )
            ]);
        }

        try {

            // Remove all associations with any projects
            foreach($skill->getProjects() as $project) {
                $skill->removeProject($project);
            }
            
            // Remove the skill from the database
            $this->skillsRepository->remove($skill, true);

            // Return a message to the user
            return $this->redirectToRoute("admin_skill_index", [
                "response" => urlencode(
                    json_encode(
                        $this->notificationManager->returnNotification(
                            "success", 
                            "The skill {$skill->getSkill()} has been successfully deleted."
                        ), 
                    JSON_UNESCAPED_UNICODE)
                )
            ]);
        } catch(Exception $e) {
            return $this->redirectToRoute("admin_skill_index", [
                "response" => urlencode(
                    json_encode(
                        $this->notificationManager->returnNotification(
                            "danger", 
                            "An error has been encountered : {$e->getMessage()}"
                        ), 
                    JSON_UNESCAPED_UNICODE)
                )
            ]);
        }
    }
}

<?php

namespace App\Manager;

use App\Entity\Education;
use App\Repository\EducationRepository;

class ExperienceManager {

    private float $daysInAYear = 365.25;
    private int $daysInAMonth = 30;
    private int $monthInAYear = 12;

    private EducationRepository $educationRepository;

    function __construct(EducationRepository $educationRepository) {
        $this->educationRepository = $educationRepository;
    }

    /**
     * @return double Return the number of year of all professionnal experiences
     */
    public function countYearEXP()
    {
        $experinces = $this->educationRepository->findBy(["category" => "experience"]);
        if(!$experinces) {
            return 0;
        }

        $yearExperience = $days = 0;
        foreach($experinces as $experince) {
            $diff = date_diff($experince->getStartDate(), $experince->getInProgress() ? new \DateTimeImmutable() : $experince->getEndDate());
            $days += $diff->days;
        }

        if($days > 0) {
            $yearExperience = ($days / $this->daysInAMonth) / $this->monthInAYear;
        }

        return $yearExperience;
    }
}
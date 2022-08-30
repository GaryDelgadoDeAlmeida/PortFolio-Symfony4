<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InitializePasswordCommand extends Command
{
    protected static $defaultName = 'app:initialize:password';
    private $container;
    private $encoder;
    private $manager;

    public function __construct(ContainerInterface $container, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder) {
        parent::__construct();
        $this->container = $container;
        $this->encoder = $encoder;
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Force a new password to access admin dashboard')
            ->addArgument('pwd', InputArgument::OPTIONAL, 'Force yourself a password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newPwd = $input->getArgument('pwd') ? $input->getArgument('pwd') : $this->randomPassword();
        $user = $this->manager->getRepository(User::class)->getUserByID(1);
        $user->setPassword($this->encoder->encodePassword($user, $newPwd));
        
        try {
            $this->manager->persist($user);
            $this->manager->flush();

            $output->writeln("Your password has been changed. This is the new one : {$newPwd}");
            return 0;
        } catch(Exception $e) {
            $output->writeln("An error has been found : {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * @param int password lenght
     * @return string Return the generated password
     */
    private function randomPassword(int $pwdLenght = 20) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass); //turn the array into a string
    }
}

<?php

namespace App\Command;

use App\Repository\UserRepository;
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
    
    private ContainerInterface $container;
    private EntityManagerInterface $manager;
    private UserPasswordEncoderInterface $encoder;
    private UserRepository $userRepository;

    public function __construct(
        ContainerInterface $container, 
        UserRepository $userRepository, 
        UserPasswordEncoderInterface $encoder
    ) {
        parent::__construct();
        $this->container = $container;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Force a new password to access admin dashboard')
            ->addArgument("email", InputArgument::REQUIRED, "The user email")
            ->addArgument('pwd', InputArgument::OPTIONAL, 'Force yourself a password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument("email");
        $newPwd = $input->getArgument('pwd') ? $input->getArgument('pwd') : $this->randomPassword();
        $user = $this->userRepository->findOneBy(["email" => $email]);
        if(!$user) {
            $output->writeln("The email '{$email}' don't exist.");
            return 0;
        }
        
        try {
            $user->setPassword($this->encoder->encodePassword($user, $newPwd));
            $this->userRepository->save($user, true);
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

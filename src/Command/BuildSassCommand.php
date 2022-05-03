<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Formatter\Expanded;

class BuildSassCommand extends Command
{
    private $params;
    protected static $defaultName = 'app:build:sass';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(ParameterBagInterface $params)
    {
        parent::__construct();
        $this->params = $params;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create an instance of the Sass Compiler class
        $scss = new Compiler(new Expanded());

        // Path to the folder of the sass files
        $sassFilesPath = $this->params->get('kernel.project_dir') . '/public/assets/sass/';
    
        // Path to the folder of css files
        $cssFilesPath = $this->params->get('kernel.project_dir') . '/public/assets/css/';

        // Write output css file
        file_put_contents(
            $cssFilesPath. "main.css",
            // Set the content of all.css the output of the
            // sass compiler from the file all.scss
            $scss->compile(
                // Read content of all.scss
                file_get_contents(
                    $sassFilesPath. "index.scss"
                )
            )
        );

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}

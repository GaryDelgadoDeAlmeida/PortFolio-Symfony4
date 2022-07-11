<?php

namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Psr\Container\ContainerInterface;

class FileExistExtension extends AbstractExtension
{
    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exist', [$this, 'fileExist']),
        ];
    }

    public function fileExist($value, $alternativePath = true)
    {
        if(empty($value) || !file_exists($this->container->getParameter("public_dir") . $value)) {
            if($alternativePath) {
                $value = "content/images/thumbnail.jpg";
            } else {
                $value = "";
            }
        }

        return $value;
    }
}

<?php

namespace App\Twig\Runtime;

use App\Repository\SettingsRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    private $repo;

    public function __construct(SettingsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function doSomething()
    {
        return $this->repo->findOneById(2)->getscript();
    }
}

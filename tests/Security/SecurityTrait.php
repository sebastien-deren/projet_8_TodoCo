<?php
declare(strict_types=1);
namespace Tests\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait SecurityTrait{
    protected function loginInAsUser(EntityManagerInterface $em,string $name = 'test'):User|null
    {
        $user = $em->getRepository(User::class)->findOneByUsername($name) ??null;
        if(isset($this->client) && $this->client instanceof KernelBrowser){
            $this->client->loginUser($user);
        }
        return $user;

    }
}

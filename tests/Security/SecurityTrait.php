<?php
declare(strict_types=1);
namespace Tests\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait SecurityTrait{
    protected function loginInAsUser(EntityManagerInterface $em)
    {
        if(isset($this->client) && $this->client instanceof KernelBrowser){
            //WE WILL CHANGE THIS WHEN WE IMPLEMENT GetROLES in out App
            $this->client->loginUser($em->getRepository(User::class)->findOneByUsername('seb'));
        }

    }
}

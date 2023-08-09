<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;

class UserService{
    public function __construct(private UserRepository $userRepository){
        
    }
    public function createAnon():User
    {
        $anon = new User();
        $anon->setEmail('anonymous@test.com');
        $anon->setPassword('none');
        $anon->setUsername('anonymous');
        $this->userRepository->save($anon,true);
        return $anon;
    }
    public function canDeleteTask(User $creator,User $user,bool $isAdmin):bool
    {
        if($isAdmin && 'anonymous'===$creator->getUsername()){
            return true;
        }
        if($creator == $user ){
            return true;
        }
        return false;


    }
}

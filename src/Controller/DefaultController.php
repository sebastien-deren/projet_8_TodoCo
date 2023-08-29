<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'homepage',methods:'GET')]
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('default/index.html.twig');
    }
}

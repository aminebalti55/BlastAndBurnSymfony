<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;


class BotmanController extends AbstractController{

    
    
    /**
     * @Route("/botman", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('homepage.html.twig');
    }
    
    /**
     * @Route("/chatframe", name="chatframe")
     */
    public function chatframeAction(Request $request)
    {
        return $this->render('chat_frame.html.twig');
    }
}
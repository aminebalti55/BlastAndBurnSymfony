<?php

namespace App\Controller;


use App\Entity\Event;
use App\Entity\EventReport;
use App\Entity\Recipe;
use App\Entity\RecipeReport;
use App\Entity\Report;
use App\Entity\ReportNotification;

use App\Entity\session;
use App\Entity\sessionReport;
use App\Form\ReportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

class ReportController extends AbstractController
{
    /**
     * @Route("/report/{reportedId}", name="report", methods={"GET","POST"})
     */
    public function new(Request $request, $reportedId,\Swift_Mailer $mailer): Response
    {
        $type=$request->query->get('type');
        $report=new Report();
        $report->setType($type);
        $report->setCreatedAt(new \DateTime());

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $report->setReporter($user);

        $notification=new ReportNotification();
        $notification->setUser($user);

        switch ($type){
           
            case 'event':$reported = $this->getDoctrine()->getRepository(Event::class)->find($reportedId);
                         $itemReport=new EventReport();
                         $itemReport->setEvent($reported);
                         $notification->setEvent($reported);
                         break;
            case 'session':$reported = $this->getDoctrine()->getRepository(session::class)->find($reportedId);
                        $itemReport=new sessionReport();
                        $itemReport->setsession($reported);
                        $notification->setsession($reported);
                        break;
          
            case 'recipe':$reported = $this->getDoctrine()->getRepository(Recipe::class)->find($reportedId);
                            $itemReport=new RecipeReport();
                            $itemReport->setRecipe($reported);
                            $notification->setRecipe($reported);
                            break;
        }


        $report->setTitle($reported->getTitle());
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message = (new \Swift_Message('Participated Succesufully '))

        ->setFrom('mohamedamine.balti@esprit.tn')
        ->setTo('aminebalti55@gmail.com')
        ->setBody(
            $this->renderView(

                'FrontOffice/Mail.html.twig',
                ['report' => $report]
            ),
            'text/html'

        );

    $mailer->send($message);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($report);
            $entityManager->flush();
            $itemReport->setReport($report);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($itemReport);
            $entityManager->flush();
            $notification->setReport($report);
            $notification->setCreatedAt(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($notification);
            $entityManager->flush();

            return $this->redirectToRoute('all');
        }

        return $this->render('FrontOffice/details.html.twig', [
            'report' => $report,
            'form' => $form->createView(),
        ]);
    }



     /**
     * @Route("/message", name="message")
     */
    function messageAction(Request $request)
    {
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        // Configuration for the BotMan WebDriver
        $config = [];

        // Create BotMan instance
        $botman = BotManFactory::create($config);

        // Give the bot some things to listen for.
        $botman->hears('(hello|hi|hey)', function (BotMan $bot) {
            
            $bot->reply('Hello!');
            
        });

          // Give the bot some things to listen for.
          $botman->hears('(how are you doing|how are you)', function (BotMan $bot) {
            $bot->reply(' i am fine thank you how can i help you with!');
            
        });

     // Give the bot some things to listen for.
     $botman->hears('(i would like to report something)', function (BotMan $bot) {
        $bot->reply(' what do you want to report ');
        
    });

        // Give the bot some things to listen for.
        $botman->hears('(i would like to report an event |i would like to report a session|i would like to report a recipe )', function (BotMan $bot) {
            $bot->reply(' oh too bad you did not enjoy ur time on our website if you want to report something go to the latest section and click on the thing you want to report  ');
            
        });

 // Give the bot some things to listen for.
 $botman->hears('(thank you )', function (BotMan $bot) {
    $bot->reply(' am always here for you if you need me no need to thank me  ');
    
});
 // Give the bot some things to listen for.
 $botman->hears('(i just wanna blast and burn  )', function (BotMan $bot) {
    $bot->reply(' you came to the right place  ');
    
});

        // Set a fallback
        $botman->fallback(function (BotMan $bot) {
            $bot->reply('Sorry, I did not understand.');
        });

        // Start listening
        $botman->listen();

        return new Response();
    }




    
}

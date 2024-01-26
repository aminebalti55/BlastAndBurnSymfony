<?php

namespace App\Controller;

use App\Entity\Coachs;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Event;
use App\Entity\EventRate;
use App\Entity\EventReport;
use App\Entity\Favorite;
use App\Entity\Rate;
use App\Entity\Recipe;
use App\Entity\RecipeRate;
use App\Entity\RecipeReport;
use App\Entity\Report;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use App\Entity\ReportNotification;

use App\Entity\session;
use App\Entity\sessionRate;
use App\Entity\sessionReport;
use App\Form\RateType;
use App\Form\ReportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class HomePageController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(): Response
    {   if($this->isGranted('ROLE_ADMIN')){
        return $this->redirect($this->generateUrl('admin'));
        throw new \Exception(AccessDeniedException::class);
}
  
        $newsessions=$this->getDoctrine()->getRepository(session::class)->findBy([],['createdAt'=>'desc'],'3');
        $newEvents=$this->getDoctrine()->getRepository(Event::class)->findBy([],['createdAt'=>'desc'],'3');
        $newRecipes=$this->getDoctrine()->getRepository(Recipe::class)->findBy([],['createdAt'=>'desc'],'3');

        return $this->render('FrontOffice/index.html.twig',['newsessions'=>$newsessions,'newEvents'=>$newEvents,'newRecipes'=>$newRecipes ]);
    }



    
    /**
     * @Route("/all", name="all")
     */
    public function all()
    {
        $events=$this->getDoctrine()->getRepository(Event::class)->findAll();
        $recipe=$this->getDoctrine()->getRepository(Recipe::class)->findAll();
        $session=$this->getDoctrine()->getRepository(session::class)->findAll();
        $all=array_merge($events,$recipe,$session);
        return $this->render('FrontOffice/all.html.twig', ['allItems'=>$all]);
    }


    /**
     * @Route("/details/{id}", name="details")
     */
    public function details(Request $request, $id){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $type=$request->query->get('type');
        $report=new Report();
        $report->setType($type);
        $report->setCreatedAt(new \DateTime());

        $report->setReporter($user);

        $notification=new ReportNotification();
        $notification->setUser($user);

        switch ($type){
            
            case 'event':$reported = $this->getDoctrine()->getRepository(Event::class)->find($id);
                $itemReport=new EventReport();
                $itemReport->setEvent($reported);
                $notification->setEvent($reported);
                break;

            case 'session':$reported = $this->getDoctrine()->getRepository(session::class)->find($id);
                $itemReport=new sessionReport();
                $itemReport->setsession($reported);
               // $notification->setsession($reported);
                break;
           
            case 'recipe':$reported = $this->getDoctrine()->getRepository(Recipe::class)->find($id);
                $itemReport=new RecipeReport();
                $itemReport->setRecipe($reported);
                $notification->setRecipe($reported);
                break;
        }


        $report->setTitle($reported->getTitle());

            if ($request->isMethod('POST') && $request->query->get('t')=='report') {
                $note=$request->get('reportText');
                $report->setNote($note);
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
            }
        $rate=new Rate();
        $rate->setType($type);
        $rate->setCreatedAt(new \DateTime());
        $rate->setUser($user);

        switch ($type){
         
            case 'event':$rated = $this->getDoctrine()->getRepository(Event::class)->find($id);
                $itemRate=new EventRate();
                $itemRate->setEvent($rated);
                break;
            case 'session':$rated = $this->getDoctrine()->getRepository(session::class)->find($id);
                $itemRate=new sessionRate();
                $itemRate->setsession($rated);
                break;
           
            case 'recipe':$rated = $this->getDoctrine()->getRepository(Recipe::class)->find($id);
                $itemRate=new RecipeRate();
                $itemRate->setRecipe($rated);
                break;
        }

        if ($request->isMethod('POST') && $request->query->get('t')=='rate') {
            $rate->setScore($request->get('rate'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rate);
            $entityManager->flush();
            $itemRate->setRate($rate);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($itemRate);
            $entityManager->flush();
        }
        switch ($type){
           
            case 'event':$item=$this->getDoctrine()->getRepository(Event::class)->find($id);
                break;
            case 'session':$item=$this->getDoctrine()->getRepository(session::class)->find($id);
                break;
          
            case 'recipe':$item=$this->getDoctrine()->getRepository(Recipe::class)->find($id);
                break;
        }
        $favorite=$this->getDoctrine()->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            $type => $id,
        ]);
        return $this->render('FrontOffice/details.html.twig',['item'=>$item,'fav'=>$favorite]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(){
        return $this->render('FrontOffice/about.html.twig');
    }

     /**
     * @Route("/becomeacoach", name="becomeacoach")
     */
    public function coach(Request $request,\Swift_Mailer $mailer): Response
    {
        $coachs=new Coachs();
        $form=$this->createFormBuilder($coachs)
        ->add('Name', TextType::class)
        ->add('imgurl', FileType::class, ['required'=>true])
        ->add('add', SubmitType::class, ['label'=>'Send Request'])
        ->getForm();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em=$this->getDoctrine()->getManager();
            $file = $form->get('ImgUrl')->getData();
            $filename = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move(
                    $this->getParameter( 'images_directory_recipe'),
                    $filename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $fs=new Filesystem();
            $fs->mirror($this->getParameter('images_directory_recipe'), '../../CoHeal-Desktop/src/coheal/resources/images/recipes');
            $coachs->setImgUrl("$filename");
            $em->persist($coachs);
            $em->flush();

            $message = (new \Swift_Message('Request Sent '))

            ->setFrom('ccandyxx1@gmail.com')
            ->setTo($this->getUser()->getEmail())
            
            ->setBody(
                $this->renderView(
    
                    'FrontOffice/Mailll.html.twig',
                    ['coachs' => $coachs]
                ),
                'text/html'
    
            );

    
        $mailer->send($message);

        
            return $this->redirectToRoute("home");
            
    
        }
        return $this->render('event/coach.html.twig', ['form'=>$form->createView()]) ;
        
        }

    


    
 
}

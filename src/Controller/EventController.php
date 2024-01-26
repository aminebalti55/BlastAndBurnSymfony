<?php

namespace App\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\User;
use App\Repository\EventCategoryRepository;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\EntityRepository;
use App\Repository\EventRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\DataTransformer\StringToFloatTransformer;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\UserEvent;

class EventController extends AbstractController
{


    public function downloadpdf(){
        $events=$this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('t')->where('t.isDeleted=0')->orderBy('t.createdAt','desc')->getQuery()->getResult();

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('Default/pdftemplate.html.twig', [

            'events' => $events
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("pmypdf.pdf", [
            "Attachment" => false
        ]);

        exit(0);
    }


    /**
     * @Route("/Event", name="event")
     */
    public function index(): Response
    {

        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }
    /**
     * @Route("/addEvent", name="add_event")
     * @param \Swift_Mailer $mailer
     * @return Response
     */

    public function addevent(Request $request,\Swift_Mailer $mailer)
    {
        $event=new Event();
        $form=$this->createFormBuilder($event)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, )
            ->add('type',ChoiceType::class, array(
                'choices' => array(
                    'Free ' => 'free',
                    'Paid ' => 'paid',
                ),
                'expanded' => true))


           ->add('Location',TextareaType::class, array(
                    'attr' => array(
                        'readonly' => true,
                    ),
                ))
            ->add('price', IntegerType::class, )
            ->add('startDate',DateType::class, ['widget' => 'single_text'])
            ->add('endDate',DateType::class, ['widget' => 'single_text'])
            ->add('Cat', EntityType::class, ['class'=>EventCategory::class,
                'query_builder' => function (EntityRepository   $er) {
                    return $er->createQueryBuilder('tc')
                        ->where('tc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,
                'attr' => ['class' => 'form-control']])
            ->add('ImgUrl', FileType::class, ['required'=>true])
            ->add('add', SubmitType::class, ['label'=>'Add new Event'])
            ->getForm();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $event->setUser($user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em=$this->getDoctrine()->getManager();
            $image = $form->get('ImgUrl')->getData();
            $uploads_directory = $this->getParameter('images_directory_event');
            $file = $event->getImgUrl();
            $filename = md5(uniqid()) . '.' .$file->guessExtension();
            $image->move(
                $uploads_directory,
                $filename

            );
            $fs=new Filesystem();
            $fs->mirror($this->getParameter('images_directory_event'), '../../CoHeal-Desktop/src/coheal/resources/images/events');
            $event->setImgUrl("$filename");
            $event->setCreatedAt(new \DateTime());
            $event->setModifiedAt(new \DateTime());
            $em->persist($event);
            $em->flush();
            $users= $this->getDoctrine()->getrepository(User::class)->findAll();

            foreach($users as $u)
            {
                $message = (new \Swift_Message('Blast And Burn-New event'))

                    ->setFrom('ccandyxx1@gmail.com')
                    ->setTo($u->getEmail())
                    ->setBody(
                        $this->renderView(

                            'FrontOffice/Mail.html.twig',
                            ['event' => $event]
                        ),
                        'text/html'

                    );

                $mailer->send($message);

            }
            return $this->redirectToRoute('all_event');
        }

        return $this->render('/event/add_event.html.twig', ['form'=>$form->createView()]);
    }


    /**
     * @Route("/allEvents", name="all_event")
     */
    public function all()
    {
        $events=$this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('t')->where('t.isDeleted=0')->orderBy('t.createdAt','desc')->getQuery()->getResult();
        return $this->render('/event/event_list.html.twig', ['events'=>$events]);
    }



    /**
     * @Route("/eventDtails/{idt}", name="event_details")
     */
    public function details(int $idt)
    {
        //$u = $this->getUser()->getUsername();
        //$u=$this->getUser();

        // $c = $this->getDoctrine()->getRepository(UserEvent::class)->finde($idt);
      //  $event = $this->getDoctrine()->getRepository(Event::class)->findOneBy(['eventId' => $idt]);
        // $user = $this ->getDoctrine()->getRepository()
        //////////////////////////
        //$repository = $this->getDoctrine()->getRepository(UserEvent::class);


        // $eventss = $repository->finde($idt);
        ///////////////////////
        //$bb =new User();
        // $bb = $this->getDoctrine()->getRepository(User::class)->find($u);
        // $bb = $this->getDoctrine()->getRepository(User::class)->findByEmail;
        $user = new User();

        // $b = $this->getDoctrine()->getRepository(UserEvent::class)->find(array('Event'=>$idt,'user'=>$u));
        $u=$this->getUser();

        $events = $this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('t')->where('t.eventId=?1')->setParameter(1, $idt)->getQuery()->getSingleResult();

        $event=$this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('t')
            ->where('t.eventId=?1')->setParameter(1,$idt)->getQuery()->getSingleResult();

        $exist="";
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getDoctrine()->getRepository(User::class)->find($u);
        if ($user->getEvent()->contains($events)) {
            return $this->render('event/event_detailsss.html.twig' , ['event' => $events]);

            //$exist = "exist";
        }
        //return $this->render('task/task_details.html.twig',
           // ['task' => $task, 'lastTasks' => $lastTasks, 'categories' => $categories, 'paid' => $paid, 'user' => $u, 'part' => $exist]);


        // if (!$b)
    //     if($exist="") {
        //   return $this->render('event/event_details.html.twig' , ['event' => $events ]);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // $c = $this->getDoctrine()->getRepository(UserEvent::class)->findOneBy(hey);
else
    return $this->render('event/event_details.html.twig' , ['event' => $events ]);

    }

    /**
     * @Route("/updateEvent/{id}", name="update_event")
     */
    public function update(Request $request,int $id): Response
    {
        $event=$this->getDoctrine()->getRepository(Event::class)->find($id);
        $form=$this->createFormBuilder($event)
            ->add('title', TextType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->add('location', TextType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->add('type',ChoiceType::class, array(
                'choices' => array(
                    'Free ' => 'free',
                    'Paid ' => 'paid',
                ),
                'expanded' => true))
            ->add('price', TextType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->add('startDate',DateType::class, ['widget' => 'single_text'])
            ->add('endDate',DateType::class, ['widget' => 'single_text'])
            ->add('description', TextareaType::class, ['required'=>true, 'attr'=>['placeholder'=>'Write description']])
            ->add('Cat', EntityType::class, ['class'=>EventCategory::class,
                'query_builder' => function (EntityRepository   $er) {
                    return $er->createQueryBuilder('tc')
                        ->where('tc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,
                'attr' => ['class' => 'form-control']])

            ->add('update', SubmitType::class, ['label'=>'Save', 'attr'=>['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('event_details', array('idt' => $event->geteventId()));
        }
        return $this->render('/event/update_Event.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/deleteEvent/{id}", name="delete_event")
     */
    public function delete(int $id)
    {
        $events=$this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('t')->update(Event::class,'t')->set('t.isDeleted','?1')->where('t.eventId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return $this->redirectToRoute("all_event");
    }

    /**
     * @Route("/deleteAdminEvent/{id}", name="delete_admin_event")
     */
    public function adminDelete(int $id)
    {
        $events=$this->getDoctrine()->getRepository(Event::class)->createQueryBuilder('t')->update(Event::class,'t')->set('t.isDeleted','?1')->where('t.eventId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return $this->redirectToRoute("events");
    }

    /**
     * @param EventRepository $repository
     * @param Request $request
     * @return Response
     * @Route("/rechercherEvent",name="rechercherEvent")
     */

    function RechercheEvent(EventRepository $repository , Request $request)
    {
        $title=$request->get('recherche');
        $events=$repository->findByTitle($title);
        return $this->render('/event/event_list.html.twig', ['events'=>$events]);
    }
    /**
     *
     * @Route("/SearchEvent ", name="SearchEvent")
     */
    public function searchEvent(        EventRepository $repository   ,Request $request,NormalizerInterface $Normalizer)
    {

      //  $repository = $this->getDoctrine()->getRepository(Event::class);
        $requestString=$request->get('searchValueBack');

        $events = $repository->findByTitle($requestString);


        $jsonContent = $Normalizer->normalize($events, 'json',['groups'=>'BS']);
        $retour=json_encode($jsonContent);
        return new Response($retour);

    }



    /**
     * @Route("/participate_event/{id}", name="participate_event")
     * @param \Swift_Mailer $mailer
     */
    public function participate(int $id,\Swift_Mailer $mailer)
    {
        $event = new Event();
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);
        //  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // $userId = $this->security->getUser()->getId();
        //   if(!empty($user)){
        //      $userId= $user->getId();
        //  }

        $u = $this->getUser();
        $userevent = new UserEvent();
        //$idd = $this->getUser()->getId();
        //var_dump(  $idd);
        // var_dump($user->getId())
        $userevent->setUser($u);
        $userevent->setEvent($event);
        $e=$this->getUser()->getUsername();
       
        $em = $this->getDoctrine()->getManager();
        $em->persist($userevent);
        $em->flush();
        $em->persist($u);
        $em->flush();
        $message = (new \Swift_Message('Participated Succesufully '))

        ->setFrom('ccandyxx1@gmail.com')
        ->setTo($this->getUser()->getEmail())
        ->setBody(
            $this->renderView(

                'FrontOffice/Maill.html.twig',
                ['event' => $event]
            ),
            'text/html'

        );

    $mailer->send($message);
        return $this->redirectToRoute("all_event");
        

    }



    /**
     * @Route("/map", name="event_map", methods={"GET"})
     */
    public function indexing(): Response
    {
        return $this->render('event/map.html.twig');
    }

    


}

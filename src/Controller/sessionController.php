<?php

namespace App\Controller;
use App\Entity\Paidsession;
use App\Entity\session;
use App\Entity\sessionCategory;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class sessionController extends AbstractController
{
    /**
     * @Route("/session", name="session")
     */
    public function index(): Response
    {
        return $this->render('session/index.html.twig', [
            'controller_name' => 'sessionController',
        ]);
    }
    /**
     * @Route("/addsession", name="add_session")
     */
    public function addsession(Request $request)
    {
        $session=new session();
        $form=$this->createFormBuilder($session)
            ->add('Title', TextType::class)
            ->add('Description', TextareaType::class, )
            ->add('Cat', EntityType::class, ['class'=>sessionCategory::class,
                'query_builder' => function (EntityRepository   $er) {
                    return $er->createQueryBuilder('tc')
                        ->where('tc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,
                'attr' => ['class' => 'form-control']])
            ->add('ImgUrl', FileType::class, ['required'=>true])
            ->add('type',ChoiceType::class, array(
                'choices' => array(
                    'Free session' => 'free',
                    'Paid session' => 'paid',
                ),
                'expanded' => true))
            ->add('price', IntegerType::class)
            ->add('add', SubmitType::class, ['label'=>'Add new session'])
            ->getForm();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();

        $session->setU($u);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $file = $session->getImgUrl();
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory_session'),
                    $filename
                );
            } catch (FileException $e) {
            }
            $fs=new Filesystem();
            $fs->mirror($this->getParameter('images_directory_session'), '../../CoHeal-Desktop/src/coheal/resources/images/sessions');

            $em=$this->getDoctrine()->getManager();
            $session->setImgUrl($filename);
            $session->setCreatedAt(new \DateTime());
            $session->setModifiedAt(new \DateTime());
            $em->persist($session);
            $em->flush();
//////////////
            if($session->getType()=='paid'){
                $p=new Paidsession();
                $t=$this->getDoctrine()->getRepository(session::class)->findOneBy(['title'=> $session->getTitle()]);
                $p->setsession($t);
                $p->setPrice($session->getPrice());
                $em->persist($p);
                $em->flush();
            }
            return $this->redirectToRoute('session_category_list');
        }

        return $this->render('/session/add_session.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/sessionDtails/{idt}", name="session_details")
     */
    public function details(int $idt)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')
            ->where('t.sessionId=?1')->setParameter(1,$idt)->getQuery()->getSingleResult();
        $paid=new Paidsession();
        if($session->getType()=="paid"){
            $p=$this->getDoctrine()->getRepository(Paidsession::class)->find($session->getId());
            $paid=$p;
        }
        $lastsessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t')
            ->join('t.cat','c')->where('t.isDeleted=0')->andWhere('c.isDeleted=0')
            ->orderBy('t.createdAt','desc')->setFirstResult(0)->setMaxResults(3)
            ->getQuery()->getResult();
        $categories = $this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t ')
            ->addSelect('tc.catId, tc.name, tc.imgUrl,count(t) as totalsessions')->join('t.cat','tc')
            ->where('t.cat=tc.catId')
            ->andWhere('tc.isDeleted=0')->andWhere('t.isDeleted=0')->groupBy('tc.catId ')->orderBy('tc.createdAt', 'desc')
            ->setFirstResult(0)->setMaxResults(3)->getQuery()->getResult();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();
        $exist="";

        $user=$this->getDoctrine()->getRepository(User::class)->find($u);
        if($user->getsession()->contains($session)){;
            $exist="exist";
        }
        return $this->render('session/session_details.html.twig',
            ['session'=>$session,'lastsessions'=>$lastsessions,'categories'=>$categories,'paid'=>$paid,'user'=>$u,'part'=>$exist]);
    }

    /**
     * @Route("/updatesession/{id}", name="update_session")
     */
    public function update(Request $request,int $id): Response
    {
        $session=$this->getDoctrine()->getRepository(session::class)->find($id);
        $form=$this->createFormBuilder($session)
            ->add('Title', TextType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->add('Description', TextareaType::class, ['required'=>true, 'attr'=>['placeholder'=>'Write Description']])
            ->add('Cat', EntityType::class, ['class'=>sessionCategory::class,
                'query_builder' => function (EntityRepository   $er) {
                    return $er->createQueryBuilder('tc')
                        ->where('tc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,
                'attr' => ['class' => 'form-control']])
            ->add('update', SubmitType::class, ['label'=>'Save', 'attr'=>['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($session);
            $em->flush();
            return $this->redirectToRoute('session_details', array('idt' => $session->getId()));
        }
        return $this->render('/session/update_session.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/deletesession/{id}", name="delete_session")
     */
    public function delete(int $id)
    {
        $sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')->update(session::class,'t')->set('t.isDeleted','?1')->where('t.sessionId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return $this->redirectToRoute("all_session");
    }

    /**
     * @Route("/deleteAdminsession/{id}", name="delete_admin_session")
     */
    public function admiDelete(int $id)
    {
        $sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')
            ->update(session::class,'t')->set('t.isDeleted','?1')->where('t.sessionId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return $this->redirectToRoute("sessions");
    }

    /**
     * @Route("/allsessions", name="all_session")
     */
    public function all()
    {
        // $sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')->where('t.isDeleted=0')->orderBy('t.createdAt','desc')->getQuery()->getResult();
        $paid=$this->getDoctrine()->getRepository(Paidsession::class)->findAll();
        $sessions = $this->getDoctrine()->getRepository(session::class)->findBy(['isDeleted'=>0]);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();
        $yours=$u->getsession();
        return $this->render('/session/all_sessions.html.twig', ['sessions'=>$sessions,'paid'=>$paid,'yours'=>$yours]);
    }

    /**
     * @Route("/participatesession/{id}", name="participate_session")
     */
    public function participate(int $id)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->find( $id);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();

        $u->addsession($session);
        $session->addUser($u);
        $em = $this->getDoctrine()->getManager();
        $em->persist($session);
        $em->flush();
        $em->persist($u);
        $em->flush();
        $lastsessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t')
            ->join('t.cat','c')->where('t.isDeleted=0')->andWhere('c.isDeleted=0')
            ->orderBy('t.createdAt','desc')->setFirstResult(0)->setMaxResults(3)
            ->getQuery()->getResult();
        $categories = $this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t ')
            ->addSelect('tc.catId, tc.name, tc.imgUrl,count(t) as totalsessions')->join('t.cat','tc')
            ->where('t.cat=tc.catId')
            ->andWhere('tc.isDeleted=0')->andWhere('t.isDeleted=0')->groupBy('tc.catId ')->orderBy('tc.createdAt', 'desc')
            ->setFirstResult(0)->setMaxResults(3)->getQuery()->getResult();
        return $this->render('session/session_details.html.twig', ['session'=>$session,'lastsessions'=>$lastsessions,'categories'=>$categories]);

    }
    /**
     * @Route("/listParticipatesession/{id}", name="success")
     */
    public function success(int $id): Response
    {
        $session=$this->getDoctrine()->getRepository(session::class)->find( $id);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();

        $u->addsession($session);
        $session->addUser($u);
        $em = $this->getDoctrine()->getManager();
        $em->persist($session);
        $em->flush();
        $em->persist($u);
        $em->flush();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();
        $user=$this->getDoctrine()->getRepository(User::class)->find($u);

        // $sessions=$this->getDoctrine()->getRepository(User::class)->find($u->getU)
        return $this->render('session/participate_user_session.html.twig', [
            'sessions' => $user->getsession(),
        ]);
    }
    /**
     * @Route("/yours", name="yours")
     */
    public function yours(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();
        $user=$this->getDoctrine()->getRepository(User::class)->find($u);
        if( in_array("ROLE_coach", $u->getRoles())){
            $sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t')
                ->where('t.u=?1')->setParameter('1',$u)->andWhere('t.isDeleted=0')->getQuery()->getResult();

        }
        else{
            $sessions=$user->getsession();

        }


        // $sessions=$this->getDoctrine()->getRepository(User::class)->find($u->getU)
        return $this->render('session/participate_user_session.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * @Route("/error", name="error")
     */
    public function error(): Response
    {
        return $this->render('session/error.html.twig', [
            'controller_name' => 'sessionController',
        ]);
    }
    /**
     * @Route("/create-checkout-session/{price}/{title}/{id}", name="checkout")
     */
    public function checkout(int $price,string $title,int $id): Response
    {
        \Stripe\Stripe::setApiKey('sk_test_51IjkvCBmeiBzIRGDyyarfCt1PkQ84C1qClt5fW2OoDI1C9kj5P0Cqfmxo9qhcVvomVSvZzPnu8gNURcNGwK3RO3M00YdjlLqWL');
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $title,
                    ],
                    'unit_amount' => $price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('success',['id'=>$id],UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('error',[],UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        return new JsonResponse([ 'id' => $session->id ]);
    }

}

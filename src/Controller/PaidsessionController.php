<?php

namespace App\Controller;

use App\Entity\Paidsession;
use App\Entity\session;
use App\Entity\sessionCategory;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaidsessionController extends AbstractController
{
    /**
     * @Route("/paid/session", name="paid_session")
     */
    public function index(): Response
    {
        return $this->render('paid_session/index.html.twig', [
            'controller_name' => 'PaidsessionController',
        ]);
    }
    /**
     * @Route("/paid/addpaidsession", name="add_paid_session")
     */
    public function addPaidsession(Request $request)
    {
        $session=new Paidsession();
        $form=$this->createFormBuilder($session)
            ->add('Title', TextType::class)
            ->add('Description', TextareaType::class, )
            ->add('Cat', EntityType::class, ['class'=>sessionCategory::class,
                'query_builder' => function (EntityRepository   $er) {
                    return $er->createQueryBuilder('tc')
                        ->where('tc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,
                'attr' => ['class' => 'form-control']])
            ->add('numOfDays', IntegerType::class)
            ->add('ImgUrl', FileType::class, ['required'=>true])
            ->add('add', SubmitType::class, ['label'=>'Add new session'])
            ->getForm();
        $user=$this->getDoctrine()->getRepository(User::class)->find(6);

        $session->setU($user);
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
                // ... handle exception if something happens during file upload
            }
            $em=$this->getDoctrine()->getManager();
            $session->setImgUrl($filename);
            $session->setCreatedAt(new \DateTime());
            $session->setModifiedAt(new \DateTime());
            $em->persist($session);
            $em->flush();
            return $this->redirectToRoute('session_category_list');
        }

        return $this->render('/session/add_session.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/sessionDtails/{idt}", name="session_details")
     */
    public function details(int $idt)
    {
        $sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')
            ->where('t.sessionId=?1')->setParameter(1,$idt)->getQuery()->getResult();
        return $this->render('session/session_details.html.twig', ['sessions'=>$sessions]);
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
            ->add('numOfDays', IntegerType::class, ['required'=>true])
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
     * @Route("/allsessions", name="all_session")
     */
    public function all()
    {
        $sessions=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')->where('t.isDeleted=0')->orderBy('t.createdAt','desc')->getQuery()->getResult();
        return $this->render('/session/all_sessions.html.twig', ['sessions'=>$sessions]);
    }
}

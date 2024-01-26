<?php

namespace App\Controller;

use App\Entity\session;
use App\Entity\sessionActions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class sessionActionsController extends AbstractController
{
    /**
     * @Route("/session/actions", name="session_actions")
     */
    public function index(): Response
    {
        return $this->render('session_actions/index.html.twig', [
            'controller_name' => 'sessionActionsController',
        ]);
    }

    /**
     * @Route("/addsessionAction/{id}", name="add_session_action")
     */
    public function add(Request $request,int $id)
    {
        $action=new sessionActions();
        $form=$this->createFormBuilder($action)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, )
            ->add('add', SubmitType::class, ['label'=>'Add new session action'])
            ->getForm();
        $session=$this->getDoctrine()->getRepository(session::class)->find($id);

        $action->setsession($session);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $em=$this->getDoctrine()->getManager();
            $em->persist($action);
            $em->flush();
            return $this->redirectToRoute('session_actions_by_session', array('id' => $id));
        }

        return $this->render('session_actions/add_session_action.html.twig', ['form'=>$form->createView()]);
    }



    /**
     * @Route("/updatesessionAction/{id}", name="update_session_action")
     */
    public function update(Request $request,int $id): Response
    {
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->find($id);
        $form=$this->createFormBuilder($action)
            ->add('title', TextType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->add('description', TextareaType::class, ['required'=>true, 'attr'=>['placeholder'=>'Write Description']])
            ->add('update', SubmitType::class, ['label'=>'Save', 'attr'=>['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($action);
            $em->flush();
            return $this->redirectToRoute('session_details', array('idt' => $action->getsession()->getId()));
        }
        return $this->render('session_actions/update_session_action.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/updatesessionActionDone/{id}", name="update_session_action_done")
     */
    public function updateDone(Request $request,int $id): Response
    {
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->find($id);
        $action->setDone(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($action);
        $em->flush();
        // return $this->redirect($request->getUri());
        /*  $action=$this->getDoctrine()->getRepository(sessionActions::class)->find($id);
          $form=$this->createFormBuilder($action)
              ->add('done', CheckboxType::class, ['required'=>true, 'attr'=>['placeholder'=>'Write Description']])
             ->getForm();

          $form->handleRequest($request);
          if($form->isSubmitted() && $form->isValid()) {
              $em = $this->getDoctrine()->getManager();
              $em->persist($action);
              $em->flush();
              return $this->redirectToRoute('session_actions_by_session', array('id' => $action->getsession()->getsessionId()));
          }
          return $this->render('session_actions/list_session_actions.html.twig', ['form'=>$form->createView()]);*/
        return $this->redirectToRoute('session_actions_by_session', array('id' => $action->getsession()->getId()));
    }
    /**
     * @Route("/deletesessionAction/{id}", name="delete_session_action")
     */
    public function delete(int $id)
    {
        $a=$this->getDoctrine()->getRepository(sessionActions::class)->find($id);
        $idt=$a->getsession()->getId();
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->createQueryBuilder('t')->delete(sessionActions::class,'ta')->where('ta.actionId = ?1')->setParameter(1, $id)->getQuery()->execute();
        return $this->redirectToRoute("session_actions_by_session", array('id' =>$idt));
    }

    /**
     * @Route("/sessionActionsBysession/{id}", name="session_actions_by_session")
     */
    public function sessionActionsByCategory(int $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();
        $actions = $this->getDoctrine()->getRepository(sessionActions::class)->createQueryBuilder('ta')->where('ta.session=?1')->setParameter(1, $id)->getQuery()->getResult();
        $session=$this->getDoctrine()->getRepository(session::class)->find($id);
        return $this->render('session_actions/list_session_actions.html.twig', ['actions' => $actions,'id'=>$id,'user'=>$u,'session'=>$session]);
    }

    /**
     * @Route("/donesessionAction/{id}", name="done_session_action")
     */
    public function done(Request $request,int $id): Response
    {
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->find($id);
        $form=$this->createFormBuilder($action)
            ->add('done', CheckboxType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($action);
            $em->flush();
            return $this->redirectToRoute('session_actions_by_session', array('idt' => $action->getsession()->getId()));
        }
        return $this->redirectToRoute('session_actions_by_session', array('idt' => $action->getsession()->getId()));
    }
}

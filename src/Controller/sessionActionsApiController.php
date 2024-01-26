<?php

namespace App\Controller;

use App\Entity\session;
use App\Entity\sessionActions;
use App\Entity\sessionCategory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class sessionActionsApiController extends AbstractController
{
    /**
     * @Route("/api/sessionActions/{id}", name="session_actions_api")
     */
    public function sessionActions(int $id,NormalizerInterface $normalizer)
    {

        $actions = $this->getDoctrine()->getRepository(sessionActions::class)->createQueryBuilder('ta')->where('ta.session=?1')->setParameter(1, $id)->getQuery()->getResult();
        $jsonContent=$normalizer->normalize($actions, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/api/addsessionActions/{id}", name="session_actions_add_api")
     */
    public function addActions(Request $request,SerializerInterface $serializer,$id)
    {
        $content=$request->getContent();
        $data=$serializer->deserialize($content,sessionActions::class,'json');
        $parameters = json_decode($content, true);

        $session=$this->getDoctrine()->getRepository(session::class)->find($id);
        $data->setsession($session);
        $em=$this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();

        return new Response("session action added successfully");
    }
    /**
     * @Route("/api/updatesessionActions/{id}", name="session_actions_update_api")
     */
    public function updateActions(Request $request,$id)
    {
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->find($id);
        $content=$request->getContent();
        $parameters = json_decode($content, true);
        $action->setTitle($parameters['title']);
        $action->setDescription($parameters['description']);
        $em=$this->getDoctrine()->getManager();
        $em->persist($action);
        $em->flush();

        return new Response("session action updated successfully");
    }

    /**
     * @Route("/api/deletesessionAction/{id}", name="session_actions_delete_api")
     */
    public function delete(Request $request,NormalizerInterface $normalizer,int $id)
    {
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->createQueryBuilder('t')->delete(sessionActions::class,'ta')->where('ta.actionId = ?1')->setParameter(1, $id)->getQuery()->execute();
        return new Response("session action deleted successfully");
    }


    /**
     * @Route("/api/getsessionActions/{id}", name="session_action_get_api")
     */
    public function ac(NormalizerInterface $normalizer,string $id)
    {
        $action=$this->getDoctrine()->getRepository(sessionActions::class)->findBy(['actionId'=>$id]);
        $jsonContent=$normalizer->normalize($action, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
}

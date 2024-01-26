<?php

namespace App\Controller;

use App\Entity\Paidsession;
use App\Entity\session;
use App\Entity\SessionCategory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PaidsessionApiController extends AbstractController
{
    /**
     * @Route("/api/paidsessions", name="paid_session_api")
     */
    public function paidDetails(Request $request,NormalizerInterface $normalizer)
    {

        $paid=$this->getDoctrine()->getRepository(Paidsession::class)->findAll();
        $jsonContent=$normalizer->normalize($paid, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/api/addPaidsession", name="paid_session_add_api")
     */
    public function add(Request $request,SerializerInterface $serializer)
    {
        /*$em=$this->getDoctrine()->getManager();
        $session=new session();

        $session->setTitle($request->get('title'));
        $session->setCat($request->get('cat'));
        $session->setDescription($request->get('description'));
        $session->setNumOfDays($request->get('numOfDays'));
        $session->setU($request->get('user'));
        $session->setImgUrl($request->get('imgUrl'));
        $session->setCreatedAt(new \DateTime());
        $session->setModifiedAt(new \DateTime());
        $em->persist($session);
        $em->flush();

        $paid=new Paidsession();
        $t=$this->getDoctrine()->getRepository(session::class)->findOneBy(['title'=> $session->getTitle()]);
        $paid->setsession($t);
        $paid->setPrice($request->get('price'));

        $em->persist($paid);
        $em->flush();
        $jsonContent=$normalizer->normalize($paid, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));*/
        $content=$request->getContent();
        $data=$serializer->deserialize($content,Paidsession::class,'json');
        $parameters = json_decode($content, true);

        $session=$this->getDoctrine()->getRepository(session::class)->find($parameters['session']);
        $data->setsession($session);
        $data->setPrice($parameters['price']);
        $em=$this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();

        return new Response("Paid session added successfully");
    }

    /**
     * @Route("/api/updatePaidsession/{id}", name="paid_session_update_api")
     */
    public function update(Request $request,NormalizerInterface $normalizer,int $id)
    {
        /*$em=$this->getDoctrine()->getManager();
        $paid=$this->getDoctrine()->getRepository(Paidsession::class)->find($id);

        $paid->setsession()->setTitle($request->get('title'));
        $paid->setsession()->setCat($request->get('cat'));
        $paid->setsession()->setDescription($request->get('description'));
        $paid->setsession()->setNumOfDays($request->get('numOfDays'));
        $paid->setsession()->setU($request->get('user'));
        $paid->setsession()->setImgUrl($request->get('imgUrl'));
        $paid->setsession()->setModifiedAt(new \DateTime());
        $paid->setPrice($request->get('price'));
        $em->flush();
        $jsonContent=$normalizer->normalize($paid, 'json',['groups'=>'post:read']);
        return new Response("updated".json_encode($jsonContent));*/
        $paid=$this->getDoctrine()->getRepository(Paidsession::class)->find($id);
        $content=$request->getContent();
        $parameters = json_decode($content, true);

        /* $user=$this->getDoctrine()->getRepository(User::class)->find($parameters['u']);
         $cat=$this->getDoctrine()->getRepository(sessionCategory::class)->find($parameters['cat']);
         $paid->setsession()->setU($user);
         $paid->setsession()->setCat($cat);*/
        $paid->setsession()->setTitle($parameters['title']);
        $paid->setsession()->setDescription($parameters['description']);
        $paid->setsession()->setType("free");
        $paid->setsession()->setModifiedAt(new \DateTime());
        $paid->setPrice($parameters['price']);
        $em=$this->getDoctrine()->getManager();
        $em->persist($paid);
        $em->flush();

        return new Response("Paid session updated successfully");
    }



    /**
     * @Route("/api/paidsessionDetails/{id}", name="paid_session_delete_api")
     */
    public function details(Request $request,NormalizerInterface $normalizer,int $id)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')
            ->where('t.session=?1')->setParameter(1,$id)->getQuery()->getSingleResult();
        $paid=new Paidsession();
        if($session->getType()=="paid"){
            $p=$this->getDoctrine()->getRepository(session::class)->find($session->getId());
            $paid=$p;
        }
        $jsonContent=$normalizer->normalize($paid, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
}

<?php

namespace App\Controller;

use App\Entity\Paidsession;
use App\Entity\session;
use App\Entity\sessionCategory;
use App\Entity\User;
use Hshn\Base64EncodedFile\HttpFoundation\File\Base64EncodedFile;
use Hshn\Base64EncodedFile\HttpFoundation\File\UploadedBase64EncodedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class sessionApiController extends AbstractController
{
    /**
     * @Route("/api/sessions", name="session_list_api")
     */
    public function list(NormalizerInterface $normalizer)
    {
        $paid=$this->getDoctrine()->getRepository(Paidsession::class)->findAll();
        $sessions = $this->getDoctrine()->getRepository(session::class)->findBy(['isDeleted'=>0],['createdAt'=>'desc']);
        foreach ( $sessions as $t ){
            foreach ( $paid as $p ){
                if($p->getsession()==$t){
                    $key = array_search($t, $sessions);
                    if ($key !== false) {
                        unset($sessions[$key]);
                    }
                }
            }
        }

        $all=array_merge($paid,$sessions);
        $data=$normalizer->normalize($all, 'json',['groups'=>'post:read']);

        return new Response(json_encode($data));
    }


    /**
     * @Route("/api/addsession", name="session_add_api")
     */
    public function add(Request $request,SerializerInterface $serializer)
    {

        $content=$request->getContent();
        $data=$serializer->deserialize($content,session::class,'json');
        $parameters = json_decode($content, true);

        $uploads_directory = $this->getParameter('images_directory_task');
        $filename = md5(uniqid()) . '.' .$parameters['ext'];
        $file=new UploadedBase64EncodedFile(new Base64EncodedFile($parameters['file']));
        $file->move(
            $uploads_directory,
            $filename
        );
        $fs=new Filesystem();
        $fs->mirror($this->getParameter('images_directory_task'), '../../CoHeal-Desktop/src/coheal/resources/images/tasks');
        $user=$this->getDoctrine()->getRepository(User::class)->find($parameters['u']);
        $cat=$this->getDoctrine()->getRepository(sessionCategory::class)->find($parameters['cat']);
        $data->setU($user);
        $data->setCat($cat);
        $data->setCreatedAt(new \DateTime());
        $data->setModifiedAt(new \DateTime());
        $data->setImgUrl($filename);
        $em=$this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();

        return new Response("session added successfully");
    }

    /**
     * @Route("/api/updatesession/{id}", name="session_update_api")
     */
    public function update(Request $request,int $id)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->find($id);
        $content=$request->getContent();
        $parameters = json_decode($content, true);

        /*$user=$this->getDoctrine()->getRepository(User::class)->find($parameters['u']);
        $cat=$this->getDoctrine()->getRepository(sessionCategory::class)->find($parameters['cat']);
        $session->setU($user);
        $session->setCat($cat);*/
        $session->setTitle($parameters['title']);
        $session->setDescription($parameters['description']);
        $session->setType("free");
        $session->setModifiedAt(new \DateTime());
        $em=$this->getDoctrine()->getManager();
        $em->persist($session);
        $em->flush();

        return new Response("session updated successfully");
    }

    /**
     * @Route("/api/deletesession/{id}", name="session_delete_api")
     */
    public function delete(Request $request,NormalizerInterface $normalizer,int $id)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->createQueryBuilder('t')->update(session::class,'t')->set('t.isDeleted','?1')->where('t.sessionId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return new Response("session deleted successfully");
    }


    /**
     * @Route("/api/sessionDetails/{id}", name="session_details_api")
     */
    public function details(NormalizerInterface $normalizer,int $id)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->findBy(['sessionId'=>$id]);
        $jsonContent=$normalizer->normalize($session, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/api/getsession/{title}", name="session_p_api")
     */
    public function session(NormalizerInterface $normalizer,string $title)
    {
        $session=$this->getDoctrine()->getRepository(session::class)->findBy(['title'=>$title]);
        $jsonContent=$normalizer->normalize($session, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/api/participatesession/{id}", name="participate")
     */
    public function success(Request $request,int $id): Response
    {
        $session=$this->getDoctrine()->getRepository(session::class)->find( $id);
        $content=$request->getContent();
        $parameters = json_decode($content, true);
        $u=$this->getDoctrine()->getRepository(User::class)->find($parameters['u']);

        $u->addsession($session);
        $session->addUser($u);
        $em = $this->getDoctrine()->getManager();
        $em->persist($session);
        $em->flush();
        $em->persist($u);
        $em->flush();
        return new Response("participate");

    }

    /**
     * @Route("/api/getParticipatesession/{id}", name="session_participate_api")
     */
    public function participate(NormalizerInterface $normalizer,int $id)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $jsonContent=$normalizer->normalize($user->getsession(), 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
}

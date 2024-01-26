<?php

namespace App\Controller;

use App\Entity\session;
use App\Entity\sessionCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class sessionCategoryApiController extends AbstractController
{
    /**
     * @Route("/api/sessionCategory", name="session_category_api")
     */
    public function index(NormalizerInterface $normalizer)
    {
        $categories=$this->getDoctrine()->getRepository(sessionCategory::class)->findBy(['isDeleted'=>'0'],['createdAt'=>'DESC']);
        $jsonContent=$normalizer->normalize($categories, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/api/getsessionCategory/{name}", name="session_category_get_api")
     */
    public function cat(NormalizerInterface $normalizer,string $name)
    {
        $categories=$this->getDoctrine()->getRepository(sessionCategory::class)->findBy(['name'=>$name]);
        $jsonContent=$normalizer->normalize($categories, 'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
}

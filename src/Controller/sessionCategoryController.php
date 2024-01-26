<?php

namespace App\Controller;

use App\Entity\Paidsession;
use App\Entity\session;
use App\Entity\sessionCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class sessionCategoryController extends AbstractController
{
    /**
     * @Route("/session/category", name="session_category")
     */
    public function index(): Response
    {
        return $this->render('session_category/index.html.twig', [
            'controller_name' => 'sessionCategoryController',
        ]);
    }

    /**
     * @Route("/listsessionCategory", name="session_category_list")
     */
    public function sessionCategoryList()
    {
        $categories = $this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t ')
            ->addSelect('tc.catId, tc.name, tc.imgUrl,count(t) as totalsessions')->join('t.cat','tc')
            ->where('t.cat=tc.catId')
            ->andWhere('tc.isDeleted=0')->andWhere('t.isDeleted=0')->groupBy('tc.catId ')->orderBy('tc.createdAt', 'desc')
            ->getQuery()->getResult();

        //$categories = $this->getDoctrine()->getRepository(sessionCategory::class)->createQueryBuilder('tc')->where('tc.isDeleted=0')->orderBy('tc.createdAt', 'desc')->getQuery()->getResult();
        return $this->render('/session_category/all_categories.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/sessionsByCategory/{id}", name="sessions_by_category")
     */
    public function sessionsByCategory(int $id)
    {
        $paid=$this->getDoctrine()->getRepository(Paidsession::class)->findAll();
        $sessions = $this->getDoctrine()->getRepository(session::class)->findBy(['isDeleted'=>0,'cat'=>$id]);
        /* $sessions = $this->getDoctrine()->getRepository(session::class)->createQueryBuilder(' t ')
             ->where('  t.isDeleted=0 and t.cat=?1  order by t.createdAt DESC ')
             ->setParameter(1, $id)->getQuery()->getResult();*/
        /*   $paid = $this->getDoctrine()->getRepository(Paidsession::class)->createQueryBuilder('pt')
               ->join('pt.session','t')->addSelect('t')
               ->where('t.isDeleted=0')->andWhere('t.cat=?1')->setParameter(1, $id)
               ->orderBy('t.createdAt', 'desc')->getQuery()->getResult();*/
        $all=array_merge($paid,$sessions);
        return $this->render('/session_category/sessions_by_category.html.twig', ['sessions'=>$sessions,'paid'=>$paid]);
    }



    /**
     * @Route("/addsessionCategory", name="addsessionCategory")
     */
    public function addsessionCategory(Request $request)
    {

        $category = new sessionCategory();
        $form = $this->createFormBuilder($category)
            ->add('name', TextType::class)
            ->add('ImgUrl', FileType::class, ['required' => true])
            ->add('add', SubmitType::class, ['label' => 'Add new session Category'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $category->getImgUrl();
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory_session'),
                    $filename
                );
                $fs=new Filesystem();
                $fs->mirror($this->getParameter('images_directory_session'), '../../CoHeal-Desktop/src/coheal/resources/images/sessions');

            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $em = $this->getDoctrine()->getManager();
            $category->setImgUrl($filename);
            $category->setCreatedAt(new \DateTime());
            $category->setModifiedAt(new \DateTime());
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('sessions');
        }
        return $this->render('/BackOffice/add_sessionCategory.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/updatesessionCategory/{id}", name="update_sessionCategory")
     */
    public function update(Request $request,int $id): Response
    {
        $category=$this->getDoctrine()->getRepository(sessionCategory::class)->find($id);
        $form = $this->createFormBuilder($category)
            ->add('name', TextType::class)
            ->add('update', SubmitType::class, ['label' => 'update session Category'])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('sessions');
        }
        return $this->render('/BackOffice/update_sessionCategory.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/deletesessionCategory/{id}", name="delete_sessionCategory")
     */
    public function delete(int $id)
    {
        $sessions=$this->getDoctrine()->getRepository(sessionCategory::class)->createQueryBuilder('tc')->update(sessionCategory::class,'tc')->set('tc.isDeleted','?1')->where('tc.catId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return $this->redirectToRoute("sessions");
    }


}

<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Entity\User;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


class RecipeController extends AbstractController
{
    /**
     * @Route("/recipe", name="recipe")
     */
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    /**
     * @Route("/recipeHome", name="recipeHome")
     */
    public function home(): Response
    {
        return $this->render('recipe/recipeHome.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    /**
     * @Route("/addRecipe", name="addRecipe")
     */
    public function addRecipe(Request $request )
    {   
        $recipe = new Recipe();
        $form =$this ->createForm(RecipeType::class,$recipe);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
                    
        $user = $this->getUser();
        $recipe->setUser($user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           //$file = $recipe->getImgUrl();
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

            $em=$this->getDoctrine()->getManager();
            $recipe->setImgUrl($filename);
            $recipe->setCreatedAt(new \DateTime());
            $recipe->setModifiedAt(new \DateTime());
            $em->persist($recipe);
            $em->flush();
            return $this->redirectToRoute('allRecipes');
        }
        return $this->render('/recipe/addRecipe.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/recipeDetails/{idr}", name="recipeDetails")
     */
    public function recipeDetails(int $idr)
    {
        $recipes=$this->getDoctrine()->getRepository(Recipe::class)->createQueryBuilder('r')
            ->where('r.recipeId=?1')->setParameter(1,$idr)->getQuery()->getSingleResult();
            $repository = $this->getDoctrine()->getrepository(Recipe::class);//recuperer repisotory

            $Recipez = $repository->findBy(
                ['recipeId' => $idr]
            );
            foreach ($Recipez as $b ){

                $b->setViews( $b->getViews()+1);
                    $em = $this->getDoctrine()->getManager();//recupuration entity manager
                    $em->persist($b);//l'ajout de la objet cree
                    $em->flush();
                }

               

        return $this->render('recipe/recipeDetails.html.twig', ['recipes'=>$recipes]);

 
    }

    /**
     * @Route("/allRecipes", name="allRecipes")
     */
    public function allRecipes()
    {
        $recipes=$this->getDoctrine()->getRepository(Recipe::class)->findBy(['isDeleted'=>'0'],['createdAt'=>'DESC']);
        return $this->render('/recipe/allRecipes.html.twig', ['recipes'=>$recipes]);
    }

    /**
     * @Route("/updateRecipe/{id}", name="updateRecipe")
     */
    public function update(Request $request,int $id,FlashyNotifier $flashy): Response
    {
        $recipe=$this->getDoctrine()->getRepository(Recipe::class)->find($id);
        $form=$this->createFormBuilder($recipe)
            ->add('Title', TextType::class, ['required'=>true, 'attr'=>['class' => 'form-control']])
            ->add('Description', TextareaType::class, ['required'=>true, 'attr'=>['placeholder'=>'Write Description']])
            ->add('Cat', EntityType::class, ['class'=>RecipeCategory::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('tc')
                        ->where('tc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,'label'=> 'Category',
                'attr' => ['class' => 'form-control']])
            ->add('Ingredients', CKEditorType::class)
            ->add('Steps', CKEditorType::class)
            ->add('Calories', IntegerType::class)
            ->add('Duration', IntegerType::class)
            ->add('Persons', IntegerType::class)
            ->add('Update', SubmitType::class, ['label'=>'Save', 'attr'=>['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($recipe);
            $em->flush();
            return $this->redirectToRoute('recipeDetails', array('idr' => $recipe->getId()));
            $flashy->success('recipe created!', 'http://your-awesome-link.com');

        }

     

        return $this->render('/recipe/updateRecipe.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/deleteRecipe/{id}", name="deleteRecipe")
     */
    public function deleteRecipe(int $id)
    {
        $recipes=$this->getDoctrine()->getRepository(Recipe::class)->createQueryBuilder('r')->update(Recipe::class,'r')->set('r.isDeleted','?1')->where('r.recipeId = ?2')->setParameter(1, 1)
            ->setParameter(2, $id)->getQuery()->execute();
        return $this->redirectToRoute("allRecipes");
    }

    /**
     * @Route("/printRecipe/{id}", name="printRecipe")
     */
    public function printRecipe(int $id)
    {
        $recipes=$this->getDoctrine()->getRepository(Recipe::class)->createQueryBuilder('r')->where('r.recipeId=?1')->setParameter(1,$id)->getQuery()->getResult();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);
        $pdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in twig file
        $html = $this->renderView('recipe/recipePDF.html.twig', [
            'title' => "Recipe in PDF file",
            'recipes'=>$recipes
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        // Output the generated PDF to be downloaded
        $dompdf->stream("recipePDF.pdf", [
            "Attachment" => true
        ]);
    }

    /**
     * @Route("/yourRecipes", name="yourRecipes")
     */
    public function yours(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u = $this->getUser();
        $user=$this->getDoctrine()->getRepository(User::class)->find($u);
        if( in_array("ROLE_Nutritionist", $u->getRoles())){
            $recipes=$this->getDoctrine()->getRepository(Recipe::class)->createQueryBuilder(' r')
                ->where('r.u=?1')->setParameter('1',$u)->andWhere('r.isDeleted=0')->getQuery()->getResult();

        }
        else{
            $recipes=$user->getRecipe();

        }
        return $this->render('recipe/yours.html.twig', [
            'recipes' => $recipes,
        ]);

     

    }


 /**
     *
     * @Route("/SearchRecipe ", name="SearchRecipe")
     */
   public function searchRecipe(Request $request,NormalizerInterface $Normalizer  ,  RecipeRepository $repository)
    {
      //  $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $requestString=$request->get('searchValueBackk');

        $recipe = $repository->findByTitle($requestString);


        $jsonContent = $Normalizer->normalize($recipe, 'json',['groups'=>'BS']);
        $retour=json_encode($jsonContent);
        return new Response($retour);

    }


    
}

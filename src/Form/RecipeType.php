<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use App\Entity\RecipeCategory;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('Title', TextType::class)
        ->add('Description', TextareaType::class )
        ->add('Cat', EntityType::class, ['class'=>RecipeCategory::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rc')
                    ->where('rc.isDeleted=0');}, 'choice_label'=>'name','required'=>true,'label'=> 'Category',
            'attr' => ['class' => 'form-control']])
        ->add('Ingredients', CKEditorType::class)
        ->add('Steps',CKEditorType::class)
        ->add('Calories', IntegerType::class)
        ->add('Duration', IntegerType::class)
        ->add('Persons', IntegerType::class)
        ->add('ImgUrl', FileType::class, ['label'=>'Image','required'=>true])
        ->add('add', SubmitType::class, ['label'=>'Add recipe'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}

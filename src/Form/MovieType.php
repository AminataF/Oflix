<?php

namespace App\Form;

use App\Entity\Movie;
use App\Entity\Genre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('synopsis')
            ->add('poster')
            ->add('rating')
            ->add('duration')
            ->add('type')
            ->add('summary')
            ->add('releaseDate', DateType::class, [
                "widget" => 'single_text'
            ])
            // ->add('createdAt')
            // ->add('updatedAt')
            ->add('genres', EntityType::class, [
                // ! ne pas oublier de dire de quelle entité en parle
                'class' => Genre::class,
                // ! Object of class App\Entity\Genre could not be converted to string
                // je dois préciser quelle propriété doit être afficher dans la liste déroulante
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Review;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => "Votre adresse email : mail@mondomaine.com"

                ]
            ])
            ->add('content')
            // ? https://symfony.com/doc/5.4/reference/forms/types/choice.html
            ->add('rating', ChoiceType::class, [
                'choices'  => [
                    "Meilleure note" => [
                        'Excellent' => 5,
                        'Très bon' => 4,
                    ],
                    'Bon'  => 3,
                    'Peut mieux faire' => 2,
                    'A éviter' => 1
                ],
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('reactions', ChoiceType::class, [
                // Rire, Pleurer, Réfléchir, Dormir, Rêver.
                // (smile, cry, think, sleep, dream)
                'choices' => [
                    'Rire' => 'smile',
                    'Pleurer' => 'cry',
                    'Réfléchir' => 'think',
                    'Dormir' => 'sleep',
                    'Rêver' => 'dream',
                ],
                // ? https://symfony.com/doc/5.4/reference/forms/types/choice.html#select-tag-checkboxes-or-radio-buttons
                // ! Notice: Array to string conversion
                'multiple' => true,
                'expanded' => true,

            ])
            ->add('watchedAt', DateType::class, [
                // renders it as a single text box
                'widget' => 'single_text',
                // ? https://symfony.com/doc/5.4/reference/forms/types/date.html#input
                // ! Expected argument of type "DateTimeImmutable", "instance of DateTime" given at property path "watchedAt".
                'input' => 'datetime_immutable',
                'data' => new DateTimeImmutable()
            ])
            // * je ne met pas la propriété movie, car j'obtiens l'info depuis la route
            // je fait l'association manuellement dans le controller
            // ->add('movie')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Travel;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('postedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('travel', EntityType::class, [
                'class' => Travel::class,
                'choice_label' => function (Travel $travel) {
                    return $travel->getDestination()->getName() . ' ' . $travel->getStartAt()->format('d/m/Y') . ' (id: ' . $travel->getId() . ')';
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}

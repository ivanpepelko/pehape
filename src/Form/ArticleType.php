<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Gallery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'Naslov'])
            ->add('description', null, ['label' => 'Opis'])
            ->add('body', null, ['label' => 'Tekst'])
            ->add('releaseDate', DateTimeType::class, ['html5' => true, 'widget' => 'single_text'])
            ->add('draft', null, ['label' => 'U pripremi'])
            ->add(
                'gallery',
                null,
                [
                    'label'        => 'Galerija',
                    'class'        => Gallery::class,
                    'choice_label' => 'title',
                    'required'     => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Article::class,
            ]
        );
    }
}

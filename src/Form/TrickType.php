<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('group', EntityType::class, [
                'class' => TrickGroup::class,
                'choice_label' => 'name'
            ])
            ->add('defaultPicture', FileType::class, [
                'mapped'    => false,
                'required'  => true
            ])
            ->add('pictures', FileType::class, [
                'mapped'    => false,
                'required'  => false,
                'multiple'  => true
            ])
            ->add('videos', TextareaType::class, [
                'mapped'    => false,
                'required'  => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}

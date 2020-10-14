<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\SearchEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('keyword', SearchType::class, ['label' => 'Mots-clés'])
            ->add('dateStart', DateType::class, ['format' => DateType::HTML5_FORMAT, 'label' => 'Du'])
            ->add('dateEnd', DateType::class, ['format' => DateType::HTML5_FORMAT, 'label' => 'au'])
            ->add('includeCreatedEvent', CheckboxType::class, ['label' => 'Sorties dont je suis le créateur'])
            ->add('includeRegistered', CheckboxType::class, ['label' => 'Sorties auxquelles je suis inscrit'])
            ->add('includeNotRegistered', CheckboxType::class, ['label' => 'Sorties auxquelles je ne suis pas inscrit'])
            ->add('includePastEvent', CheckboxType::class, ['label' => "Sorties passées"])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name'
            ])
            ->add('submit', SubmitType::class, ['label' => 'Rechercher'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchEvent::class,
        ]);
    }
}

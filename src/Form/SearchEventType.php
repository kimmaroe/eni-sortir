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
        $builder->setMethod('GET');
        $builder
            ->add('keyword', SearchType::class, ['label' => 'Mots-clés', 'required' => false])
            ->add('dateStart', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Du',
                'attr' => ['class' => 'w-2/3'],
                'required' => false
            ])
            ->add('dateEnd', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'au',
                'attr' => ['class' => 'w-2/3'],
                'required' => false

            ])
            ->add('includeCreatedEvent', CheckboxType::class, ['label' => 'Sorties dont je suis le créateur', 'required' => false])
            ->add('includeRegistered', CheckboxType::class, ['label' => 'Sorties auxquelles je suis inscrit', 'required' => false])
            ->add('includeNotRegistered', CheckboxType::class, ['label' => 'Sorties auxquelles je ne suis pas inscrit', 'required' => false])
            ->add('includePastEvent', CheckboxType::class, ['label' => "Sorties passées", 'required' => false])
            ->add('campus', EntityType::class, [
                'placeholder' => 'Choisir un campus...',
                'class' => Campus::class,
                'choice_label' => 'name',
                'required' => false
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

<?php

namespace App\Form;

use App\Entity\Event;
use App\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'Nom de la sortie'
            ])
            ->add('dateStart', null,  [
                    'widget' => 'single_text',
                    'html5' => true,
                    'label' => 'Date de début'
            ])
            ->add('dateEnd', null,  [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Date de fin'
            ])
            ->add('maxRegistrations', NumberType::class, [
                'label' => 'Nombre de places',
                'html5' => true,
            ])
            ->add('description')
            ->add('dateRegistrationEnded', null,  [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Clôture des inscriptions'
            ])
            ->add('city', EntityType::class, [
                'label' => 'Ville de la sortie',
                'class' => 'App\Entity\City',
                'choice_label' => 'name',
                //on ne récupère que les villes des Pays de la Loire
                'query_builder' => function(CityRepository $cityRepository){
                    return $cityRepository->createQueryBuilder('c')->andWhere('c.region = 52')->orderBy('c.name', 'ASC');
                }
            ])
            ->add('location', ChoiceType::class, [
                'label' => 'Adresse',
                'choices' => [],
                'mapped' => false,
                'placeholder' => "Choisissez d'abord une ville !"
            ])
            ->add('submit', SubmitType::class, ['label' => 'Créer ma sortie !'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

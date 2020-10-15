<?php

namespace App\Form;

use App\Entity\Location;
use App\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('streetNumber')
            ->add('streetName')
            ->add('lat')
            ->add('lng')
            ->add('zip')
            ->add('city', EntityType::class, [
                'label' => 'Ville de la sortie',
                'class' => 'App\Entity\City',
                'choice_label' => 'name',
                //on ne récupère que les villes des Pays de la Loire
                'query_builder' => function(CityRepository $cityRepository){
                    return $cityRepository->createQueryBuilder('c')->andWhere('c.region = 52')->orderBy('c.name', 'ASC');
                }
            ])
            ->add('submit', SubmitType::class, ['label' => 'Créer le lieu !'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}

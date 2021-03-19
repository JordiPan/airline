<?php

namespace App\Form;

use App\Entity\Airplane;
use App\Entity\Airport;
use App\Entity\Flight;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlightFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('beginAirport', EntityType::class,[
                'class' => Airport::class,
                'choice_label' => 'name'
            ])
            ->add('destination', EntityType::class,[
                'class' => Airport::class,
                'choice_label' => 'name'
            ])
            ->add('date',DateType::class,[
                'widget' => 'single_text'
            ])
            ->add('time', TimeType::class,[
                'widget' => 'single_text'
            ])
            ->add('airplane', EntityType::class,[
                'class' => Airplane::class,
                'choice_label' => 'name'
            ])
            ->add('price')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Flight::class,
        ]);
    }
}

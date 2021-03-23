<?php

namespace App\Form;

use App\Entity\Airport;
use App\Entity\Flight;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SearchFlightFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('trip', ChoiceType::class,[
                'choices' => [
                    'Return trip' => 'return',
                    'One way' => 'oneWay'
                ]
            ])
            ->add('beginAirport', EntityType::class,[
                'class' => Airport::class,
                'choice_label' => 'name'
            ])
            ->add('destination', EntityType::class,[
                'class' => Airport::class,
                'choice_label' => 'name'
            ])
            ->add('date',DateType::class,[
                'widget' => 'single_text',
                'data' => new \DateTime()
            ])
            ->add('returnDate', DateType::class,[
                'widget' => 'single_text',
                'required' => false,
                'data' => new \DateTime()
            ])
            ->add('group_size', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}

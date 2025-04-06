<?php

namespace App\Form;

use App\Entity\Conge;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'label' => 'Employé',
                'placeholder' => 'Sélectionnez un employé',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Congé payé' => 'Congé payé',
                    'Congé maladie' => 'Congé maladie',
                    'Congé sans solde' => 'Congé sans solde',
                    'RTT' => 'RTT',
                    'Autre' => 'Autre',
                ],
                'label' => 'Type de congé',
                'required' => true,
            ])
            ->add('date_debut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'required' => true,
            ])
            ->add('date_fin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'required' => true,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conge::class,
        ]);
    }
}
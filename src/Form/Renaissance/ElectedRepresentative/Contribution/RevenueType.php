<?php

namespace App\Form\Renaissance\ElectedRepresentative\Contribution;

use App\ElectedRepresentative\Contribution\ContributionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RevenueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('revenueAmount', NumberType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ContributionRequest::class,
                'validation_groups' => ['fill_revenue'],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_renaissance_elected_representative_contribution';
    }
}

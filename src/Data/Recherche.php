<?php

namespace App\Data;

use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;





class Recherche
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', SearchType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Rechercher...'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher'
            ]);
    }


}


namespace App\Form;



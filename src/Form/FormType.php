<?php

namespace App\Form;


use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom',TextareaType::class,[
                'label' => 'Rechercher une sortie  ',
                'required' => false,
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur   ',
                'required' => false,
            ])
            ->add('inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/te   ',
                'required' => false,
            ])

            ->add('noninscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/te   ',
                'required' => false,
            ])
            ->add('passe', CheckboxType::class, [
                'label' => 'Sorties passées   ',
                'required' => false,
            ])

            ->add('debut1', DateTimeType::class, array(
                'label' => 'Date comprise entre ',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,

            ))
            ->add('debut2', DateTimeType::class, array(
                'label' => 'et',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,

            ))

            ->add('Campus', EntityType::class, [
            'class' => Campus::class,
            'choice_label' => 'nom',
            'placeholder' => 'Sélectionner un campus',
            'required' => false,
        ]);



    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

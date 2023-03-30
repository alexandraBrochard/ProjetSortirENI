<?php

namespace App\Form;


use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Doctrine\ORM\EntityRepository;
use phpDocumentor\Reflection\Types\False_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('duree')
            ->add('dateLimiteInscription', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('nbInscriptionsMax')
            ->add('infosSortie')

            ->add('lieu', EntityType::class, [
            'class' => Lieu::class,
            'choice_label' => 'nom',
            'placeholder' => 'Sélectionner un lieu',
            'required' => false,
            ])

            ->add('nouveaulieu', LieuType::class, [
                'mapped'=> false,
                'required'=>false,
                'property_path' => 'lieu',
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!empty($data['nouveaulieu']['nom'])) {
                $form->remove('item');

                $form->add('nouveaulieu', LieuType::class, [
                    'required' => TRUE,
                    'mapped' => TRUE,
                    'property_path' => 'lieu',
                ]);
            }
        });
            /*'query_builder' => function (EntityRepository $er) {
               return $er->createQueryBuilder('l')
                   ->orderBy('l.nom', 'ASC');
           },*/
            /*  ->add('etat', EntityType::class,
                  [
                      'class' => Etat::class,
                      'choice_label' => 'libelle',
                  ])*/



    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}

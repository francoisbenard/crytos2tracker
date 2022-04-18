<?php

namespace App\Form;

use App\Entity\Cryptolist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Entity\Mycrypto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyCryptoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('crypto', EntityType::class, [
                'class' => Cryptolist::class,
                'choice_label' => 'name',])
            ->add('quantity')
            ->add('price', NumberType::class, array (
                'required' => true,
                'scale' => 2,
                'attr' => array(
                    'min' => 0,
                    'max' => 1000000,
                    'step' => 0.0000001,
                ),
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mycrypto::class,
        ]);
    }
}

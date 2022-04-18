<?php

namespace App\Form;

use App\Entity\Mycrypto;
use App\Form\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveCryptoQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('crypto', EntityType::class, [
                'class' => Mycrypto::class,
                'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('m')
                            ->from( 'App\Entity\Cryptolist', 'c')
                            ->where('c.id = m.crypto');
                    },
                'choice_label' => 'crypto.name',
            ])
            ->add('quantity');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}

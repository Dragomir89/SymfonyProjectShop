<?php

namespace AppBundle\Form;

use AppBundle\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name',    TextType::class, array('attr' => array('readonly' => 'true')))
            ->add('price',   TextType::class, array('attr' => array('readonly' => 'true')))
            ->add('newPrice',TextType::class, array('attr' => array('readonly' => 'true')))
            ->add('quantity',TextType::class, array('attr' => array('readonly' => 'true')))
            ->add('user',    TextType::class, array('attr' => array('readonly' => 'true')))
            ->add('id',     HiddenType::class,array('attr' => array('readonly' => 'true')))
            ->add('Buy',SubmitType::class, array('attr' => array('value' => 'Send')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' =>Product::class
        ]);
    }

    public function getName()
    {
        return 'app_bundle_buy_product_type';
    }
}

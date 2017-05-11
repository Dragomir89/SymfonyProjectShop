<?php

namespace AppBundle\Form;

use AppBundle\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class,array(
                'label'=> "Име"
            ))
            ->add("discount", NumberType::class,array(
                'label'=> "Отстъпка %"
            ))
//            ->add("startDate", TextType::class,
//                array(
//                    'label'=> "Начална Дата",
//                    'attr'=>["class" =>"date_style"]
//                ))
//            ->add("endDate", TextType::class,
//                array(
//                    'label'=> "Крайна Дата",
//                    'attr'=>["class" =>"date_style"]
//                ))
            ->add("startDate",DateType::class, array(
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day')))
            ->add("endDate", DateType::class, array(
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day'
                )
            ))
            ->add("Save", SubmitType::class,array(
                'label'=> "Запази",
                'attr'=>["class" =>"btn btn-success"]
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(["data_class" => Promotion::class]);
    }

    public function getName()
    {
        return 'app_bundle_promotion_type';
    }
}

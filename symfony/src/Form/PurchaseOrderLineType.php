<?php

namespace App\Form;

use App\Entity\PurchaseOrderLine;
use App\Entity\PurchaseOrder;
use App\Entity\Product;
use App\Entity\Restaurant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class PurchaseOrderLineType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class)
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choices' => $options['restaurant']->getProducts()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrderLine::class,
            'restaurant' => Restaurant::class
        ]);
        $resolver->addAllowedTypes('restaurant', Restaurant::class);
    }
}

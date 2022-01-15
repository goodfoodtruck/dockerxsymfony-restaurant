<?php

namespace App\Form;

use App\Entity\PurchaseOrder;
use App\Entity\Product;
use App\Entity\Restaurant;
use App\Form\PurchaseOrderLineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PurchaseOrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('purchaseOrderLines', CollectionType::class, [
                'entry_type' => PurchaseOrderLineType::class,
                'entry_options' => [
                    'restaurant' => $options['restaurant'],
                    'label' => false
                ],
                'label' => 'Order :',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
            'restaurant' => Restaurant::class
        ]);
        $resolver->setAllowedTypes('restaurant', Restaurant::class);
    }
}

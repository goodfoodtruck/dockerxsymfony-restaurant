<?php

namespace App\Controller;

use App\Entity\PurchaseOrder;
use App\Form\PurchaseOrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RestaurantRepository;
use App\Repository\PurchaseOrderRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class HomeController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'home')]
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        $restaurants = $restaurantRepository->findAll();

        return $this->render('home/index.html.twig', [
            'restaurants' => $restaurants
        ]);
    }

    /**
     * @Route("/restaurant/{id}", name="restaurant_index")
     */
    public function restaurant(Restaurant $restaurant): Response
    {
        return $this->render('home/restaurant.html.twig', [
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurant/{id}/makePurchaseOrder', name: 'make_purchase_order')]
    public function restaurantPurchase(Request $request, Restaurant $restaurant, EntityManagerInterface $em): Response
    {
        $purchaseOrder = new PurchaseOrder();


        $form = $this->createForm(PurchaseOrderType::class, $purchaseOrder, [
            'restaurant' => $restaurant,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();

            $uuid = Uuid::v4();
            $purchaseOrder->setPurchaseOrderId(strval($uuid));
            $purchaseOrder->setRestaurant($restaurant);
            $purchaseOrder->setUser($user);

            $totalPrice = 0;
            foreach ($purchaseOrder->getPurchaseOrderLines() as $purchaseOrderLine) {
                $totalPrice += $purchaseOrderLine->getProduct()->getPrice() * $purchaseOrderLine->getQuantity();
            }

            $purchaseOrder->setTotalPrice($totalPrice);

            $em->persist($purchaseOrder);
            $em->flush();

            return $this->redirectToRoute('restaurant_index', ['id' => $restaurant->getId()], Response::HTTP_SEE_OTHER);
        }


        return $this->render('order/purchase.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/purchaseOrders', name: 'purchase_order')]
    public function purchaseOrders(): Response
    {
        $user = $this->security->getUser();

        $orders = $user->getPurchaseOrders();

        return $this->render('order/index.html.twig', [
            'purchase_orders' => $orders
        ]);
    }

    #[Route('/profile/purchaseOrders/{id}', name: 'purchase_orders')]
    public function purchaseOrder(PurchaseOrderRepository $order): Response
    {
        return $this->render('order/order.html.twig', [
            'purchase_orders' => $order
        ]);
    }
}

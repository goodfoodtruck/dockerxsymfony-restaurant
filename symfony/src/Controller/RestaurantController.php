<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Product;
use App\Form\RestaurantType;
use App\Form\ProductType;
use App\Repository\RestaurantRepository;
use App\Repository\PurchaseOrderLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;

/**
 * @Route("/admin/restaurant")
 */
class RestaurantController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="owner_restaurant_index", methods={"GET"})
     */
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        $user = $this->security->getUser();
        $restaurants = $user->getRestaurants();

        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    /**
     * @Route("/new", name="restaurant_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();
            $restaurant->addOwner($user);

            $entityManager->persist($restaurant);
            $entityManager->flush();

            return $this->redirectToRoute('owner_restaurant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('restaurant/new.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="restaurant_show", methods={"GET"})
     */
    public function show(Restaurant $restaurant): Response
    {
        $products = $restaurant->getProducts();
        $orders = $restaurant->getPurchaseOrders();

        return $this->render('restaurant/show_owner.html.twig', [
            'restaurant' => $restaurant,
            'products' => $products,
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/{id}/edit", name="restaurant_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        $products = $restaurant->getProducts();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('owner_restaurant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('restaurant/edit.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
            'products' => $products,
        ]);
    }

    /**
     * @Route("/{id}/product/new", name="restaurant_add_product", methods={"GET", "POST"})
     */
    public function newProduct(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setRestaurant($restaurant);

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('restaurant_edit', ['id' => $restaurant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('restaurant/addProduct.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
            'product' => $product,
        ]);
    }

    /**
     * @Route("/product/{id}/edit", name="product_edit", methods={"GET", "POST"})
     */
    public function editProduct(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $restaurant = $product->getRestaurant();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('restaurant_edit', ['id' => $restaurant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('restaurant/edit-product.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
            'product' => $product,
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_delete", methods={"POST"})
     */
    public function deleteProduct(Request $request, Product $product, EntityManagerInterface $entityManager, PurchaseOrderLineRepository $purchaseOrderLineRepository): Response
    {
        $restaurant = $product->getRestaurant();
        $orderLines = $purchaseOrderLineRepository->findBy(['product' => $product]);
        $orders = [];

        if (isset($orderLines)) {
            foreach ($orderLines as $orderLine) {
                $order = $orderLine->getPurchaseOrder();
                if (!in_array($order, $orders)) {
                    array_push($orders, $order);
                }
            }
        }


        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            if (isset($orderLines)) {
                foreach ($orderLines as $orderLine) {
                    $entityManager->remove($orderLine);
                }
                foreach ($orders as $order) {
                    $entityManager->remove($order);
                }
            }
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('restaurant_edit', ['id' => $restaurant->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}", name="restaurant_delete", methods={"POST"})
     */
    public function delete(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $restaurant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($restaurant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('owner_restaurant_index', [], Response::HTTP_SEE_OTHER);
    }
}

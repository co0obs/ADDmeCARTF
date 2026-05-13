<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted; 


#[IsGranted('ROLE_CUSTOMER')]
class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add(int $id, ProductRepository $productRepo, EntityManagerInterface $entityManager): Response
    {
        // Login
        $user = $this->getUser();

        // Security
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to add items to your cart.');
            return $this->redirectToRoute('app_login');
        }

        // Fetch Cart
        $cart = $user->getCart();
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
        }

        // Find product
        $product = $productRepo->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        // Enforce limit
        $currentTotalItems = 0;
        foreach ($cart->getCartItems() as $item) {
            $currentTotalItems += $item->getQuantity();
        }

        if ($currentTotalItems >= 300) {
            $this->addFlash('error', 'Cart Limit Reached: You cannot add more than 300 items.');
            return $this->redirectToRoute('app_product_catalog');
        }

        // Check existing cart
        $existingCartItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct() === $product) {
                $existingCartItem = $item;
                break;
            }
        }

        if ($existingCartItem) {
            // Increase quantity if it's already there
            $existingCartItem->setQuantity($existingCartItem->getQuantity() + 1);
        } else {
            // Create a brand new line item
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $cartItem->setCart($cart);
            $entityManager->persist($cartItem);
        }

        // Save 
        $entityManager->flush();
        $this->addFlash('success', $product->getName() . ' was added to your cart!');

        return $this->redirectToRoute('app_product_catalog');
    }

    #[Route('/cart', name: 'app_cart_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user || !method_exists($user, 'getCart') || !$user->getCart()) {
            return $this->render('cart/index.html.twig', [
                'cartItems' => [],
                'total' => 0
            ]);
        }

        $cartItems = $user->getCart()->getCartItems();
        $total = 0;
        $groupedItems = [];
        
        foreach ($cartItems as $item) {
            $total += $item->getProduct()->getEffectivePrice() * $item->getQuantity();
            
            $seller = $item->getProduct()->getSeller();
            $storeName = ($seller && $seller->getStoreName()) ? $seller->getStoreName() : 'ADDmeCART Official';
            
            if (!isset($groupedItems[$storeName])) {
                $groupedItems[$storeName] = [];
            }
            $groupedItems[$storeName][] = $item;
        }

        return $this->render('cart/index.html.twig', [
            'groupedItems' => $groupedItems,
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }
    
    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cartItem = $entityManager->getRepository(CartItem::class)->find($id);

        if ($cartItem && $cartItem->getCart()->getUser() === $user) {
            $entityManager->remove($cartItem);
            $entityManager->flush();
            
            $this->addFlash('success', 'Item removed from your cart.');
        } else {
            $this->addFlash('error', 'Could not remove that item.');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/cart/increase/{id}', name: 'app_cart_increase')]
    public function increase(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cartItem = $entityManager->getRepository(CartItem::class)->find($id);

        if ($cartItem && $cartItem->getCart()->getUser() === $user) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cartItem = $entityManager->getRepository(CartItem::class)->find($id);

        if ($cartItem && $cartItem->getCart()->getUser() === $user) {
            if ($cartItem->getQuantity() > 1) {
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
            } else {
                $entityManager->remove($cartItem);
            }
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_cart_index');
    }
}
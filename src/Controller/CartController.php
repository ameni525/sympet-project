<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/panier', name: 'cart_index')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getDetailedCart(),
            'total' => $cartService->getTotalAmount(),
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'cart_add')]
    public function add(Produit $produit, CartService $cartService): Response
    {
        if ($produit->getStock() < 1) {
            $this->addFlash('warning', 'Produit indisponible.');
            return $this->redirectToRoute('product_show', ['id' => $produit->getId()]);
        }

        $cartService->add($produit->getId());
        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/panier/reduire/{id}', name: 'cart_decrease')]
    public function decrease(Produit $produit, CartService $cartService): Response
    {
        $cartService->decrease($produit->getId());
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/panier/supprimer/{id}', name: 'cart_remove')]
    public function remove(Produit $produit, CartService $cartService): Response
    {
        $cartService->remove($produit->getId());
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/panier/vider', name: 'cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Panier vidé.');

        return $this->redirectToRoute('cart_index');
    }
}

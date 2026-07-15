<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private ProduitRepository $produitRepository
    ) {
    }

    public function getDetailedCart(): array
    {
        $items = [];

        foreach ($this->getCart() as $productId => $quantity) {
            $product = $this->produitRepository->find($productId);
            if (!$product instanceof Produit) {
                continue;
            }

            $lineTotal = (float) $product->getPrix() * $quantity;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        return $items;
    }

    public function getTotalAmount(): float
    {
        return array_reduce(
            $this->getDetailedCart(),
            fn (float $sum, array $item): float => $sum + $item['line_total'],
            0.0
        );
    }

    public function getItemsCount(): int
    {
        return array_sum($this->getCart());
    }

    public function add(int $productId): void
    {
        $cart = $this->getCart();
        $cart[$productId] = ($cart[$productId] ?? 0) + 1;
        $this->saveCart($cart);
    }

    public function decrease(int $productId): void
    {
        $cart = $this->getCart();
        if (!isset($cart[$productId])) {
            return;
        }

        if ($cart[$productId] <= 1) {
            unset($cart[$productId]);
        } else {
            --$cart[$productId];
        }

        $this->saveCart($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $this->saveCart($cart);
    }

    public function clear(): void
    {
        $this->saveCart([]);
    }

    public function getCart(): array
    {
        return $this->requestStack->getSession()->get(self::CART_KEY, []);
    }

    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set(self::CART_KEY, $cart);
    }
}

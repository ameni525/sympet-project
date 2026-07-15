<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(CommandeRepository $commandeRepository, EntityManagerInterface $entityManager): Response
    {
        $productsCount = $entityManager->getRepository(Produit::class)->count([]);
        $usersCount = $entityManager->getRepository(User::class)->count([]);
        $ordersCount = $commandeRepository->countAll();
        $revenue = $commandeRepository->getRevenueTotal();
        $monthlyRevenue = $commandeRepository->getMonthlyRevenue();
        $chartLabels = array_map(static fn (array $row): string => (string) $row['month'], $monthlyRevenue);
        $chartValues = array_map(static fn (array $row): float => (float) $row['total'], $monthlyRevenue);

        return $this->render('admin/dashboard.html.twig', [
            'productsCount' => $productsCount,
            'usersCount' => $usersCount,
            'ordersCount' => $ordersCount,
            'revenue' => $revenue,
            'monthlyRevenue' => $monthlyRevenue,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/admin/orders', name: 'admin_orders')]
    #[IsGranted('ROLE_ADMIN')]
    public function orders(CommandeRepository $commandeRepository): Response
    {
        return $this->render('admin/orders.html.twig', [
            'orders' => $commandeRepository->findBy([], ['dateCommande' => 'DESC']),
        ]);
    }

    #[Route('/admin/orders/{id}', name: 'admin_order_show', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function orderShow(Commande $order): Response
    {
        return $this->render('admin/order_show.html.twig', [
            'order' => $order,
        ]);
    }
}

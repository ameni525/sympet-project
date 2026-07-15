<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProductController extends AbstractController
{
    public function __construct(
        private PaginationService $paginationService
    ) {
    }

    #[Route('/products', name: 'product_index')]
    public function index(
        ProduitRepository $repo,
        CategorieRepository $catRepo,
        Request $request
    ): Response {
        $query = $repo->createQueryBuilder('p')->orderBy('p.createdAt', 'DESC')->getQuery();
        $products = $this->paginationService->paginate($query, $request);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $catRepo->findAll(),
        ]);
    }

    #[Route('/product/{id}', name: 'product_show')]
    public function show(Produit $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/search', name: 'product_search')]
    public function search(
        Request $request,
        ProduitRepository $repo,
        CategorieRepository $catRepo
    ): Response {
        $name = $request->query->get('name');
        $category = $request->query->get('category');
        $query = $repo->searchQuery($name, $category);
        $products = $this->paginationService->paginate($query, $request);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $catRepo->findAll(),
        ]);
    }

    #[Route('/admin/produits', name: 'produit_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(ProduitRepository $repo): Response
    {
        return $this->render('produit/index.html.twig', [
            'products' => $repo->findAll(),
        ]);
    }

    #[Route('/admin/produit/new', name: 'produit_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminNew(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit créé avec succès.');
            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/admin/produit/{id}', name: 'produit_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminShow(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/admin/produit/{id}/edit', name: 'produit_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/admin/produit/{id}/delete', name: 'produit_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDelete(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('produit_index');
    }
}

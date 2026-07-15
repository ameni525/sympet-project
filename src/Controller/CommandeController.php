<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Enum\StatutCommande;
use App\Repository\CommandeRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commandes')]
#[IsGranted('ROLE_USER')]
final class CommandeController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(STRIPE_SECRET_KEY)%')]
        private readonly string $stripeSecretKey
    ) {
    }

    #[Route('', name: 'order_history')]
    public function history(CommandeRepository $commandeRepository): Response
    {
        return $this->render('order/history.html.twig', [
            'orders' => $commandeRepository->findBy(
                ['user' => $this->getUser()],
                ['dateCommande' => 'DESC']
            ),
        ]);
    }

    #[Route('/{id}', name: 'order_show', requirements: ['id' => '\d+'])]
    public function show(Commande $commande): Response
    {
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/show.html.twig', [
            'order' => $commande,
        ]);
    }

    #[Route('/valider', name: 'order_checkout', methods: ['GET', 'POST'])]
    public function checkout(
        Request $request,
        CartService $cartService,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $items = $cartService->getDetailedCart();
        if ([] === $items) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        if ($request->isMethod('POST')) {
            $address = trim((string) $request->request->get('address'));
            if ($address === '') {
                $this->addFlash('danger', 'Veuillez renseigner une adresse de livraison.');
                return $this->redirectToRoute('order_checkout');
            }

            $order = new Commande();
            $order->setUser($this->getUser());
            $order->setAdresseLivraison($address);
            $order->setStatut(StatutCommande::EN_ATTENTE);
            $order->setStripePaymentId(null);

            $total = 0.0;
            foreach ($items as $item) {
                $product = $item['product'];
                $qty = $item['quantity'];

                if ($product->getStock() < $qty) {
                    $this->addFlash('danger', sprintf('Stock insuffisant pour %s.', $product->getNom()));
                    return $this->redirectToRoute('cart_index');
                }

                $line = new LigneCommande();
                $line->setProduit($product);
                $line->setQuantite($qty);
                $line->setPrixUnitaire((string) $product->getPrix());
                $line->setSousTotale(number_format($item['line_total'], 2, '.', ''));
                $line->setCommande($order);
                $entityManager->persist($line);

                $total += $item['line_total'];
            }

            $order->setTotal(number_format($total, 2, '.', ''));
            $entityManager->persist($order);
            $entityManager->flush();

            $stripeSessionUrl = $this->createStripeCheckoutSession($order, $items);
            if ($stripeSessionUrl === null) {
                $this->addFlash('danger', 'Paiement indisponible. Vérifiez la configuration Stripe.');
                return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
            }

            return $this->redirect($stripeSessionUrl);
        }

        return $this->render('order/checkout.html.twig', [
            'items' => $items,
            'total' => $cartService->getTotalAmount(),
        ]);
    }

    #[Route('/success/{id}', name: 'order_success', requirements: ['id' => '\d+'])]
    public function success(
        Commande $order,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        CartService $cartService
    ): Response {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $sessionId = (string) $request->query->get('session_id', '');
        if ($sessionId === '') {
            $this->addFlash('danger', 'Session de paiement invalide.');
            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        $client = HttpClient::create();
        $response = $client->request('GET', sprintf('https://api.stripe.com/v1/checkout/sessions/%s', $sessionId), [
            'auth_basic' => [$this->stripeSecretKey, ''],
        ]);
        $data = $response->toArray(false);

        if (($data['payment_status'] ?? '') !== 'paid') {
            $this->addFlash('warning', 'Paiement non confirmé.');
            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        if ($order->getStatut() !== StatutCommande::PAYEE) {
            foreach ($order->getLigneCommandes() as $line) {
                $product = $line->getProduit();
                if ($product->getStock() < $line->getQuantite()) {
                    $this->addFlash('danger', sprintf('Stock insuffisant pour %s.', $product->getNom()));
                    return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
                }
                $product->setStock($product->getStock() - $line->getQuantite());
            }

            $order->setStatut(StatutCommande::PAYEE);
            $order->setStripePaymentId((string) ($data['payment_intent'] ?? $sessionId));
            $entityManager->flush();

            $mailer->send(
                (new TemplatedEmail())
                    ->from(new Address('amaniihomri@gmail.com', 'SymPet'))
                    ->to((string) $this->getUser()->getUserIdentifier())
                    ->subject(sprintf('Commande #%d confirmée', $order->getId()))
                    ->htmlTemplate('emails/order_confirmation.html.twig')
                    ->context(['order' => $order])
            );
        }

        $cartService->clear();
        $this->addFlash('success', 'Paiement confirmé. Votre commande est validée.');
        return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
    }

    private function createStripeCheckoutSession(Commande $order, array $items): ?string
    {
        if ($this->stripeSecretKey === '' || str_contains($this->stripeSecretKey, 'sk_test_xxx')) {
            return null;
        }

        $payload = [
            'mode' => 'payment',
            'success_url' => $this->generateUrl('order_success', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('order_checkout', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata[order_id]' => (string) $order->getId(),
        ];

        foreach ($items as $index => $item) {
            $payload[sprintf('line_items[%d][quantity]', $index)] = (string) $item['quantity'];
            $payload[sprintf('line_items[%d][price_data][currency]', $index)] = 'eur';
            $payload[sprintf('line_items[%d][price_data][unit_amount]', $index)] = (string) ((int) round(((float) $item['product']->getPrix()) * 100));
            $payload[sprintf('line_items[%d][price_data][product_data][name]', $index)] = $item['product']->getNom();
        }

        $client = HttpClient::create();
        $response = $client->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
            'auth_basic' => [$this->stripeSecretKey, ''],
            'body' => $payload,
        ]);

        if ($response->getStatusCode() >= 400) {
            return null;
        }

        $data = $response->toArray(false);
        return $data['url'] ?? null;
    }
}

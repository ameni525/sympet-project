<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot/message', name: 'chatbot_message', methods: ['POST'])]
    public function message(Request $request): JsonResponse
    {
        $data     = json_decode($request->getContent(), true);
        $messages = $data['messages'] ?? [];

        if (empty($messages)) {
            return $this->json(['error' => 'No messages provided'], 400);
        }

        $apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';

        if (empty($apiKey)) {
            return $this->json(['error' => 'API key not configured'], 500);
        }

        $systemPrompt = "Tu es PetBot 🐾, l'assistant virtuel de SymPet, une boutique e-commerce "
            . "spécialisée dans les produits pour animaux de compagnie (nourriture, accessoires, hygiène). "
            . "SymPet vend de la nourriture premium pour chiens et chats, des accessoires, des jouets et des produits de soin. "
            . "Tu aides les clients avec leurs questions sur les produits, les conseils pour leurs animaux, "
            . "les commandes, la livraison et le paiement sécurisé par Stripe. "
            . "Réponds toujours en français, sois chaleureux et utilise des emojis avec modération. "
            . "Tes réponses doivent être concises (2-4 phrases maximum).";

        // Construire les messages pour OpenRouter (format OpenAI)
        $apiMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($messages as $msg) {
            $apiMessages[] = [
                'role'    => $msg['role'], // 'user' ou 'assistant'
                'content' => $msg['content'],
            ];
        }

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: http://localhost:8000',
                'X-Title: SymPet Chatbot',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => 'mistralai/mistral-7b-instruct:free',
                'messages'   => $apiMessages,
                'max_tokens' => 400,
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return $this->json(['error' => 'API error: ' . $response], 500);
        }

        $result = json_decode($response, true);
        $reply  = $result['choices'][0]['message']['content']
                  ?? 'Désolé, je n\'ai pas pu répondre. Réessayez.';

        return $this->json(['reply' => $reply]);
    }
}

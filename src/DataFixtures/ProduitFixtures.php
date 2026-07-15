<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture implements DependentFixtureInterface
{
    private const PRODUITS = [

        // ===================== DOG FOOD =====================
        [
            'nom'   => 'Croquettes Premium Chien Adulte Poulet',
            'desc'  => 'Alimentation complète au poulet pour chiens adultes. Favorise énergie, digestion et masse musculaire.',
            'prix'  => 59.90,
            'stock' => 50,
            'image' => 'https://images.pexels.com/photos/1108099/pexels-photo-1108099.jpeg',
            'cat'   => 'Nourriture chien',
        ],
        [
            'nom'   => 'Croquettes Chien Grande Race Bœuf',
            'desc'  => 'Formule spéciale grandes races riche en protéines de bœuf pour articulations et vitalité.',
            'prix'  => 74.90,
            'stock' => 40,
            'image' => 'https://images.pexels.com/photos/4587995/pexels-photo-4587995.jpeg',
            'cat'   => 'Nourriture chien',
        ],
        [
            'nom'   => 'Snack Naturel Poulet pour Chien',
            'desc'  => 'Friandises naturelles au poulet idéales pour dressage et récompense.',
            'prix'  => 9.90,
            'stock' => 120,
            'image' => 'https://images.pexels.com/photos/6568943/pexels-photo-6568943.jpeg',
            'cat'   => 'Nourriture chien',
        ],

        // ===================== CAT FOOD =====================
        [
            'nom'   => 'Croquettes Chat Adulte Saumon',
            'desc'  => 'Alimentation équilibrée au saumon pour chats adultes. Favorise un pelage brillant.',
            'prix'  => 39.90,
            'stock' => 70,
            'image' => 'https://images.pexels.com/photos/45201/kitty-cat-kitten-pet-45201.jpeg',
            'cat'   => 'Nourriture chat',
        ],
        [
            'nom'   => 'Pâtée Chat Poulet en Sauce',
            'desc'  => 'Repas humide savoureux au poulet pour chats exigeants.',
            'prix'  => 14.50,
            'stock' => 150,
            'image' => 'https://images.pexels.com/photos/416160/pexels-photo-416160.jpeg',
            'cat'   => 'Nourriture chat',
        ],
        [
            'nom'   => 'Croquettes Chaton Croissance',
            'desc'  => 'Formule riche pour chatons assurant une croissance saine et équilibrée.',
            'prix'  => 29.90,
            'stock' => 60,
            'image' => 'https://images.pexels.com/photos/127028/pexels-photo-127028.jpeg',
            'cat'   => 'Nourriture chat',
        ],

        // ===================== DOG ACCESSORIES =====================
        [
            'nom'   => 'Laisse Rétractable Confort 5m',
            'desc'  => 'Laisse solide avec frein automatique pour promenades sécurisées.',
            'prix'  => 19.90,
            'stock' => 80,
            'image' => 'https://images.pexels.com/photos/2607544/pexels-photo-2607544.jpeg',
            'cat'   => 'Accessoires chien',
        ],
        [
            'nom'   => 'Harnais Anti-Traction Réglable',
            'desc'  => 'Harnais confortable réduisant la traction lors des promenades.',
            'prix'  => 24.90,
            'stock' => 65,
            'image' => 'https://images.pexels.com/photos/2607543/pexels-photo-2607543.jpeg',
            'cat'   => 'Accessoires chien',
        ],
        [
            'nom'   => 'Jouet Résistant Chien Caoutchouc',
            'desc'  => 'Jouet durable pour mastication et stimulation mentale.',
            'prix'  => 12.90,
            'stock' => 100,
            'image' => 'https://images.pexels.com/photos/1904105/pexels-photo-1904105.jpeg',
            'cat'   => 'Accessoires chien',
        ],

        // ===================== CAT ACCESSORIES =====================
        [
            'nom'   => 'Arbre à Chat Multi-Niveaux',
            'desc'  => 'Structure complète pour jeu, repos et griffades.',
            'prix'  => 69.90,
            'stock' => 25,
            'image' => 'https://images.pexels.com/photos/416195/pexels-photo-416195.jpeg',
            'cat'   => 'Accessoires chat',
        ],
        [
            'nom'   => 'Fontaine Eau Automatique Chat',
            'desc'  => 'Fontaine filtrante pour eau fraîche et propre en continu.',
            'prix'  => 34.90,
            'stock' => 40,
            'image' => 'https://images.pexels.com/photos/96938/pexels-photo-96938.jpeg',
            'cat'   => 'Accessoires chat',
        ],
        [
            'nom'   => 'Jouet Plume Interactif Chat',
            'desc'  => 'Jouet stimulant l’instinct de chasse et l’activité du chat.',
            'prix'  => 7.90,
            'stock' => 200,
            'image' => 'https://images.pexels.com/photos/45230/pexels-photo-45230.jpeg',
            'cat'   => 'Accessoires chat',
        ],

        // ===================== CARE =====================
        [
            'nom'   => 'Shampooing Doux Animaux Aloe Vera',
            'desc'  => 'Shampooing naturel adapté aux peaux sensibles.',
            'prix'  => 11.90,
            'stock' => 90,
            'image' => 'https://images.pexels.com/photos/6816865/pexels-photo-6816865.jpeg',
            'cat'   => 'Soins et hygiène',
        ],
        [
            'nom'   => 'Brosse de Toilettage Double Face',
            'desc'  => 'Brosse professionnelle pour entretien du pelage.',
            'prix'  => 9.90,
            'stock' => 75,
            'image' => 'https://images.pexels.com/photos/6816866/pexels-photo-6816866.jpeg',
            'cat'   => 'Soins et hygiène',
        ],
        [
            'nom'   => 'Lingettes Nettoyantes Animaux',
            'desc'  => 'Lingettes pratiques pour hygiène rapide quotidienne.',
            'prix'  => 6.90,
            'stock' => 180,
            'image' => 'https://images.pexels.com/photos/6816867/pexels-photo-6816867.jpeg',
            'cat'   => 'Soins et hygiène',
        ],
    ];

    public function load(ObjectManager $em): void
    {
        $categoriesRepo = $em->getRepository(Categorie::class);

        $categoriesMap = [];
        foreach ($categoriesRepo->findAll() as $cat) {
            $categoriesMap[$cat->getNom()] = $cat;
        }

        foreach (self::PRODUITS as $data) {
            $produit = new Produit();
            $produit->setNom($data['nom'])
                ->setDescription($data['desc'])
                ->setPrix($data['prix'])
                ->setStock($data['stock'])
                ->setImage($data['image'])
                ->setCreatedAt(new \DateTimeImmutable())
                ->setCategorie($categoriesMap[$data['cat']]);

            $em->persist($produit);
        }

        $em->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategorieFixtures::class,
        ];
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public const CATEGORIES = [
        'Nourriture chien'  => 'Croquettes, pâtées et friandises pour chiens de toutes tailles.',
        'Nourriture chat'   => 'Aliments secs et humides adaptés aux besoins nutritionnels des chats.',
        'Accessoires chien' => 'Laisses, colliers, jouets et accessoires pour le confort de votre chien.',
        'Accessoires chat'  => 'Griffoirs, arbres à chat, jouets et litières pour le bien-être de votre chat.',
        'Soins et hygiène'  => 'Shampooings, brosses et produits de soin pour tous les animaux.',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $nom => $description) {
            $cat = new Categorie();
            $cat->setNom($nom);
            $cat->setDescription($description);

            $manager->persist($cat);
            // On stocke la référence avec un préfixe "cat_" pour l'utiliser dans ProduitFixtures
            $this->addReference('cat_' . $nom, $cat);
        }

        $manager->flush();
    }
}
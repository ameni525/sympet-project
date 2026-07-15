 # TODO : Mise à jour des entités pour matcher le schéma exact - TERMINÉ !

## Étapes complétées : 7/7

- [x] 1. Éditer User.php (isVerified, nom, prenom, createdAt; $no → $commandes; +avis)
- [x] 2. Éditer Produit.php (createdAt, prix decimal; $no → $ligneCommandes; +avis)
- [x] 3. Éditer Commande.php (adresseLivraison, stripePaymentId, total decimal, statut enum, fix user; $no → $ligneCommandes)
- [x] 4. Éditer LigneCommande.php (prixUnitaire, sous_totale → decimal(10,2); inversedBy)
- [x] 5. Ajouter OneToMany Avis dans User et Produit
- [x] 6. Créer Avis.php (bonus)
- [x] 7. **Migrations : cd sympet-project && bin/console make:migration && bin/console doctrine:migrations:migrate**

**✅ Toutes entités 100% conformes au schéma fourni (champs, types, contraintes, relations, bonus Avis).**

**Vérification : bin/console doctrine:schema:validate**

**Repository Avis : bin/console make:repository App Avis** (optionnel).

<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TechnicienFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Palette de couleurs fixes
        $colors = ['#FF5733', '#33C3FF', '#75FF33', '#FFC300', '#DA33FF', '#33FFF3'];

        // 🔹 Récupération du rôle Technicien
        $roleTech = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_TECHNICIEN']);

        if (!$roleTech) {
            // sécurité : évite une erreur si RoleFixtures n’a pas été chargé
            throw new \RuntimeException('Le rôle ROLE_TECHNICIEN est introuvable. Vérifie que RoleFixtures est bien chargé.');
        }

        // Boucle pour créer 5 techniciens si non existants
        for ($i = 1; $i <= 5; $i++) {
            $identifiant = "tech{$i}@example.com";

            // Vérifie si le technicien existe déjà
            $existingTech = $manager->getRepository(User::class)
                ->findOneBy(['identifiant' => $identifiant]);

            if ($existingTech) {
                continue; // déjà présent → on passe au suivant
            }

            // Création du technicien
            $technicien = new User();
            $technicien->setNom("Technicien {$i}");
            $technicien->setIdentifiant($identifiant);
            $technicien->setPassword($this->hasher->hashPassword($technicien, 'password123'));
            $technicien->addRole($roleTech);
            $technicien->setCouleur($colors[($i - 1) % count($colors)]);

            $manager->persist($technicien);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }
}

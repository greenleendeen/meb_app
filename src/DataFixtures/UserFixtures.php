<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\User;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // 🔹 Récupère les rôles existants
        $roleSuperAdmin = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_SUPER_ADMIN']);
        $roleAdmin      = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_ADMIN']);
        $roleTech       = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_TECHNICIEN']);

        //  Super Admin
        $this->createUserIfNotExists(
            $manager,
            'Super Admin',
            'superadmin@example.com',
            'superpassword',
            $roleSuperAdmin
        );

        //  Admin
        $this->createUserIfNotExists(
            $manager,
            'Admin Test',
            'admin@example.com',
            'adminpassword',
            $roleAdmin
        );

        //  Technicien Test
        $this->createUserIfNotExists(
            $manager,
            'Technicien Test',
            'tech@example.com',
            'techpassword',
            $roleTech
        );

        $manager->flush();
    }

    /**
     * Petite fonction utilitaire pour éviter les répétitions
     */
    private function createUserIfNotExists(
        ObjectManager $manager,
        string $nom,
        string $identifiant,
        string $plainPassword,
        ?Role $role
    ): void {
        $repo = $manager->getRepository(User::class);
        $existingUser = $repo->findOneBy(['identifiant' => $identifiant]);

        if ($existingUser) {
            return; // utilisateur déjà existant, on saute
        }

        $user = new User();
        $user->setNom($nom);
        $user->setIdentifiant($identifiant);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        if ($role) {
            $user->addRole($role);
        }

        $manager->persist($user);
    }

    /**
     * Cette méthode permet de charger RoleFixtures avant UserFixtures
     */
    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }
}

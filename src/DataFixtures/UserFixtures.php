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
        // Création du Super Admin
        $superAdmin = new User();
        $superAdmin->setNom('Super Admin');
        $superAdmin->setIdentifiant('superadmin@example.com');
        $superAdmin->setPassword(
            $this->passwordHasher->hashPassword($superAdmin, 'superpassword')
        );

        // Récupération du rôle via la référence créée dans RoleFixtures

        $roleSuperAdmin = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_SUPER_ADMIN']);
        $superAdmin->addRole($roleSuperAdmin);

        $manager->persist($superAdmin);

        //  Admin standard
        $admin = new User();
        $admin->setNom('Admin Test');
        $admin->setIdentifiant('admin@example.com');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'adminpassword')
        );

        /**  $roleAdmin */
        $roleAdmin = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_ADMIN']);
        $admin->addRole($roleAdmin);

        $manager->persist($admin);

        //  Technicien
        $tech = new User();
        $tech->setNom('Technicien Test');
        $tech->setIdentifiant('tech@example.com');
        $tech->setPassword(
            $this->passwordHasher->hashPassword($tech, 'techpassword')
        );
        $roleTech = $manager->getRepository(Role::class)->findOneBy(['nom' => 'ROLE_TECHNICIEN']);
        $tech->addRole($roleTech);
        $manager->persist($tech);

        $manager->flush();
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

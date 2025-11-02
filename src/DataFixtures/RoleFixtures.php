<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = [
            'ROLE_SUPER_ADMIN' => 'Super-Admin',
            'ROLE_ADMIN'       => 'Administrateur',
            'ROLE_OPERATEUR'   => 'Opérateur',
            'ROLE_TECHNICIEN'  => 'Technicien',
            'ROLE_CLIENT'      => 'Client',
        ];

        foreach ($roles as $roleName => $roleLabel) {
            //  Vérifie si le rôle existe déjà en base
            $existingRole = $manager->getRepository(Role::class)->findOneBy(['nom' => $roleName]);

            if (!$existingRole) {
                $role = new Role();
                $role->setNom($roleName);
                $manager->persist($role);
                $this->addReference($roleName, $role);
            } else {
                //  Réutilise la référence si déjà existant
                $this->addReference($roleName, $existingRole);
            }
        }

        $manager->flush();
    }
}

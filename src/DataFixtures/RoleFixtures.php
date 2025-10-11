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
            'ROLE_ADMIN' => 'Administrateur',
            'ROLE_OPERATEUR' => 'Opérateur',
            'ROLE_TECHNICIEN' => 'Technicien',
            'ROLE_CLIENT' => 'Client',
        ];
        
foreach (['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_OPERATEUR', 'ROLE_TECHNICIEN', 'ROLE_CLIENT'] as $r) {
    $role = new Role();
    $role->setNom($r);
    $manager->persist($role);

    $this->addReference($r, $role);
}

        $manager->flush();
    }
}
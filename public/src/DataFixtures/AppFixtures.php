<?php

namespace App\DataFixtures;

use App\Entity\Conge;
use App\Entity\Groupe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $password = password_hash('password', PASSWORD_BCRYPT);

        $groupes = [];
        foreach (['Administrateurs', 'Employés', 'Managers'] as $nom) {
            $groupe = new Groupe();
            $groupe->setNom($nom);
            $manager->persist($groupe);
            $groupes[] = $groupe;
        }

        $typesDeConges = [
            'Congé annuel',
            'Congé maladie',
            'Congé sans solde',
            'Congé maternité/paternité',
            'RTT',
            'Congé sabbatique',
        ];
        $statuts = ['En attente', 'Validé', 'Refusé'];

        for ($index = 0; $index < 10; ++$index) {
            $user = new User();
            $user
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setEmail($faker->unique()->safeEmail())
                ->setPassword($password);

            $user->addGroupe($groupes[$index % count($groupes)]);
            if ($index % 2 === 0) {
                $user->addGroupe($groupes[2]);
            }

            $manager->persist($user);

            for ($leaveIndex = 0; $leaveIndex < 3; ++$leaveIndex) {
                $dateDebut = $faker->dateTimeBetween('-6 months', '+2 months');
                $dateFin = (clone $dateDebut)->modify(sprintf('+%d days', $faker->numberBetween(1, 10)));

                $conge = new Conge();
                $conge
                    ->setType($typesDeConges[($index + $leaveIndex) % count($typesDeConges)])
                    ->setDateDebut($dateDebut)
                    ->setDateFin($dateFin)
                    ->setStatut($statuts[($index + $leaveIndex) % count($statuts)])
                    ->setUser($user);

                $manager->persist($conge);
            }
        }

        $manager->flush();
    }
}

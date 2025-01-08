<?php

namespace App\DataFixtures;

use App\Entity\Groupe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\CongeFactory;
use App\Factory\UserFactory;
use Faker\Factory;

class AppFixtures extends Fixture
{
    /**
     * Charge des données fictives pour la gestion des congés et des groupes.
     */
    public function load(ObjectManager $manager): void
    {
        // Crée une instance de Faker pour générer des données fictives
        $faker = Factory::create('fr_FR');

        // Crée des utilisateurs fictifs
        $users = UserFactory::new()->createMany(10, function () use ($faker) {
            return [
                'nom' => $faker->lastName,
                'prenom' => $faker->firstName,
                'email' => $faker->unique()->safeEmail,
                'password' => password_hash('password', PASSWORD_BCRYPT),
            ];
        });

        // Liste des types de congés disponibles
        $typesDeConges = [
            'Congé annuel',
            'Congé maladie',
            'Congé sans solde',
            'Congé maternité/paternité',
            'RTT',
            'Congé sabbatique'
        ];

        // Associe chaque utilisateur à des congés fictifs
        foreach ($users as $user) {
            $totalDays = 0; // Total des jours de congé pour cet utilisateur

            foreach ($typesDeConges as $typeDeConge) {
                $days = rand(1, 30); // Génère un nombre aléatoire de jours de congé

                // Vérifie que le total des jours ne dépasse pas 30
                if ($totalDays + $days > 30) {
                    break; // Arrête d'ajouter des congés si le total dépasse 30 jours
                }

                // Génère une date de début et calcule la date de fin
                $dateDebut = new \DateTime(sprintf('-%d days', rand(1, 30)));
                $dateFin = (clone $dateDebut)->modify(sprintf('+%d days', $days));

                // Crée un congé fictif
                CongeFactory::new()->create([
                    'type' => $typeDeConge,
                    'dateDebut' => $dateDebut,
                    'dateFin' => $dateFin,
                    'statut' => rand(0, 1) ? 'approuvé' : 'en attente',
                    'user' => $user
                ]);

                $totalDays += $days; // Ajoute les jours de congé au total
            }
        }

        // Liste des groupes fictifs
        $groupes = [
            'Administrateurs',
            'Employés',
            'Managers'
        ];

        $groupeEntities = [];
        foreach ($groupes as $groupeNom) {
            $groupe = new Groupe();
            $groupe->setNom($groupeNom);
            $manager->persist($groupe); // Persiste chaque groupe
            $groupeEntities[] = $groupe; // Stocke les groupes créés
        }

        // Associe chaque utilisateur à des groupes fictifs
        foreach ($users as $user) {
            // Ajoute un ou plusieurs groupes aléatoires à l'utilisateur
            $randomGroups = array_rand($groupeEntities, rand(1, count($groupeEntities)));

            // Assure que la variable est toujours un tableau
            if (!is_array($randomGroups)) {
                $randomGroups = [$randomGroups];
            }

            foreach ($randomGroups as $groupIndex) {
                $user->addGroupe($groupeEntities[$groupIndex]);
            }
        }

        // Flush les entités persistées dans la base de données
        $manager->flush();
    }
}

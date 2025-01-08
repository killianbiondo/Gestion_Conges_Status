<?php

namespace App\Factory;

use App\Entity\Conge;
use App\Repository\CongeRepository;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Conge>
 *
 * @method        Conge|Proxy create(array|callable $attributes = [])
 * @method static Conge|Proxy createOne(array $attributes = [])
 * @method static Conge|Proxy find(object|array|mixed $criteria)
 * @method static Conge|Proxy findOrCreate(array $attributes)
 * @method static Conge|Proxy first(string $sortedField = 'id')
 * @method static Conge|Proxy last(string $sortedField = 'id')
 * @method static Conge|Proxy random(array $attributes = [])
 * @method static Conge|Proxy randomOrCreate(array $attributes = [])
 * @method static CongeRepository|ProxyRepositoryDecorator repository()
 * @method static Conge[]|Proxy[] all()
 * @method static Conge[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Conge[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Conge[]|Proxy[] findBy(array $attributes)
 * @method static Conge[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Conge[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class CongeFactory extends PersistentProxyObjectFactory{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Conge::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'type' => self::faker()->text(255),
            'date_debut' => self::faker()->dateTime(),
            'date_fin' => self::faker()->dateTime(),
            'statut' => self::faker()->text(255),
            'user' => UserFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Conge $conge): void {})
        ;
    }
}

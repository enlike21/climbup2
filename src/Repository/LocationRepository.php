<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\ClimbingRoute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function getAllLocationsWithRouteCount(): array
    {
        // Corregir la consulta utilizando la relación unidireccional
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('App\Entity\ClimbingRoute', 'r', 'WITH', 'r.location = l.id') // Asegúrate de que la relación se hace a través de la clase ClimbingRoute
            ->addSelect('COUNT(r.id) AS routeCount')
            ->addSelect('l.id', 'l.name', 'l.latitude', 'l.longitude')
            ->groupBy('l.id');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return array_map(function ($row) {
            return [
                'id' => $row[0]->getId(),
                'name' => $row[0]->getName(),
                'latitude' => $row[0]->getLatitude(),
                'longitude' => $row[0]->getLongitude(),
                'routeCount' => (int) $row['routeCount'],
                'predominantType' => $this->getPredominantTypeForLocation($row[0]),
            ];
        }, $results);
    }

    public function getPredominantTypeForLocation(Location $location): ?string
    {
        // Obtener el tipo predominante de las rutas para esta localización
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('r.routeType, COUNT(r.id) as count') // Cambié 'type' a 'routeType'
        ->from('App\Entity\ClimbingRoute', 'r')
            ->where('r.location = :location')
            ->setParameter('location', $location)
            ->groupBy('r.routeType') // Cambié 'r.type' a 'r.routeType'
            ->orderBy('count', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ? $result['routeType'] : null;
    }
}

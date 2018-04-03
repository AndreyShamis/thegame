<?php

namespace App\Repository;

use App\Entity\TheGameUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method TheGameUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TheGameUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TheGameUser[]    findAll()
 * @method TheGameUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TheGameUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TheGameUser::class);
    }

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($username): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $criteria
     * @return TheGameUser
     */
    public function create(array $criteria): TheGameUser
    {
        $criteria['username'] = strtolower($criteria['username']);

        $entity = $this->findOneBy(array('username' => $criteria['username']));

        if (null === $entity) {
            $entity = new TheGameUser();
            $entity->setUsername($criteria['username']);
            $entity->setEmail($criteria['email']);
            $entity->setFullName($criteria['fullName']);
            $entity->setLastName($criteria['lastName']);
            $entity->setFirstName($criteria['firstName']);
            $entity->setAnotherId($criteria['anotherId']);
            $entity->setMobile($criteria['mobile']);
            $entity->setIsLdapUser($criteria['ldapUser']);
            $entity->setPassword($criteria['dummyPassword']);
            $this->_em->persist($entity);
            $this->_em->flush($entity);
        }

        return $entity;
    }

//    /**
//     * @return TheGameUser[] Returns an array of TheGameUser objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TheGameUser
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

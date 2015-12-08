<?php

namespace LastCall\Crawler\Repository;

use Doctrine\ORM\EntityRepository;

class ExistenceCheckingRepository extends EntityRepository
{

    public function has($criteria)
    {
        $persister = $this->_em->getUnitOfWork()
          ->getEntityPersister($this->_entityName);

        return $persister->count($criteria) > 0;
    }

    public function count($criteria)
    {
        $persister = $this->_em->getUnitOfWork()
          ->getEntityPersister($this->_entityName);

        return $persister->count($criteria);
    }
}
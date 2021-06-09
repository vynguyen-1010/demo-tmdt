<?php
 namespace MailPoetVendor\Doctrine\ORM; if (!defined('ABSPATH')) exit; use MailPoetVendor\Doctrine\Common\Collections\AbstractLazyCollection; use MailPoetVendor\Doctrine\Common\Collections\Collection; use MailPoetVendor\Doctrine\Common\Collections\ArrayCollection; use MailPoetVendor\Doctrine\Common\Collections\Selectable; use MailPoetVendor\Doctrine\Common\Collections\Criteria; use MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata; use function get_class; final class PersistentCollection extends \MailPoetVendor\Doctrine\Common\Collections\AbstractLazyCollection implements \MailPoetVendor\Doctrine\Common\Collections\Selectable { private $snapshot = []; private $owner; private $association; private $em; private $backRefFieldName; private $typeClass; private $isDirty = \false; public function __construct(\MailPoetVendor\Doctrine\ORM\EntityManagerInterface $em, $class, \MailPoetVendor\Doctrine\Common\Collections\Collection $collection) { $this->collection = $collection; $this->em = $em; $this->typeClass = $class; $this->initialized = \true; } public function setOwner($entity, array $assoc) { $this->owner = $entity; $this->association = $assoc; $this->backRefFieldName = $assoc['inversedBy'] ?: $assoc['mappedBy']; } public function getOwner() { return $this->owner; } public function getTypeClass() { return $this->typeClass; } public function hydrateAdd($element) { $this->collection->add($element); if ($this->backRefFieldName && $this->association['type'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::ONE_TO_MANY) { $this->typeClass->reflFields[$this->backRefFieldName]->setValue($element, $this->owner); $this->em->getUnitOfWork()->setOriginalEntityProperty(\spl_object_hash($element), $this->backRefFieldName, $this->owner); } } public function hydrateSet($key, $element) { $this->collection->set($key, $element); if ($this->backRefFieldName && $this->association['type'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::ONE_TO_MANY) { $this->typeClass->reflFields[$this->backRefFieldName]->setValue($element, $this->owner); } } public function initialize() { if ($this->initialized || !$this->association) { return; } $this->doInitialize(); $this->initialized = \true; } public function takeSnapshot() { $this->snapshot = $this->collection->toArray(); $this->isDirty = \false; } public function getSnapshot() { return $this->snapshot; } public function getDeleteDiff() { return \array_udiff_assoc($this->snapshot, $this->collection->toArray(), function ($a, $b) { return $a === $b ? 0 : 1; }); } public function getInsertDiff() { return \array_udiff_assoc($this->collection->toArray(), $this->snapshot, function ($a, $b) { return $a === $b ? 0 : 1; }); } public function getMapping() { return $this->association; } private function changed() { if ($this->isDirty) { return; } $this->isDirty = \true; if ($this->association !== null && $this->association['isOwningSide'] && $this->association['type'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::MANY_TO_MANY && $this->owner && $this->em->getClassMetadata(\get_class($this->owner))->isChangeTrackingNotify()) { $this->em->getUnitOfWork()->scheduleForDirtyCheck($this->owner); } } public function isDirty() { return $this->isDirty; } public function setDirty($dirty) { $this->isDirty = $dirty; } public function setInitialized($bool) { $this->initialized = $bool; } public function remove($key) { $removed = parent::remove($key); if (!$removed) { return $removed; } $this->changed(); if ($this->association !== null && $this->association['type'] & \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::TO_MANY && $this->owner && $this->association['orphanRemoval']) { $this->em->getUnitOfWork()->scheduleOrphanRemoval($removed); } return $removed; } public function removeElement($element) { $removed = parent::removeElement($element); if (!$removed) { return $removed; } $this->changed(); if ($this->association !== null && $this->association['type'] & \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::TO_MANY && $this->owner && $this->association['orphanRemoval']) { $this->em->getUnitOfWork()->scheduleOrphanRemoval($element); } return $removed; } public function containsKey($key) { if (!$this->initialized && $this->association['fetch'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY && isset($this->association['indexBy'])) { $persister = $this->em->getUnitOfWork()->getCollectionPersister($this->association); return $this->collection->containsKey($key) || $persister->containsKey($this, $key); } return parent::containsKey($key); } public function contains($element) { if (!$this->initialized && $this->association['fetch'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY) { $persister = $this->em->getUnitOfWork()->getCollectionPersister($this->association); return $this->collection->contains($element) || $persister->contains($this, $element); } return parent::contains($element); } public function get($key) { if (!$this->initialized && $this->association['fetch'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY && isset($this->association['indexBy'])) { if (!$this->typeClass->isIdentifierComposite && $this->typeClass->isIdentifier($this->association['indexBy'])) { return $this->em->find($this->typeClass->name, $key); } return $this->em->getUnitOfWork()->getCollectionPersister($this->association)->get($this, $key); } return parent::get($key); } public function count() { if (!$this->initialized && $this->association !== null && $this->association['fetch'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY) { $persister = $this->em->getUnitOfWork()->getCollectionPersister($this->association); return $persister->count($this) + ($this->isDirty ? $this->collection->count() : 0); } return parent::count(); } public function set($key, $value) { parent::set($key, $value); $this->changed(); if (\is_object($value) && $this->em) { $this->em->getUnitOfWork()->cancelOrphanRemoval($value); } } public function add($value) { $this->collection->add($value); $this->changed(); if (\is_object($value) && $this->em) { $this->em->getUnitOfWork()->cancelOrphanRemoval($value); } return \true; } public function offsetExists($offset) { return $this->containsKey($offset); } public function offsetGet($offset) { return $this->get($offset); } public function offsetSet($offset, $value) { if (!isset($offset)) { $this->add($value); return; } $this->set($offset, $value); } public function offsetUnset($offset) { return $this->remove($offset); } public function isEmpty() { return $this->collection->isEmpty() && $this->count() === 0; } public function clear() { if ($this->initialized && $this->isEmpty()) { $this->collection->clear(); return; } $uow = $this->em->getUnitOfWork(); if ($this->association['type'] & \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::TO_MANY && $this->association['orphanRemoval'] && $this->owner) { $this->initialize(); foreach ($this->collection as $element) { $uow->scheduleOrphanRemoval($element); } } $this->collection->clear(); $this->initialized = \true; if ($this->association['isOwningSide'] && $this->owner) { $this->changed(); $uow->scheduleCollectionDeletion($this); $this->takeSnapshot(); } } public function __sleep() : array { return ['collection', 'initialized']; } public function slice($offset, $length = null) { if (!$this->initialized && !$this->isDirty && $this->association['fetch'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY) { $persister = $this->em->getUnitOfWork()->getCollectionPersister($this->association); return $persister->slice($this, $offset, $length); } return parent::slice($offset, $length); } public function __clone() { if (\is_object($this->collection)) { $this->collection = clone $this->collection; } $this->initialize(); $this->owner = null; $this->snapshot = []; $this->changed(); } public function matching(\MailPoetVendor\Doctrine\Common\Collections\Criteria $criteria) { if ($this->isDirty) { $this->initialize(); } if ($this->initialized) { return $this->collection->matching($criteria); } if ($this->association['type'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::MANY_TO_MANY) { $persister = $this->em->getUnitOfWork()->getCollectionPersister($this->association); return new \MailPoetVendor\Doctrine\Common\Collections\ArrayCollection($persister->loadCriteria($this, $criteria)); } $builder = \MailPoetVendor\Doctrine\Common\Collections\Criteria::expr(); $ownerExpression = $builder->eq($this->backRefFieldName, $this->owner); $expression = $criteria->getWhereExpression(); $expression = $expression ? $builder->andX($expression, $ownerExpression) : $ownerExpression; $criteria = clone $criteria; $criteria->where($expression); $criteria->orderBy($criteria->getOrderings() ?: $this->association['orderBy'] ?? []); $persister = $this->em->getUnitOfWork()->getEntityPersister($this->association['targetEntity']); return $this->association['fetch'] === \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata::FETCH_EXTRA_LAZY ? new \MailPoetVendor\Doctrine\ORM\LazyCriteriaCollection($persister, $criteria) : new \MailPoetVendor\Doctrine\Common\Collections\ArrayCollection($persister->loadCriteria($criteria)); } public function unwrap() { return $this->collection; } protected function doInitialize() { $newlyAddedDirtyObjects = []; if ($this->isDirty) { $newlyAddedDirtyObjects = $this->collection->toArray(); } $this->collection->clear(); $this->em->getUnitOfWork()->loadCollection($this); $this->takeSnapshot(); if ($newlyAddedDirtyObjects) { $this->restoreNewObjectsInDirtyCollection($newlyAddedDirtyObjects); } } private function restoreNewObjectsInDirtyCollection(array $newObjects) : void { $loadedObjects = $this->collection->toArray(); $newObjectsByOid = \array_combine(\array_map('spl_object_hash', $newObjects), $newObjects); $loadedObjectsByOid = \array_combine(\array_map('spl_object_hash', $loadedObjects), $loadedObjects); $newObjectsThatWereNotLoaded = \array_diff_key($newObjectsByOid, $loadedObjectsByOid); if ($newObjectsThatWereNotLoaded) { \array_walk($newObjectsThatWereNotLoaded, [$this->collection, 'add']); $this->isDirty = \true; } } } 
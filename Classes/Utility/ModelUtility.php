<?php

namespace OrangeHive\Simplyment\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ModelUtility
{

    public static function getModel(string $fqcn, int|string $uid): ?object
    {
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManagerInterface::class);

        $query = $persistenceManager->createQueryForType($fqcn);
        $settings = $query->getQuerySettings();
        $settings->setRespectStoragePage(false);
        $settings->setRespectSysLanguage(false);

        $query->matching($query->equals('uid', $uid));

        return $query->execute()->getFirst();
    }

}
<?php

namespace OrangeHive\Simplyment\Hook;

use OrangeHive\Simplyment\Cache\CustomCache;
use OrangeHive\Simplyment\Registry\BackendLayoutRegistry;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;


class BackendLayoutDataProvider implements DataProviderInterface
{

    public function addBackendLayouts(DataProviderContext $dataProviderContext, BackendLayoutCollection $backendLayoutCollection)
    {
        $this->loadFromCache();

        foreach (BackendLayoutRegistry::list() as $data) {
            $backendLayout = $this->generateBackendLayout($data);

            if (is_null($backendLayout)) {
                continue;
            }

            $backendLayoutCollection->add($backendLayout);
        }
    }

    public function getBackendLayout($identifier, $pageId)
    {
        $this->loadFromCache();

        $data = BackendLayoutRegistry::getByIdentfier($identifier);

        if ($data !== null) {
            return $this->generateBackendLayout($data);
        }

        return null;
    }

    protected function generateBackendLayout(array $data): ?BackendLayout
    {
        $configuration = $data['configuration'];

        $backendLayoutData = $this->generateBackendLayoutFromTsConfig($data['identifier'] . '.', $configuration);
        if (is_null($backendLayoutData)) {
            return null;
        }

        $backendLayout = $this->createBackendLayout($backendLayoutData);

        // add icon to backend layout
        if (array_key_exists('icon', $data)) {
            $backendLayout->setIconPath($data['icon']);
        }

        return $backendLayout;
    }


    protected function loadFromCache()
    {
        $backendLayoutRegistryIdentifier = CustomCache::createIdentifier(BackendLayoutRegistry::class);

        $backendLayoutRegistryData = CustomCache::get($backendLayoutRegistryIdentifier);

        if (is_array($backendLayoutRegistryData)) {
            BackendLayoutRegistry::set($backendLayoutRegistryData);
            return;
        }

        CustomCache::set($backendLayoutRegistryIdentifier, BackendLayoutRegistry::list());
    }

    /**
     * Generates a Backend Layout from PageTsConfig array
     *
     * @param string $identifier
     * @param array $data
     * @return mixed
     */
    protected function generateBackendLayoutFromTsConfig($identifier, $data)
    {
        $backendLayout = [];
        if (!empty($data['config.']['backend_layout.']) && is_array($data['config.']['backend_layout.'])) {
            $backendLayout['uid'] = substr($identifier, 0, -1);
            $backendLayout['title'] = $data['title'] ?? $backendLayout['uid'];
            $backendLayout['icon'] = $data['icon'] ?? '';
            // Convert PHP array back to plain TypoScript so it can be processed
            $config = ArrayUtility::flatten($data['config.'] ?? []);
            $backendLayout['config'] = '';
            foreach ($config as $row => $value) {
                $backendLayout['config'] .= $row . ' = ' . $value . "\r\n";
            }
            return $backendLayout;
        }
        return null;
    }

    protected function createBackendLayout(array $data)
    {
        $backendLayout = BackendLayout::create($data['uid'], $data['title'], $data['config']);
        //$backendLayout->setIconPath($this->getIconPath($data['icon']));
        $backendLayout->setData($data);
        return $backendLayout;
    }
}
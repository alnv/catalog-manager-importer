<?php

namespace Alnv\CatalogManagerImporterBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Contao\CoreBundle\ContaoCoreBundle;
use Alnv\CatalogManagerBundle\AlnvCatalogManagerBundle;
use Alnv\CatalogManagerImporterBundle\AlnvCatalogManagerImporterBundle;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{

    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(AlnvCatalogManagerImporterBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, AlnvCatalogManagerBundle::class])
                ->setReplace(['catalog-manager-importer-bundle']),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__ . '/../Resources/config/routing.yml')
            ->load(__DIR__ . '/../Resources/config/routing.yml');
    }
}
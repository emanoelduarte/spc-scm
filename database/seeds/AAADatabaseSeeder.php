<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AAADatabaseSeeder extends AbstractSeed
{
    /**
     * Define as dependências para essa seed.
     * 
     * @return array
     * 
     */
    public function getDependencies(): array
    {
        return [
            'AddAdmsDamanUsers',
            'AddAdmsDamanAccessLevels',
            'AddAdmsDamanUsersAccessLevels',
            'AddAdmsPackagesPagesSeeder',
            'AddAdmsGroupsPagesSeeder',
            'AddAdmsPagesSeeder',
            'AddAdmsDamanProjects',
            'AddAdmsDamanSuppliersTypes',
            'AddAdmsDamanSuppliers',
            'AddAdmsDamanCategories',
            'AddAdmsDamanOrdersStatus',
            'AddAdmsDamanAcquisitionTypes',
            'AddAdmsDamanOrders',
            'AddAdmsDamanMeasurementUnits',
            'AddAdmsDamanOrderItems',
            'AddAdmsDamanPaymentMethods',
            'AddAdmsDamanPurchasings',
            'AddAdmsDamanPurchasingItems',
        ];
    }
}

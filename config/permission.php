    <?php

    return [

        /*
        |--------------------------------------------------------------------------
        | Guards
        |--------------------------------------------------------------------------
        */
        'guards' => [
            'web',
            'api',
        ],

        /*
        |--------------------------------------------------------------------------
        | Models
        |--------------------------------------------------------------------------
        */
        'models' => [
            'permission' => Spatie\Permission\Models\Permission::class,
            'role' => Spatie\Permission\Models\Role::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Table Names
        |--------------------------------------------------------------------------
        */
        'table_names' => [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ],

        /*
        |--------------------------------------------------------------------------
        | Column Names
        |--------------------------------------------------------------------------
        */
        'column_names' => [
            'role_pivot_key' => null,
            'permission_pivot_key' => null,
            'model_morph_key' => 'model_id',
            'team_foreign_key' => 'team_id',
        ],

        /*
        |--------------------------------------------------------------------------
        | Other Settings
        |--------------------------------------------------------------------------
        */
        'register_permission_check_method' => true,
        'register_octane_reset_listener' => false,
        'events_enabled' => false,
        'teams' => false,
        'team_resolver' => \Spatie\Permission\DefaultTeamResolver::class,
        'use_passport_client_credentials' => false,
        'display_permission_in_exception' => false,
        'display_role_in_exception' => false,
        'enable_wildcard_permission' => false,
        // 'permission.wildcard_permission' => Spatie\Permission\WildcardPermission::class,

        /*
        |--------------------------------------------------------------------------
        | Cache Settings
        |--------------------------------------------------------------------------
        */
        'cache' => [
            'expiration_time' => \DateInterval::createFromDateString('24 hours'),
            'key' => 'spatie.permission.cache',
            'store' => 'default',
        ],

        /*
        |--------------------------------------------------------------------------
        | Authorities (Tambahan: Pola Mesjid)
        |--------------------------------------------------------------------------
        | Ini untuk frontend React agar bisa render table CRUD centang
        */
        'authorities' => [
            'supplies' => ['index-supplies', 'create-supplies', 'show-supplies', 'update-supplies', 'delete-supplies'],
            'products' => ['index-products', 'create-products', 'show-products', 'update-products', 'delete-products'],
            'markets' => ['index-markets', 'create-markets', 'show-markets', 'update-markets', 'delete-markets'],
            'kariawan' => ['index-kariawan', 'create-kariawan', 'show-kariawan', 'update-kariawan', 'delete-kariawan'],
            'pengiriman' => ['index-pengiriman', 'create-pengiriman', 'show-pengiriman', 'update-pengiriman', 'delete-pengiriman'],
            'penjualan' => ['index-penjualan', 'create-penjualan', 'show-penjualan', 'update-penjualan', 'delete-penjualan'],
            'barangmasuk' => ['index-barangmasuk', 'create-barangmasuk', 'show-barangmasuk', 'update-barangmasuk', 'delete-barangmasuk'],
            'ukuran-botol' => ['index-ukuran-botol', 'create-ukuran-botol', 'show-ukuran-botol', 'update-ukuran-botol', 'delete-ukuran-botol'],
            'marketproduk' => ['index-marketproduk', 'create-marketproduk'],
            'stokmarket' => ['index-stokmarket'],
            'gudang' => ['index-gudang'],
            'grafik' => [
                'view-penjualan-tren',
                'view-penjualan-produk',
                'view-penjualan-market',
                'view-penjualan-revenue',
                'view-penjualan-ukuran',
                'view-pengiriman-tren',
                'view-pengiriman-market',
                'view-pengiriman-stok-supplier',
                'view-pengiriman-supplier',
                'view-pengiriman-frekuensi'
            ],
        ],

    ];

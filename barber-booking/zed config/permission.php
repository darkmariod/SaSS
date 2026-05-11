<?php

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */

        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

        'role' => Spatie\Permission\Models\Role::class,

    ],

    'table_names' => [

        /*
         * Names of the tables used by Spatie Permission.
         *
         * IMPORTANT:
         * This project already has an existing `roles` table for the app's custom role_id flow.
         * For that reason Spatie uses prefixed table names to avoid conflicts.
         */

        'roles' => 'spatie_roles',

        'permissions' => 'spatie_permissions',

        'model_has_permissions' => 'model_has_spatie_permissions',

        'model_has_roles' => 'model_has_spatie_roles',

        'role_has_permissions' => 'spatie_role_has_permissions',
    ],

    'column_names' => [

        /*
         * Change this if you want to name the related pivots other than defaults.
         */

        'role_pivot_key' => null,
        'permission_pivot_key' => null,

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         */

        'model_morph_key' => 'model_id',

        /*
         * Change this if you want to use the teams feature.
         */

        'team_foreign_key' => 'team_id',
    ],

    /*
     * When set to true, the method for checking permissions will be registered on the gate.
     * Set this to false if you want to implement custom logic for checking permissions.
     */

    'register_permission_check_method' => true,

    /*
     * When set to true, Laravel\Octane events will trigger a permission cache reset.
     */

    'register_octane_reset_listener' => false,

    /*
     * Events will fire when a role or permission is attached/detached:
     * \Spatie\Permission\Events\RoleAttached
     * \Spatie\Permission\Events\RoleDetached
     * \Spatie\Permission\Events\PermissionAttached
     * \Spatie\Permission\Events\PermissionDetached
     */

    'events_enabled' => false,

    /*
     * Teams Feature.
     * When set to true, the package implements teams using the `team_foreign_key`.
     */

    'teams' => false,

    /*
     * The class to use to resolve the permissions team id.
     */

    'team_resolver' => \Spatie\Permission\DefaultTeamResolver::class,

    /*
     * Passport Client Credentials Grant.
     * When set to true, the package will use Passport's Client model to check permissions.
     */

    'use_passport_client_credentials' => false,

    /*
     * When set to true, the required permission names are added to exception messages.
     * This can be useful for debugging but should usually be false in production.
     */

    'display_permission_in_exception' => false,

    /*
     * When set to true, the required role names are added to exception messages.
     * This can be useful for debugging but should usually be false in production.
     */

    'display_role_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     */

    'enable_wildcard_permission' => false,

    /*
     * The class to use for interpreting wildcard permissions.
     */

    'wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    /*
     * Cache settings.
     */

    'cache' => [

        /*
         * By default, all permissions are cached for 24 hours.
         */

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
         * Cache key.
         */

        'key' => 'spatie.permission.cache',

        /*
         * Cache store.
         * default = Laravel's default cache store.
         */

        'store' => 'default',
    ],
];

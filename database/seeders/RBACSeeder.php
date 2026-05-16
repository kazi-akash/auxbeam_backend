<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'module' => 'dashboard'],
            
            // Orders
            ['name' => 'view_orders', 'display_name' => 'View Orders', 'module' => 'orders'],
            ['name' => 'create_orders', 'display_name' => 'Create Orders', 'module' => 'orders'],
            ['name' => 'edit_orders', 'display_name' => 'Edit Orders', 'module' => 'orders'],
            ['name' => 'delete_orders', 'display_name' => 'Delete Orders', 'module' => 'orders'],
            ['name' => 'update_order_status', 'display_name' => 'Update Order Status', 'module' => 'orders'],
            ['name' => 'cancel_orders', 'display_name' => 'Cancel Orders', 'module' => 'orders'],
            ['name' => 'view_order_notes', 'display_name' => 'View Order Notes', 'module' => 'orders'],
            ['name' => 'add_order_notes', 'display_name' => 'Add Order Notes', 'module' => 'orders'],
            
            // Products
            ['name' => 'view_products', 'display_name' => 'View Products', 'module' => 'products'],
            ['name' => 'create_products', 'display_name' => 'Create Products', 'module' => 'products'],
            ['name' => 'edit_products', 'display_name' => 'Edit Products', 'module' => 'products'],
            ['name' => 'delete_products', 'display_name' => 'Delete Products', 'module' => 'products'],
            ['name' => 'manage_product_images', 'display_name' => 'Manage Product Images', 'module' => 'products'],
            ['name' => 'manage_product_variations', 'display_name' => 'Manage Product Variations', 'module' => 'products'],
            
            // Inventory
            ['name' => 'view_inventory', 'display_name' => 'View Inventory', 'module' => 'inventory'],
            ['name' => 'adjust_inventory', 'display_name' => 'Adjust Inventory', 'module' => 'inventory'],
            ['name' => 'view_inventory_logs', 'display_name' => 'View Inventory Logs', 'module' => 'inventory'],
            
            // Categories & Brands
            ['name' => 'view_categories', 'display_name' => 'View Categories', 'module' => 'categories'],
            ['name' => 'manage_categories', 'display_name' => 'Manage Categories', 'module' => 'categories'],
            ['name' => 'view_brands', 'display_name' => 'View Brands', 'module' => 'brands'],
            ['name' => 'manage_brands', 'display_name' => 'Manage Brands', 'module' => 'brands'],
            
            // Customers
            ['name' => 'view_customers', 'display_name' => 'View Customers', 'module' => 'customers'],
            ['name' => 'create_customers', 'display_name' => 'Create Customers', 'module' => 'customers'],
            ['name' => 'edit_customers', 'display_name' => 'Edit Customers', 'module' => 'customers'],
            ['name' => 'delete_customers', 'display_name' => 'Delete Customers', 'module' => 'customers'],
            
            // Coupons & Promotions
            ['name' => 'view_coupons', 'display_name' => 'View Coupons', 'module' => 'marketing'],
            ['name' => 'manage_coupons', 'display_name' => 'Manage Coupons', 'module' => 'marketing'],
            ['name' => 'view_promotions', 'display_name' => 'View Promotions', 'module' => 'marketing'],
            ['name' => 'manage_promotions', 'display_name' => 'Manage Promotions', 'module' => 'marketing'],
            ['name' => 'manage_campaigns', 'display_name' => 'Manage Campaigns', 'module' => 'marketing'],
            
            // Shipping
            ['name' => 'view_shipping', 'display_name' => 'View Shipping', 'module' => 'shipping'],
            ['name' => 'manage_shipping', 'display_name' => 'Manage Shipping', 'module' => 'shipping'],
            
            // Returns & Refunds
            ['name' => 'view_returns', 'display_name' => 'View Returns', 'module' => 'returns'],
            ['name' => 'manage_returns', 'display_name' => 'Manage Returns', 'module' => 'returns'],
            ['name' => 'view_refunds', 'display_name' => 'View Refunds', 'module' => 'refunds'],
            ['name' => 'manage_refunds', 'display_name' => 'Manage Refunds', 'module' => 'refunds'],
            
            // Reviews
            ['name' => 'view_reviews', 'display_name' => 'View Reviews', 'module' => 'reviews'],
            ['name' => 'manage_reviews', 'display_name' => 'Manage Reviews', 'module' => 'reviews'],
            
            // Reports
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'module' => 'reports'],
            ['name' => 'view_sales_reports', 'display_name' => 'View Sales Reports', 'module' => 'reports'],
            ['name' => 'view_profit_reports', 'display_name' => 'View Profit Reports', 'module' => 'reports'],
            ['name' => 'view_customer_reports', 'display_name' => 'View Customer Reports', 'module' => 'reports'],
            ['name' => 'export_reports', 'display_name' => 'Export Reports', 'module' => 'reports'],
            
            // Analytics
            ['name' => 'view_analytics', 'display_name' => 'View Analytics', 'module' => 'analytics'],
            
            // Content Management
            ['name' => 'manage_pages', 'display_name' => 'Manage Pages', 'module' => 'content'],
            ['name' => 'manage_banners', 'display_name' => 'Manage Banners', 'module' => 'content'],
            ['name' => 'manage_media', 'display_name' => 'Manage Media', 'module' => 'content'],
            
            // Settings
            ['name' => 'view_settings', 'display_name' => 'View Settings', 'module' => 'settings'],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'module' => 'settings'],
            
            // Users & Roles
            ['name' => 'view_users', 'display_name' => 'View Users', 'module' => 'users'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'module' => 'users'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'module' => 'users'],
            ['name' => 'manage_permissions', 'display_name' => 'Manage Permissions', 'module' => 'users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'permissions' => Permission::all()->pluck('id')->toArray(),
            ],
            [
                'name' => 'order_manager',
                'display_name' => 'Order Manager',
                'description' => 'Manage orders, shipping, and customer communications',
                'permissions' => Permission::whereIn('module', ['dashboard', 'orders', 'customers', 'shipping', 'returns', 'refunds'])
                    ->pluck('id')->toArray(),
            ],
            [
                'name' => 'inventory_manager',
                'display_name' => 'Inventory Manager',
                'description' => 'Manage products, inventory, categories, and brands',
                'permissions' => Permission::whereIn('module', ['dashboard', 'products', 'inventory', 'categories', 'brands'])
                    ->pluck('id')->toArray(),
            ],
            [
                'name' => 'marketing_manager',
                'display_name' => 'Marketing Manager',
                'description' => 'Manage promotions, coupons, campaigns, and content',
                'permissions' => Permission::whereIn('module', ['dashboard', 'marketing', 'content', 'analytics', 'reports'])
                    ->pluck('id')->toArray(),
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant',
                'description' => 'View financial reports and manage refunds',
                'permissions' => Permission::whereIn('module', ['dashboard', 'reports', 'refunds'])
                    ->orWhereIn('name', ['view_orders', 'view_customers'])
                    ->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            $role->syncPermissions($permissions);
        }

        $this->command->info('RBAC system seeded successfully!');
    }
}

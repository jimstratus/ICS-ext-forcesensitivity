<?php
/**
 * Force Sensitivity Detector - Installation Routine
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  setup
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\setup\install;

/**
 * Installation Steps
 */
class _install
{
    /**
     * Install Step 1: Create database tables
     *
     * @return array Array of language keys for messages
     */
    public function step1(): array
    {
        // Create status table
        \IPS\Db::i()->createTable([
            'name' => 'forcesensitivity_status',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                [
                    'name' => 'member_id',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true
                ],
                [
                    'name' => 'is_force_sensitive',
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 0
                ],
                [
                    'name' => 'detection_date',
                    'type' => 'DATETIME'
                ],
                [
                    'name' => 'detection_method',
                    'type' => 'VARCHAR',
                    'length' => 50
                ],
                [
                    'name' => 'probability_used',
                    'type' => 'DECIMAL',
                    'length' => '5,4',
                    'default' => '0.0000'
                ],
                [
                    'name' => 'admin_modifier',
                    'type' => 'DECIMAL',
                    'length' => '5,4',
                    'default' => '0.0000'
                ],
                [
                    'name' => 'detected_by',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'notes',
                    'type' => 'TEXT',
                    'allow_null' => true,
                    'default' => null
                ]
            ],
            'indexes' => [
                [
                    'type' => 'primary',
                    'columns' => ['id']
                ],
                [
                    'type' => 'unique',
                    'name' => 'idx_member',
                    'columns' => ['member_id']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_sensitive',
                    'columns' => ['is_force_sensitive']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_date',
                    'columns' => ['detection_date']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_method',
                    'columns' => ['detection_method']
                ]
            ]
        ]);
        
        return ['fs_install_status_table'];
    }
    
    /**
     * Install Step 2: Create log table
     *
     * @return array
     */
    public function step2(): array
    {
        \IPS\Db::i()->createTable([
            'name' => 'forcesensitivity_log',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                [
                    'name' => 'member_id',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true
                ],
                [
                    'name' => 'action',
                    'type' => 'VARCHAR',
                    'length' => 50
                ],
                [
                    'name' => 'old_value',
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'new_value',
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'performed_by',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'ip_address',
                    'type' => 'VARCHAR',
                    'length' => 45,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'timestamp',
                    'type' => 'DATETIME'
                ],
                [
                    'name' => 'details',
                    'type' => 'TEXT',
                    'allow_null' => true,
                    'default' => null
                ]
            ],
            'indexes' => [
                [
                    'type' => 'primary',
                    'columns' => ['id']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_member',
                    'columns' => ['member_id']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_action',
                    'columns' => ['action']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_timestamp',
                    'columns' => ['timestamp']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_performed_by',
                    'columns' => ['performed_by']
                ]
            ]
        ]);
        
        return ['fs_install_log_table'];
    }
    
    /**
     * Install Step 3: Create modifiers table
     *
     * @return array
     */
    public function step3(): array
    {
        \IPS\Db::i()->createTable([
            'name' => 'forcesensitivity_modifiers',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'INT',
                    'length' => 10,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                [
                    'name' => 'type',
                    'type' => 'VARCHAR',
                    'length' => 20
                ],
                [
                    'name' => 'target_id',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'modifier',
                    'type' => 'DECIMAL',
                    'length' => '5,4',
                    'default' => '0.0000'
                ],
                [
                    'name' => 'reason',
                    'type' => 'VARCHAR',
                    'length' => 255,
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'start_date',
                    'type' => 'DATETIME',
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'end_date',
                    'type' => 'DATETIME',
                    'allow_null' => true,
                    'default' => null
                ],
                [
                    'name' => 'is_active',
                    'type' => 'TINYINT',
                    'length' => 1,
                    'default' => 1
                ],
                [
                    'name' => 'created_by',
                    'type' => 'BIGINT',
                    'length' => 20,
                    'unsigned' => true
                ],
                [
                    'name' => 'created_date',
                    'type' => 'DATETIME'
                ]
            ],
            'indexes' => [
                [
                    'type' => 'primary',
                    'columns' => ['id']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_type_target',
                    'columns' => ['type', 'target_id']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_active',
                    'columns' => ['is_active']
                ],
                [
                    'type' => 'key',
                    'name' => 'idx_dates',
                    'columns' => ['start_date', 'end_date']
                ]
            ]
        ]);
        
        return ['fs_install_modifiers_table'];
    }
    
    /**
     * Install Step 4: Create profile field
     *
     * @return array
     */
    public function step4(): array
    {
        // Check if a Force Sensitivity profile field already exists
        try {
            $existing = \IPS\Db::i()->select(
                'pf_id',
                'core_pfields_data',
                ["pf_name LIKE ?", '%Force Sensitive%']
            )->first();
            
            // Store the existing field ID
            \IPS\Settings::i()->changeValues([
                'fs_profile_field_id' => $existing
            ]);
            
            return ['fs_install_field_exists'];
        } catch (\UnderflowException $e) {
            // Field doesn't exist, we could create it here
            // For now, we'll let the admin configure this manually
        }
        
        return ['fs_install_field_manual'];
    }
    
    /**
     * Install Step 5: Set default settings
     *
     * @return array
     */
    public function step5(): array
    {
        $defaults = [
            'fs_detection_enabled' => 1,
            'fs_base_probability' => 0.05,
            'fs_min_probability' => 0.01,
            'fs_max_probability' => 0.50,
            'fs_target_ratio' => 0.10,
            'fs_ratio_enforcement' => 'soft',
            'fs_ratio_window' => 100,
            'fs_auto_adjust' => 1,
            'fs_admin_override' => 1,
            'fs_reroll_enabled' => 1,
            'fs_reroll_cooldown' => 86400,
            'fs_bulk_operations' => 1,
            'fs_show_badge' => 1,
            'fs_badge_style' => 'glow',
            'fs_show_in_posts' => 1,
            'fs_sensitive_label' => 'Force Sensitive',
            'fs_blind_label' => 'Force Blind',
            'fs_log_retention' => 0,
            'fs_profile_field_id' => 0
        ];
        
        // Only set settings that don't already exist
        foreach ($defaults as $key => $value) {
            try {
                $existing = \IPS\Db::i()->select('conf_value', 'core_sys_conf_settings', ['conf_key=?', $key])->first();
            } catch (\UnderflowException $e) {
                // Setting doesn't exist, create it
                \IPS\Db::i()->insert('core_sys_conf_settings', [
                    'conf_key' => $key,
                    'conf_value' => $value,
                    'conf_default' => $value,
                    'conf_app' => 'forcesensitivity'
                ]);
            }
        }
        
        // Clear settings cache
        unset(\IPS\Data\Store::i()->settings);
        
        return ['fs_install_settings'];
    }
}

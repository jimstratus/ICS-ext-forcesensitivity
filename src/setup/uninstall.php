<?php
/**
 * Force Sensitivity Detector - Uninstall Routine
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  setup
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\setup\uninstall;

/**
 * Uninstallation Steps
 */
class _uninstall
{
    /**
     * Uninstall Step 1: Remove database tables
     *
     * @return array Array of language keys for messages
     */
    public function step1(): array
    {
        // Drop status table
        if (\IPS\Db::i()->checkForTable('forcesensitivity_status')) {
            \IPS\Db::i()->dropTable('forcesensitivity_status');
        }
        
        return ['fs_uninstall_status_table'];
    }
    
    /**
     * Uninstall Step 2: Remove log table
     *
     * @return array
     */
    public function step2(): array
    {
        // Drop log table
        if (\IPS\Db::i()->checkForTable('forcesensitivity_log')) {
            \IPS\Db::i()->dropTable('forcesensitivity_log');
        }
        
        return ['fs_uninstall_log_table'];
    }
    
    /**
     * Uninstall Step 3: Remove modifiers table
     *
     * @return array
     */
    public function step3(): array
    {
        // Drop modifiers table
        if (\IPS\Db::i()->checkForTable('forcesensitivity_modifiers')) {
            \IPS\Db::i()->dropTable('forcesensitivity_modifiers');
        }
        
        return ['fs_uninstall_modifiers_table'];
    }
    
    /**
     * Uninstall Step 4: Clean up settings
     *
     * @return array
     */
    public function step4(): array
    {
        // Remove all app settings
        \IPS\Db::i()->delete('core_sys_conf_settings', ['conf_app=?', 'forcesensitivity']);
        
        // Clear settings cache
        unset(\IPS\Data\Store::i()->settings);
        
        return ['fs_uninstall_settings'];
    }
    
    /**
     * Uninstall Step 5: Optionally remove profile field
     *
     * Note: By default, we leave the profile field intact to preserve data.
     * Administrators can manually remove it if desired.
     *
     * @return array
     */
    public function step5(): array
    {
        // We intentionally don't delete the profile field
        // This preserves user data in case of reinstall
        
        return ['fs_uninstall_complete'];
    }
}

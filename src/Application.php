<?php
/**
 * Force Sensitivity Detector Application
 * 
 * @package     IPS\forcesensitivity
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 * @version     1.0.0
 */

namespace IPS\forcesensitivity;

/**
 * Force Sensitivity Application Class
 */
class _Application extends \IPS\Application
{
    /**
     * Application version
     */
    public static string $version = '1.0.0';
    
    /**
     * Application author
     */
    public static string $author = 'jimstratus';
    
    /**
     * Install other elements that need to be in place
     *
     * @return void
     */
    public function installOther(): void
    {
        // Create custom profile field for Force Sensitivity if it doesn't exist
        $this->createProfileField();
    }
    
    /**
     * Create the Force Sensitivity profile field
     *
     * @return void
     */
    protected function createProfileField(): void
    {
        // Check if field already exists
        try {
            $existing = \IPS\Db::i()->select(
                'pf_id',
                'core_pfields_data',
                ["pf_name LIKE ?", '%force_sensitive%']
            )->first();
            
            // Field exists, store its ID
            \IPS\Settings::i()->changeValues([
                'fs_profile_field_id' => $existing
            ]);
            
            return;
        } catch (\UnderflowException $e) {
            // Field doesn't exist, create it
        }
        
        // Create the profile field
        // TODO: Implement field creation
    }
    
    /**
     * Default front navigation
     *
     * @param array $frontNavigation Current navigation
     * @param \IPS\Member|null $member Member (NULL for guest)
     * @return array
     */
    public function defaultFrontNavigation(array $frontNavigation, ?\IPS\Member $member = null): array
    {
        return $frontNavigation;
    }
}

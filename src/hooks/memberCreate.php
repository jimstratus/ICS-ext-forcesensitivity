<?php
/**
 * Force Sensitivity Detector - Member Registration Hook
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  hooks
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

/* To install this hook, point to: \IPS\Member */

/**
 * Member Registration Hook
 * 
 * Hooks into the member creation process to automatically detect
 * Force Sensitivity for newly registered members.
 */
class forcesensitivity_hook_memberCreate extends _HOOK_CLASS_
{
    /**
     * Create a new member
     * 
     * @return void
     */
    public function save(): void
    {
        // Check if this is a new member (no member_id yet)
        $isNew = !$this->member_id;
        
        // Call parent save method
        parent::save();
        
        // Only process new members
        if (!$isNew) {
            return;
        }
        
        // Check if Force Sensitivity detection is enabled
        if (!\IPS\Settings::i()->fs_detection_enabled) {
            return;
        }
        
        // Check if this is a real registration (not admin-created, API, etc.)
        // We want to detect for all new members by default
        try {
            \IPS\forcesensitivity\ForceSensitivity\Detector::detect($this, 'registration');
        } catch (\Exception $e) {
            // Log error but don't prevent registration
            \IPS\Log::log($e, 'forcesensitivity');
        }
    }
}

<?php
/**
 * Force Sensitivity Detector - Member Profile Extension
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  extensions
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\extensions\core\Profile;

/* To install this extension, point to: \IPS\core\ProfileFields */

/**
 * Profile Tab Extension
 * 
 * Adds Force Sensitivity information to member profiles.
 */
class _ForceSensitivity
{
    /**
     * @brief Member whose profile we're viewing
     */
    protected \IPS\Member $member;
    
    /**
     * Constructor
     * 
     * @param \IPS\Member $member Member whose profile we're viewing
     */
    public function __construct(\IPS\Member $member)
    {
        $this->member = $member;
    }
    
    /**
     * Can the viewer see this extension?
     * 
     * @return bool
     */
    public function canView(): bool
    {
        // Check if badges/display is enabled
        if (!\IPS\Settings::i()->fs_show_badge) {
            return false;
        }
        
        // Check if member has a status
        $status = \IPS\forcesensitivity\ForceSensitivity\Status::loadByMember($this->member);
        
        return $status !== null;
    }
    
    /**
     * Get the content to display
     * 
     * @return string
     */
    public function render(): string
    {
        $status = \IPS\forcesensitivity\ForceSensitivity\Status::loadByMember($this->member);
        
        if (!$status) {
            return '';
        }
        
        return \IPS\Theme::i()->getTemplate('profile', 'forcesensitivity', 'front')->badge($status);
    }
}

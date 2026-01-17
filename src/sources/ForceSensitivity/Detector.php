<?php
/**
 * Force Sensitivity Detector - Core Detector Class
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  ForceSensitivity
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\ForceSensitivity;

/**
 * Core detection engine for Force Sensitivity determination
 * 
 * This class handles all aspects of detecting whether a user is Force Sensitive,
 * including probability calculation, random rolling, and status management.
 * 
 * @see \IPS\forcesensitivity\ForceSensitivity\Status
 * @see \IPS\forcesensitivity\ForceSensitivity\Modifier
 */
class _Detector
{
    /**
     * Custom probability modifier callbacks
     * 
     * Callbacks should have signature: function(\IPS\Member $member, float $probability): float
     * 
     * @var callable[]
     */
    protected static array $customModifiers = [];
    
    /**
     * Detect Force Sensitivity for a member
     * 
     * This is the main entry point for Force Sensitivity detection. It calculates
     * the probability, performs the roll, updates the database, and fires events.
     * 
     * @param \IPS\Member $member The member to detect
     * @param string $method Detection method (registration, admin, reroll, event)
     * @param float|null $customProbability Override probability (bypasses calculation)
     * @param \IPS\Member|null $admin Admin performing the action (if applicable)
     * @return bool True if member is Force Sensitive
     * 
     * @example
     * // Basic detection on registration
     * $isSensitive = Detector::detect($newMember, 'registration');
     * 
     * // Admin-triggered with custom probability
     * $isSensitive = Detector::detect($member, 'admin', 0.75, $adminMember);
     */
    public static function detect(
        \IPS\Member $member,
        string $method = 'registration',
        ?float $customProbability = null,
        ?\IPS\Member $admin = null
    ): bool {
        // Calculate final probability
        $probability = $customProbability ?? static::calculateProbability($member);
        
        // Prepare logging details
        $details = [
            'probability' => $probability,
            'method' => $method,
            'custom_modifiers_applied' => count(static::$customModifiers),
            'ratio_at_detection' => RatioManager::getCurrentRatio()
        ];
        
        // Fire pre-roll event (allows last-minute probability adjustments)
        // TODO: Implement event dispatcher integration
        
        // Perform the probability roll
        $isSensitive = static::roll($probability);
        
        // Load or create status record
        $status = Status::loadByMember($member);
        $oldValue = null;
        
        if ($status !== null) {
            $oldValue = $status->is_force_sensitive ? 'sensitive' : 'blind';
        } else {
            $status = new Status();
        }
        
        // Update status
        $status->member_id = $member->member_id;
        $status->is_force_sensitive = $isSensitive;
        $status->detection_date = new \IPS\DateTime();
        $status->detection_method = $method;
        $status->probability_used = $probability;
        $status->detected_by = $admin?->member_id;
        $status->save();
        
        // Update the member's profile field
        static::updateProfileField($member, $isSensitive);
        
        // Create audit log entry
        \IPS\forcesensitivity\Log\Entry::log(
            $member,
            'detection',
            $oldValue,
            $isSensitive ? 'sensitive' : 'blind',
            $admin,
            $details
        );
        
        // Fire post-detection event
        // TODO: Implement event dispatcher integration
        
        return $isSensitive;
    }
    
    /**
     * Directly set Force Sensitivity status (admin override)
     * 
     * Bypasses probability completely and directly assigns a status.
     * This is logged as an admin action for audit purposes.
     * 
     * @param \IPS\Member $member The member to update
     * @param bool $isSensitive The status to set
     * @param \IPS\Member $admin The admin performing the action
     * @param string|null $reason Optional reason for the override
     * @return void
     * 
     * @example
     * Detector::setStatus($member, true, $admin, 'Contest winner reward');
     */
    public static function setStatus(
        \IPS\Member $member,
        bool $isSensitive,
        \IPS\Member $admin,
        ?string $reason = null
    ): void {
        $status = Status::loadByMember($member);
        $oldValue = null;
        
        if ($status !== null) {
            $oldValue = $status->is_force_sensitive ? 'sensitive' : 'blind';
        } else {
            $status = new Status();
        }
        
        $status->member_id = $member->member_id;
        $status->is_force_sensitive = $isSensitive;
        $status->detection_date = new \IPS\DateTime();
        $status->detection_method = 'admin';
        $status->probability_used = $isSensitive ? 1.0 : 0.0;
        $status->detected_by = $admin->member_id;
        $status->notes = $reason;
        $status->save();
        
        static::updateProfileField($member, $isSensitive);
        
        \IPS\forcesensitivity\Log\Entry::log(
            $member,
            'admin_override',
            $oldValue,
            $isSensitive ? 'sensitive' : 'blind',
            $admin,
            ['reason' => $reason]
        );
    }
    
    /**
     * Calculate the probability for a member
     * 
     * Combines base probability with all applicable modifiers and enforces
     * minimum/maximum bounds.
     * 
     * @param \IPS\Member $member The member to calculate for
     * @return float Probability between min and max settings
     */
    public static function calculateProbability(\IPS\Member $member): float
    {
        // Start with base probability from settings
        $probability = (float) \IPS\Settings::i()->fs_base_probability;
        
        // Apply ratio-based adjustment
        if (\IPS\Settings::i()->fs_auto_adjust) {
            $probability += RatioManager::calculateAdjustment($probability);
        }
        
        // Apply stored modifiers (member, group, global, event)
        $probability += Modifier::calculateTotalModifier($member);
        
        // Apply custom callback modifiers
        foreach (static::$customModifiers as $callback) {
            $probability = $callback($member, $probability);
        }
        
        // Clamp to configured min/max
        $min = (float) \IPS\Settings::i()->fs_min_probability;
        $max = (float) \IPS\Settings::i()->fs_max_probability;
        
        return min($max, max($min, $probability));
    }
    
    /**
     * Perform the probability roll
     * 
     * Uses cryptographically secure random number generation for fair results.
     * 
     * @param float $probability The probability (0.0 to 1.0)
     * @return bool True if roll succeeds (user is Force Sensitive)
     */
    protected static function roll(float $probability): bool
    {
        // Use cryptographically secure random for fairness
        // Generate number 0-10000, divide by 10000 for 4 decimal precision
        $roll = random_int(0, 10000) / 10000;
        
        return $roll <= $probability;
    }
    
    /**
     * Update the member's profile field
     * 
     * @param \IPS\Member $member The member to update
     * @param bool $isSensitive The Force Sensitivity status
     * @return void
     */
    protected static function updateProfileField(\IPS\Member $member, bool $isSensitive): void
    {
        $fieldId = \IPS\Settings::i()->fs_profile_field_id;
        
        if (!$fieldId) {
            return;
        }
        
        try {
            $member->setProfileFieldValuesInMemory([
                'core_pfield_' . $fieldId => $isSensitive ? 1 : 0
            ]);
            $member->save();
        } catch (\Exception $e) {
            // Log error but don't fail detection
            \IPS\Log::log($e, 'forcesensitivity');
        }
    }
    
    /**
     * Add a custom probability modifier
     * 
     * Custom modifiers allow external code to adjust probability based on
     * any criteria. They are called after all built-in modifiers.
     * 
     * @param callable $callback Function(\IPS\Member $member, float $probability): float
     * @return void
     * 
     * @example
     * // Give users with 1000+ posts a 5% bonus
     * Detector::addProbabilityModifier(function($member, $probability) {
     *     if ($member->posts >= 1000) {
     *         return $probability + 0.05;
     *     }
     *     return $probability;
     * });
     */
    public static function addProbabilityModifier(callable $callback): void
    {
        static::$customModifiers[] = $callback;
    }
    
    /**
     * Clear all custom modifiers
     * 
     * Primarily used for testing purposes.
     * 
     * @return void
     */
    public static function clearCustomModifiers(): void
    {
        static::$customModifiers = [];
    }
    
    /**
     * Check if a member can be rerolled
     * 
     * @param \IPS\Member $member The member to check
     * @return bool|int True if can reroll, or seconds until cooldown expires
     */
    public static function canReroll(\IPS\Member $member): bool|int
    {
        if (!\IPS\Settings::i()->fs_reroll_enabled) {
            return false;
        }
        
        $status = Status::loadByMember($member);
        
        if ($status === null) {
            return true; // Never detected, can "reroll" (really first roll)
        }
        
        $cooldown = (int) \IPS\Settings::i()->fs_reroll_cooldown;
        $lastDetection = $status->detection_date;
        $now = new \IPS\DateTime();
        
        $secondsSince = $now->getTimestamp() - $lastDetection->getTimestamp();
        
        if ($secondsSince >= $cooldown) {
            return true;
        }
        
        return $cooldown - $secondsSince;
    }
}

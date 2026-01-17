<?php
/**
 * Force Sensitivity Detector - Status Model
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  ForceSensitivity
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\ForceSensitivity;

/**
 * Force Sensitivity Status Model
 * 
 * Represents a member's Force Sensitivity status in the database.
 * Each member can have at most one status record.
 */
class _Status extends \IPS\Patterns\ActiveRecord
{
    /**
     * @brief Database Table
     */
    public static $databaseTable = 'forcesensitivity_status';
    
    /**
     * @brief Database ID Field
     */
    public static $databaseColumnId = 'id';
    
    /**
     * @brief Multiton Store
     */
    protected static $multitons;
    
    /**
     * @brief Default Values
     */
    protected static $defaultValues = [
        'is_force_sensitive' => 0,
        'admin_modifier' => 0.0
    ];
    
    /**
     * @brief Database Column Map
     */
    public static $databaseColumnMap = [
        'date' => 'detection_date'
    ];
    
    /**
     * Load status by member
     * 
     * @param \IPS\Member $member The member to load status for
     * @return static|null The status record or null if not found
     */
    public static function loadByMember(\IPS\Member $member): ?static
    {
        try {
            return static::constructFromData(
                \IPS\Db::i()->select(
                    '*',
                    static::$databaseTable,
                    ['member_id=?', $member->member_id]
                )->first()
            );
        } catch (\UnderflowException $e) {
            return null;
        }
    }
    
    /**
     * Check if member is Force Sensitive
     * 
     * @return bool
     */
    public function isForceSensitive(): bool
    {
        return (bool) $this->is_force_sensitive;
    }
    
    /**
     * Get the member this status belongs to
     * 
     * @return \IPS\Member
     */
    public function getMember(): \IPS\Member
    {
        return \IPS\Member::load($this->member_id);
    }
    
    /**
     * Get detection method label (translated)
     * 
     * @return string
     */
    public function getMethodLabel(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack(
            'fs_method_' . $this->detection_method
        );
    }
    
    /**
     * Get status label (translated)
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        if ($this->is_force_sensitive) {
            $customLabel = \IPS\Settings::i()->fs_sensitive_label;
            return $customLabel ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_sensitive');
        }
        
        $customLabel = \IPS\Settings::i()->fs_blind_label;
        return $customLabel ?: \IPS\Member::loggedIn()->language()->addToStack('fs_status_blind');
    }
    
    /**
     * Get admin who performed detection (if applicable)
     * 
     * @return \IPS\Member|null
     */
    public function getDetectedBy(): ?\IPS\Member
    {
        if ($this->detected_by) {
            $member = \IPS\Member::load($this->detected_by);
            return $member->member_id ? $member : null;
        }
        return null;
    }
    
    /**
     * Get formatted detection date
     * 
     * @return string
     */
    public function getFormattedDate(): string
    {
        if ($this->detection_date instanceof \IPS\DateTime) {
            return $this->detection_date->localeDate();
        }
        
        $date = new \IPS\DateTime($this->detection_date);
        return $date->localeDate();
    }
    
    /**
     * Get all statuses with pagination
     * 
     * @param array $where Additional where conditions
     * @param string $order Order by clause
     * @param array $limit Limit clause [offset, count]
     * @return array
     */
    public static function getAll(array $where = [], string $order = 'detection_date DESC', array $limit = [0, 25]): array
    {
        $statuses = [];
        
        foreach (\IPS\Db::i()->select(
            '*',
            static::$databaseTable,
            $where ?: null,
            $order,
            $limit
        ) as $row) {
            $statuses[] = static::constructFromData($row);
        }
        
        return $statuses;
    }
    
    /**
     * Count statuses matching conditions
     * 
     * @param array $where Where conditions
     * @return int
     */
    public static function count(array $where = []): int
    {
        return \IPS\Db::i()->select(
            'COUNT(*)',
            static::$databaseTable,
            $where ?: null
        )->first();
    }
    
    /**
     * Count Force Sensitive users
     * 
     * @return int
     */
    public static function countSensitive(): int
    {
        return static::count(['is_force_sensitive=?', 1]);
    }
    
    /**
     * Count Force Blind users
     * 
     * @return int
     */
    public static function countBlind(): int
    {
        return static::count(['is_force_sensitive=?', 0]);
    }
}

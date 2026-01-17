<?php
/**
 * Force Sensitivity Detector - Modifier Model
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  ForceSensitivity
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

namespace IPS\forcesensitivity\ForceSensitivity;

/**
 * Probability Modifier Model
 * 
 * Represents a probability modifier that can be applied to members,
 * groups, globally, or during specific events.
 */
class _Modifier extends \IPS\Patterns\ActiveRecord
{
    /**
     * @brief Database Table
     */
    public static $databaseTable = 'forcesensitivity_modifiers';
    
    /**
     * @brief Database ID Field
     */
    public static $databaseColumnId = 'id';
    
    /**
     * @brief Multiton Store
     */
    protected static $multitons;
    
    /**
     * Modifier type constants
     */
    const TYPE_MEMBER = 'member';
    const TYPE_GROUP = 'group';
    const TYPE_GLOBAL = 'global';
    const TYPE_EVENT = 'event';
    
    /**
     * @brief Default Values
     */
    protected static $defaultValues = [
        'modifier' => 0.0,
        'is_active' => 1
    ];
    
    /**
     * Get all active modifiers for a member
     * 
     * Returns all modifiers that currently apply to the given member,
     * including member-specific, group-based, global, and active event modifiers.
     * 
     * @param \IPS\Member $member The member to get modifiers for
     * @return array Array of Modifier objects
     */
    public static function getForMember(\IPS\Member $member): array
    {
        $modifiers = [];
        $now = new \IPS\DateTime();
        $nowStr = $now->format('Y-m-d H:i:s');
        
        // Query all potentially active modifiers
        $where = [
            'is_active = 1',
            '(start_date IS NULL OR start_date <= ?)',
            '(end_date IS NULL OR end_date >= ?)'
        ];
        
        foreach (\IPS\Db::i()->select(
            '*',
            static::$databaseTable,
            [implode(' AND ', $where), $nowStr, $nowStr]
        ) as $row) {
            $modifier = static::constructFromData($row);
            
            // Check if this modifier applies to the member
            if ($modifier->appliesTo($member)) {
                $modifiers[] = $modifier;
            }
        }
        
        return $modifiers;
    }
    
    /**
     * Check if this modifier applies to a specific member
     * 
     * @param \IPS\Member $member The member to check
     * @return bool True if modifier applies
     */
    public function appliesTo(\IPS\Member $member): bool
    {
        switch ($this->type) {
            case self::TYPE_GLOBAL:
            case self::TYPE_EVENT:
                return true;
                
            case self::TYPE_MEMBER:
                return (int) $this->target_id === (int) $member->member_id;
                
            case self::TYPE_GROUP:
                // Check if member is in the target group
                $memberGroups = array_merge(
                    [$member->member_group_id],
                    explode(',', $member->mgroup_others)
                );
                return in_array((int) $this->target_id, array_map('intval', $memberGroups));
                
            default:
                return false;
        }
    }
    
    /**
     * Calculate total modifier value for a member
     * 
     * Sums all applicable modifiers for the given member.
     * 
     * @param \IPS\Member $member The member to calculate for
     * @return float Total modifier adjustment
     */
    public static function calculateTotalModifier(\IPS\Member $member): float
    {
        $total = 0.0;
        
        foreach (static::getForMember($member) as $modifier) {
            $total += (float) $modifier->modifier;
        }
        
        return $total;
    }
    
    /**
     * Get modifier type label (translated)
     * 
     * @return string
     */
    public function getTypeLabel(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack('fs_modifier_type_' . $this->type);
    }
    
    /**
     * Get target label (member name, group name, etc.)
     * 
     * @return string
     */
    public function getTargetLabel(): string
    {
        switch ($this->type) {
            case self::TYPE_MEMBER:
                $member = \IPS\Member::load($this->target_id);
                return $member->member_id ? $member->name : \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_member');
                
            case self::TYPE_GROUP:
                try {
                    $group = \IPS\Member\Group::load($this->target_id);
                    return $group->name;
                } catch (\OutOfRangeException $e) {
                    return \IPS\Member::loggedIn()->language()->addToStack('fs_deleted_group');
                }
                
            case self::TYPE_GLOBAL:
                return \IPS\Member::loggedIn()->language()->addToStack('fs_all_members');
                
            case self::TYPE_EVENT:
                return $this->reason ?: \IPS\Member::loggedIn()->language()->addToStack('fs_event');
                
            default:
                return '—';
        }
    }
    
    /**
     * Get formatted modifier value (with + or - sign)
     * 
     * @return string
     */
    public function getFormattedValue(): string
    {
        $value = (float) $this->modifier;
        $percentage = round($value * 100, 1);
        
        if ($value >= 0) {
            return '+' . $percentage . '%';
        }
        
        return $percentage . '%';
    }
    
    /**
     * Check if modifier is currently active (within date range)
     * 
     * @return bool
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        $now = new \IPS\DateTime();
        
        if ($this->start_date) {
            $start = new \IPS\DateTime($this->start_date);
            if ($now < $start) {
                return false;
            }
        }
        
        if ($this->end_date) {
            $end = new \IPS\DateTime($this->end_date);
            if ($now > $end) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get active period description
     * 
     * @return string
     */
    public function getActivePeriod(): string
    {
        $parts = [];
        
        if ($this->start_date) {
            $start = new \IPS\DateTime($this->start_date);
            $parts[] = \IPS\Member::loggedIn()->language()->addToStack('fs_from') . ' ' . $start->localeDate();
        }
        
        if ($this->end_date) {
            $end = new \IPS\DateTime($this->end_date);
            $parts[] = \IPS\Member::loggedIn()->language()->addToStack('fs_until') . ' ' . $end->localeDate();
        }
        
        if (empty($parts)) {
            return \IPS\Member::loggedIn()->language()->addToStack('fs_always');
        }
        
        return implode(' ', $parts);
    }
    
    /**
     * Get admin who created this modifier
     * 
     * @return \IPS\Member|null
     */
    public function getCreatedBy(): ?\IPS\Member
    {
        if ($this->created_by) {
            $member = \IPS\Member::load($this->created_by);
            return $member->member_id ? $member : null;
        }
        return null;
    }
    
    /**
     * Create a new modifier
     * 
     * @param string $type Modifier type (use class constants)
     * @param int|null $targetId Target ID (member or group ID, null for global/event)
     * @param float $modifier Modifier value (-1.0 to 1.0)
     * @param string|null $reason Reason for the modifier
     * @param \IPS\DateTime|null $startDate When modifier becomes active
     * @param \IPS\DateTime|null $endDate When modifier expires
     * @param \IPS\Member $createdBy Admin creating the modifier
     * @return static
     */
    public static function createModifier(
        string $type,
        ?int $targetId,
        float $modifier,
        ?string $reason,
        ?\IPS\DateTime $startDate,
        ?\IPS\DateTime $endDate,
        \IPS\Member $createdBy
    ): static {
        $obj = new static();
        $obj->type = $type;
        $obj->target_id = $targetId;
        $obj->modifier = $modifier;
        $obj->reason = $reason;
        $obj->start_date = $startDate?->format('Y-m-d H:i:s');
        $obj->end_date = $endDate?->format('Y-m-d H:i:s');
        $obj->is_active = 1;
        $obj->created_by = $createdBy->member_id;
        $obj->created_date = (new \IPS\DateTime())->format('Y-m-d H:i:s');
        $obj->save();
        
        return $obj;
    }
    
    /**
     * Get all modifiers with optional filtering
     * 
     * @param array $filters Filter options
     * @param string $order Order by clause
     * @param array $limit Limit [offset, count]
     * @return array
     */
    public static function getAll(array $filters = [], string $order = 'created_date DESC', array $limit = [0, 25]): array
    {
        $where = [];
        
        if (isset($filters['type'])) {
            $where[] = ['type=?', $filters['type']];
        }
        
        if (isset($filters['is_active'])) {
            $where[] = ['is_active=?', $filters['is_active'] ? 1 : 0];
        }
        
        if (isset($filters['target_id'])) {
            $where[] = ['target_id=?', $filters['target_id']];
        }
        
        $modifiers = [];
        
        foreach (\IPS\Db::i()->select(
            '*',
            static::$databaseTable,
            $where ?: null,
            $order,
            $limit
        ) as $row) {
            $modifiers[] = static::constructFromData($row);
        }
        
        return $modifiers;
    }
    
    /**
     * Count modifiers
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
     * Deactivate expired modifiers
     * 
     * Should be run periodically via task.
     * 
     * @return int Number of modifiers deactivated
     */
    public static function deactivateExpired(): int
    {
        $now = (new \IPS\DateTime())->format('Y-m-d H:i:s');
        
        return \IPS\Db::i()->update(
            static::$databaseTable,
            ['is_active' => 0],
            ['end_date IS NOT NULL AND end_date < ? AND is_active = 1', $now]
        );
    }
}

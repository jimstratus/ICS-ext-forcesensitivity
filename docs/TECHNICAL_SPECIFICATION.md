# Technical Specification

> Force Sensitivity Detector - ICS v4.7.20 Extension  
> **Version**: 1.0.0 | **Last Updated**: January 18, 2026

---

## 1. System Overview

### 1.1 Purpose
This extension adds a gamification element to Invision Community Suite by randomly assigning a "Force Sensitivity" attribute to users based on configurable probability settings.

### 1.2 Scope
- User registration event handling
- Admin-triggered status changes
- Probability calculation engine
- Profile field management
- Audit logging system

---

## 2. Probability Engine

### 2.1 Base Calculation

The probability engine uses a multi-factor approach to determine the final probability:

```
Final Probability = min(max_probability, max(min_probability, 
    base_probability 
    + ratio_adjustment 
    + member_modifier 
    + group_modifier 
    + event_modifier
))
```

### 2.2 Ratio Adjustment Algorithm

```php
function calculateRatioAdjustment(float $baseProbability): float
{
    $currentRatio = $this->getCurrentFSRatio();
    $targetRatio = $this->settings['target_ratio'];
    $enforcementMode = $this->settings['ratio_enforcement'];
    
    if ($enforcementMode === 'none') {
        return 0;
    }
    
    $deviation = $targetRatio - $currentRatio;
    
    // Soft enforcement: gradual adjustment
    if ($enforcementMode === 'soft') {
        // Scale adjustment based on how far from target
        // Max adjustment is ±50% of base probability
        return $baseProbability * ($deviation / $targetRatio) * 0.5;
    }
    
    // Hard enforcement: aggressive adjustment
    if ($enforcementMode === 'hard') {
        if ($currentRatio < $targetRatio * 0.8) {
            // Significantly under target: boost probability
            return $baseProbability * 2;
        } elseif ($currentRatio > $targetRatio * 1.2) {
            // Significantly over target: reduce probability
            return -($baseProbability * 0.75);
        }
        return $baseProbability * ($deviation / $targetRatio);
    }
    
    return 0;
}
```

### 2.3 Current Ratio Calculation

```php
function getCurrentFSRatio(): float
{
    $window = $this->settings['ratio_window'];
    
    // Get recent members within window
    $recentMembers = \IPS\Db::i()->select(
        'COUNT(*) as total, SUM(is_force_sensitive) as sensitive',
        'forcesensitivity_status',
        NULL,
        'detection_date DESC',
        $window
    )->first();
    
    if ($recentMembers['total'] === 0) {
        return 0;
    }
    
    return $recentMembers['sensitive'] / $recentMembers['total'];
}
```

### 2.4 Random Number Generation

```php
function rollForForceSensitivity(float $probability): bool
{
    // Use cryptographically secure random for fairness
    $roll = random_int(0, 10000) / 10000;
    return $roll <= $probability;
}
```

---

## 3. Detection Triggers

### 3.1 Registration Hook

**File**: `hooks/memberCreate.php`

```php
class forcesensitivity_hook_memberCreate extends _HOOK_CLASS_
{
    /**
     * Create a new member
     */
    public function save()
    {
        // Call parent first
        parent::save();
        
        // Only process new members (not updates)
        if ($this->_new) {
            // Check if detection is enabled
            if (\IPS\Settings::i()->fs_detection_enabled) {
                // Trigger detection for the new member
                \IPS\forcesensitivity\ForceSensitivity\Detector::detect($this, 'registration');
            }
        }
    }
}
```

### 3.2 Admin Manual Trigger

**File**: `modules/admin/members.php`

```php
class _members extends \IPS\Dispatcher\Controller
{
    /**
     * Manually trigger detection for a member
     */
    public function detect()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('fs_manage_members');
        
        $memberId = \IPS\Request::i()->id;
        $member = \IPS\Member::load($memberId);
        
        if (!$member->member_id) {
            \IPS\Output::i()->error('Invalid member', '2FS101/1');
        }
        
        // Check for reroll cooldown
        if (!$this->canReroll($member)) {
            \IPS\Output::i()->error('Reroll cooldown active', '1FS101/2');
        }
        
        // Perform detection with optional probability override
        $customProbability = \IPS\Request::i()->probability ?? null;
        
        $result = \IPS\forcesensitivity\Detector::detect(
            $member, 
            'admin',
            $customProbability,
            \IPS\Member::loggedIn()
        );
        
        // Redirect with result
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=members&controller=members'),
            $result ? 'fs_detected_sensitive' : 'fs_detected_blind'
        );
    }
    
    /**
     * Directly set Force Sensitivity status
     */
    public function setStatus()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('fs_override_status');
        
        $memberId = \IPS\Request::i()->id;
        $newStatus = (bool) \IPS\Request::i()->status;
        $member = \IPS\Member::load($memberId);
        
        \IPS\forcesensitivity\Detector::setStatus(
            $member,
            $newStatus,
            \IPS\Member::loggedIn(),
            \IPS\Request::i()->reason ?? null
        );
        
        \IPS\Output::i()->redirect(
            \IPS\Http\Url::internal('app=forcesensitivity&module=members&controller=members'),
            'fs_status_updated'
        );
    }
}
```

---

## 4. Data Models

### 4.1 Status Model

**File**: `sources/ForceSensitivity/Status.php`

```php
namespace IPS\forcesensitivity\ForceSensitivity;

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
     * Get status for member
     *
     * @param \IPS\Member $member
     * @return static|null
     */
    public static function loadByMember(\IPS\Member $member): ?static
    {
        try {
            return static::constructFromData(
                \IPS\Db::i()->select('*', static::$databaseTable, [
                    'member_id=?', $member->member_id
                ])->first()
            );
        } catch (\UnderflowException $e) {
            return null;
        }
    }
    
    /**
     * Check if member is Force Sensitive
     */
    public function isForceSensitive(): bool
    {
        return (bool) $this->is_force_sensitive;
    }
    
    /**
     * Get detection method label
     */
    public function getMethodLabel(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack(
            'fs_method_' . $this->detection_method
        );
    }
    
    /**
     * Get admin who performed detection (if applicable)
     */
    public function getDetectedBy(): ?\IPS\Member
    {
        if ($this->detected_by) {
            return \IPS\Member::load($this->detected_by);
        }
        return null;
    }
}
```

### 4.2 Log Entry Model

**File**: `sources/Log/Entry.php`

```php
namespace IPS\forcesensitivity\Log;

class _Entry extends \IPS\Patterns\ActiveRecord
{
    public static $databaseTable = 'forcesensitivity_log';
    public static $databaseColumnId = 'id';
    protected static $multitons;
    
    /**
     * Create log entry
     */
    public static function log(
        \IPS\Member $member,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?\IPS\Member $performer = null,
        ?array $details = null
    ): static {
        $entry = new static;
        $entry->member_id = $member->member_id;
        $entry->action = $action;
        $entry->old_value = $oldValue;
        $entry->new_value = $newValue;
        $entry->performed_by = $performer?->member_id;
        $entry->ip_address = \IPS\Request::i()->ipAddress();
        $entry->timestamp = new \IPS\DateTime;
        $entry->details = $details ? json_encode($details) : null;
        $entry->save();
        
        return $entry;
    }
    
    /**
     * Get entries for member
     */
    public static function getForMember(\IPS\Member $member, int $limit = 50): array
    {
        $entries = [];
        foreach (\IPS\Db::i()->select(
            '*', 
            static::$databaseTable, 
            ['member_id=?', $member->member_id],
            'timestamp DESC',
            $limit
        ) as $row) {
            $entries[] = static::constructFromData($row);
        }
        return $entries;
    }
}
```

### 4.3 Modifier Model

**File**: `sources/ForceSensitivity/Modifier.php`

```php
namespace IPS\forcesensitivity\ForceSensitivity;

class _Modifier extends \IPS\Patterns\ActiveRecord
{
    public static $databaseTable = 'forcesensitivity_modifiers';
    public static $databaseColumnId = 'id';
    protected static $multitons;
    
    const TYPE_MEMBER = 'member';
    const TYPE_GROUP = 'group';
    const TYPE_GLOBAL = 'global';
    const TYPE_EVENT = 'event';
    
    /**
     * Get active modifiers for member
     */
    public static function getForMember(\IPS\Member $member): array
    {
        $modifiers = [];
        $now = new \IPS\DateTime;
        
        foreach (\IPS\Db::i()->select('*', static::$databaseTable, [
            'is_active=1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?)',
            $now->format('Y-m-d H:i:s'),
            $now->format('Y-m-d H:i:s')
        ]) as $row) {
            $mod = static::constructFromData($row);
            
            // Check if applies to this member
            if ($mod->appliesTo($member)) {
                $modifiers[] = $mod;
            }
        }
        
        return $modifiers;
    }
    
    /**
     * Check if modifier applies to member
     */
    public function appliesTo(\IPS\Member $member): bool
    {
        return match($this->type) {
            self::TYPE_GLOBAL => true,
            self::TYPE_MEMBER => $this->target_id == $member->member_id,
            self::TYPE_GROUP => in_array($this->target_id, $member->groups),
            self::TYPE_EVENT => true, // Events apply globally when active
            default => false
        };
    }
    
    /**
     * Calculate total modifier for member
     */
    public static function calculateTotalModifier(\IPS\Member $member): float
    {
        $total = 0;
        foreach (static::getForMember($member) as $modifier) {
            $total += $modifier->modifier;
        }
        return $total;
    }
}
```

---

## 5. Core Detector Class

**File**: `sources/ForceSensitivity/Detector.php`

```php
namespace IPS\forcesensitivity\ForceSensitivity;

class _Detector
{
    /**
     * @var array Custom probability modifiers (callbacks)
     */
    protected static array $customModifiers = [];
    
    /**
     * Detect Force Sensitivity for a member
     */
    public static function detect(
        \IPS\Member $member,
        string $method = 'registration',
        ?float $customProbability = null,
        ?\IPS\Member $admin = null
    ): bool {
        // Calculate probability
        $probability = $customProbability ?? static::calculateProbability($member);
        
        // Store pre-roll data for logging
        $details = [
            'probability' => $probability,
            'method' => $method,
            'custom_modifiers_applied' => count(static::$customModifiers)
        ];
        
        // Fire pre-roll event
        \IPS\Dispatcher::i()->trigger('onProbabilityCalculated', [
            'member' => $member,
            'probability' => &$probability
        ]);
        
        // Roll the dice
        $isSensitive = static::roll($probability);
        
        // Create/update status record
        $status = Status::loadByMember($member) ?? new Status;
        $oldValue = $status->id ? ($status->is_force_sensitive ? 'sensitive' : 'blind') : null;
        
        $status->member_id = $member->member_id;
        $status->is_force_sensitive = $isSensitive;
        $status->detection_date = new \IPS\DateTime;
        $status->detection_method = $method;
        $status->probability_used = $probability;
        $status->detected_by = $admin?->member_id;
        $status->save();
        
        // Update member's profile field
        static::updateProfileField($member, $isSensitive);
        
        // Log the action
        \IPS\forcesensitivity\Log\Entry::log(
            $member,
            'detection',
            $oldValue,
            $isSensitive ? 'sensitive' : 'blind',
            $admin,
            $details
        );
        
        // Fire post-detection event
        \IPS\Dispatcher::i()->trigger('onForceSensitivityDetermined', [
            'member' => $member,
            'isSensitive' => $isSensitive,
            'method' => $method
        ]);
        
        return $isSensitive;
    }
    
    /**
     * Directly set Force Sensitivity status (admin override)
     */
    public static function setStatus(
        \IPS\Member $member,
        bool $isSensitive,
        \IPS\Member $admin,
        ?string $reason = null
    ): void {
        $status = Status::loadByMember($member) ?? new Status;
        $oldValue = $status->id ? ($status->is_force_sensitive ? 'sensitive' : 'blind') : null;
        
        $status->member_id = $member->member_id;
        $status->is_force_sensitive = $isSensitive;
        $status->detection_date = new \IPS\DateTime;
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
        
        \IPS\Dispatcher::i()->trigger('onForceSensitivityChanged', [
            'member' => $member,
            'isSensitive' => $isSensitive,
            'changedBy' => $admin
        ]);
    }
    
    /**
     * Calculate probability for member
     */
    public static function calculateProbability(\IPS\Member $member): float
    {
        $settings = \IPS\Settings::i();
        
        // Start with base probability
        $probability = (float) $settings->fs_base_probability;
        
        // Apply ratio adjustment
        $probability += RatioManager::calculateAdjustment($probability);
        
        // Apply stored modifiers
        $probability += Modifier::calculateTotalModifier($member);
        
        // Apply custom callback modifiers
        foreach (static::$customModifiers as $callback) {
            $probability = $callback($member, $probability);
        }
        
        // Clamp to min/max
        return min(
            (float) $settings->fs_max_probability,
            max((float) $settings->fs_min_probability, $probability)
        );
    }
    
    /**
     * Perform the probability roll
     */
    protected static function roll(float $probability): bool
    {
        return (random_int(0, 10000) / 10000) <= $probability;
    }
    
    /**
     * Update member's profile field
     */
    protected static function updateProfileField(\IPS\Member $member, bool $isSensitive): void
    {
        $fieldId = \IPS\Settings::i()->fs_profile_field_id;
        
        if ($fieldId) {
            $member->setProfileFieldValuesInMemory([
                'core_pfield_' . $fieldId => $isSensitive ? 1 : 0
            ]);
            $member->save();
        }
    }
    
    /**
     * Add custom probability modifier
     */
    public static function addProbabilityModifier(callable $callback): void
    {
        static::$customModifiers[] = $callback;
    }
}
```

---

## 6. ACP Settings Schema

**File**: `dev/settings.json`

```json
[
    {
        "key": "fs_detection_enabled",
        "default": "1",
        "type": "toggle"
    },
    {
        "key": "fs_base_probability",
        "default": "0.05",
        "type": "number"
    },
    {
        "key": "fs_min_probability",
        "default": "0.01",
        "type": "number"
    },
    {
        "key": "fs_max_probability",
        "default": "0.50",
        "type": "number"
    },
    {
        "key": "fs_target_ratio",
        "default": "0.10",
        "type": "number"
    },
    {
        "key": "fs_ratio_enforcement",
        "default": "soft",
        "type": "select",
        "options": {
            "none": "fs_ratio_none",
            "soft": "fs_ratio_soft",
            "hard": "fs_ratio_hard"
        }
    },
    {
        "key": "fs_ratio_window",
        "default": "100",
        "type": "number"
    },
    {
        "key": "fs_auto_adjust",
        "default": "1",
        "type": "toggle"
    },
    {
        "key": "fs_admin_override",
        "default": "1",
        "type": "toggle"
    },
    {
        "key": "fs_reroll_enabled",
        "default": "1",
        "type": "toggle"
    },
    {
        "key": "fs_reroll_cooldown",
        "default": "86400",
        "type": "number"
    },
    {
        "key": "fs_show_badge",
        "default": "1",
        "type": "toggle"
    },
    {
        "key": "fs_badge_style",
        "default": "glow",
        "type": "select",
        "options": {
            "simple": "fs_badge_simple",
            "glow": "fs_badge_glow",
            "animated": "fs_badge_animated"
        }
    },
    {
        "key": "fs_show_in_posts",
        "default": "1",
        "type": "toggle"
    },
    {
        "key": "fs_sensitive_label",
        "default": "Force Sensitive",
        "type": "text"
    },
    {
        "key": "fs_blind_label",
        "default": "Force Blind",
        "type": "text"
    },
    {
        "key": "fs_profile_field_id",
        "default": "",
        "type": "number"
    }
]
```

---

## 7. Language Strings

**File**: `dev/lang.php`

```php
<?php

$lang = [
    // Application
    '__app_forcesensitivity' => 'Force Sensitivity',
    
    // Menu
    'menu__forcesensitivity_settings' => 'Settings',
    'menu__forcesensitivity_members' => 'Members',
    'menu__forcesensitivity_logs' => 'Audit Logs',
    'menu__forcesensitivity_modifiers' => 'Modifiers',
    
    // Settings
    'fs_settings_title' => 'Force Sensitivity Settings',
    'fs_detection_enabled' => 'Enable Automatic Detection',
    'fs_detection_enabled_desc' => 'Automatically determine Force Sensitivity when users register',
    
    'fs_probability_settings' => 'Probability Settings',
    'fs_base_probability' => 'Base Probability',
    'fs_base_probability_desc' => 'The base chance (0-1) that a user will be Force Sensitive',
    'fs_min_probability' => 'Minimum Probability',
    'fs_max_probability' => 'Maximum Probability',
    
    'fs_ratio_settings' => 'Ratio Management',
    'fs_target_ratio' => 'Target FS Ratio',
    'fs_target_ratio_desc' => 'The desired ratio of Force Sensitive users in your community',
    'fs_ratio_enforcement' => 'Ratio Enforcement Mode',
    'fs_ratio_none' => 'None (ignore ratio)',
    'fs_ratio_soft' => 'Soft (gradual adjustment)',
    'fs_ratio_hard' => 'Hard (aggressive adjustment)',
    'fs_ratio_window' => 'Ratio Calculation Window',
    'fs_ratio_window_desc' => 'Number of recent users to consider for ratio calculation',
    
    'fs_admin_settings' => 'Admin Controls',
    'fs_admin_override' => 'Allow Admin Override',
    'fs_reroll_enabled' => 'Allow Reroll',
    'fs_reroll_cooldown' => 'Reroll Cooldown (seconds)',
    
    'fs_display_settings' => 'Display Settings',
    'fs_show_badge' => 'Show Badge on Profiles',
    'fs_badge_style' => 'Badge Style',
    'fs_badge_simple' => 'Simple',
    'fs_badge_glow' => 'Glowing',
    'fs_badge_animated' => 'Animated',
    'fs_show_in_posts' => 'Show in Forum Posts',
    'fs_sensitive_label' => 'Force Sensitive Label',
    'fs_blind_label' => 'Force Blind Label',
    
    // Member management
    'fs_members_title' => 'Force Sensitivity - Members',
    'fs_member_status' => 'Force Status',
    'fs_status_sensitive' => 'Force Sensitive',
    'fs_status_blind' => 'Force Blind',
    'fs_status_unknown' => 'Not Determined',
    'fs_detect_now' => 'Detect Now',
    'fs_reroll' => 'Reroll',
    'fs_set_sensitive' => 'Set as Sensitive',
    'fs_set_blind' => 'Set as Blind',
    'fs_view_history' => 'View History',
    
    // Detection methods
    'fs_method_registration' => 'Registration',
    'fs_method_admin' => 'Admin',
    'fs_method_reroll' => 'Reroll',
    'fs_method_event' => 'Event',
    
    // Logs
    'fs_logs_title' => 'Force Sensitivity - Audit Logs',
    'fs_log_action' => 'Action',
    'fs_log_member' => 'Member',
    'fs_log_old_value' => 'Previous Status',
    'fs_log_new_value' => 'New Status',
    'fs_log_performed_by' => 'Performed By',
    'fs_log_timestamp' => 'Timestamp',
    
    // Messages
    'fs_detected_sensitive' => 'User determined to be Force Sensitive!',
    'fs_detected_blind' => 'User determined to be Force Blind.',
    'fs_status_updated' => 'Force Sensitivity status updated.',
    'fs_cooldown_active' => 'Reroll cooldown is still active for this user.',
    
    // Modifiers
    'fs_modifiers_title' => 'Probability Modifiers',
    'fs_modifier_type' => 'Type',
    'fs_modifier_target' => 'Target',
    'fs_modifier_value' => 'Modifier Value',
    'fs_modifier_dates' => 'Active Period',
    'fs_modifier_reason' => 'Reason',
    'fs_add_modifier' => 'Add Modifier',
    
    // Errors
    'fs_error_invalid_member' => 'Invalid member specified.',
    'fs_error_cooldown' => 'Cannot reroll yet. Cooldown expires in %s.',
];
```

---

## 8. API Endpoints (Optional)

### 8.1 REST API

```php
namespace IPS\forcesensitivity\api;

class _members extends \IPS\Api\Controller
{
    /**
     * GET /forcesensitivity/members/{id}
     * Get Force Sensitivity status for a member
     */
    public function GETitem($id)
    {
        $member = \IPS\Member::load($id);
        $status = \IPS\forcesensitivity\ForceSensitivity\Status::loadByMember($member);
        
        return new \IPS\Api\Response(200, [
            'member_id' => $member->member_id,
            'is_force_sensitive' => $status?->isForceSensitive() ?? null,
            'detection_date' => $status?->detection_date?->rfc3339(),
            'detection_method' => $status?->detection_method
        ]);
    }
    
    /**
     * POST /forcesensitivity/members/{id}/detect
     * Trigger detection for a member
     */
    public function POSTitem_detect($id)
    {
        $member = \IPS\Member::load($id);
        $result = \IPS\forcesensitivity\ForceSensitivity\Detector::detect(
            $member,
            'api',
            $this->request->probability ?? null
        );
        
        return new \IPS\Api\Response(200, [
            'member_id' => $member->member_id,
            'is_force_sensitive' => $result
        ]);
    }
}
```

---

## 9. Security Considerations

### 9.1 Permission Checks
- All ACP actions require appropriate permissions
- API endpoints require authentication
- Audit logging for all admin actions

### 9.2 Input Validation
- Probability values clamped to valid range (0-1)
- Member IDs validated before operations
- SQL injection prevention via parameter binding

### 9.3 Rate Limiting
- Reroll cooldown prevents abuse
- API rate limits apply to detection endpoint

---

## 10. Performance Considerations

### 10.1 Caching
- Current ratio cached with short TTL (5 minutes)
- Member modifiers cached per-request

### 10.2 Database Indexes
- Indexes on frequently queried columns
- Optimized queries for ratio calculation

### 10.3 Batch Operations
- Bulk operations use transactions
- Progress feedback for large operations

---

## 11. Testing Strategy

> **Note**: Unit and integration tests are planned for v1.1.0. Current testing is manual.

### 11.1 Unit Tests (Planned v1.1.0)
- Probability calculation accuracy
- Ratio adjustment algorithms
- Modifier stacking

### 11.2 Integration Tests (Planned v1.1.0)
- Registration hook triggering
- Admin operations
- Profile field updates

### 11.3 Manual Testing (Current)
- Full registration flow verification
- ACP settings changes
- Bulk operations
- All admin modules tested
- CSRF protection verified

# Force Sensitivity Detector

> **Invision Community Suite v4.7.20 Extension**  
> Automatically determines user Force Sensitivity upon registration or via admin trigger

---

## 📋 Overview

The Force Sensitivity Detector is a custom extension for Invision Community Suite (ICS) v4.7.20 that adds a mystical element to your community by determining whether users possess "Force Sensitivity" - a special trait stored in their profile.

### Key Features

- 🎲 **Probability-based Detection** - Configurable chance system for Force Sensitivity determination
- 👤 **Registration Hook** - Automatic detection upon new user registration
- 🔧 **Admin Controls** - Manual trigger and probability manipulation per user
- 📊 **Community Ratios** - Maintain desired balance of Force Sensitive users
- 📝 **Audit Logging** - Track all Force Sensitivity determinations
- 🎨 **Profile Integration** - Custom profile field with badge/flair display options

---

## 🎯 Requirements

- Invision Community Suite **v4.7.20**
- PHP **7.4+** (recommended 8.0+)
- MySQL **5.7+** / MariaDB **10.2+**
- Admin ACP access for installation and configuration

---

## 🏗️ Architecture

### Extension Structure

```
forcesensitivity/
├── Application.php                 # Main application class
├── extensions/
│   └── core/
│       ├── Profile/
│       │   └── ForceSensitivity.php    # Profile badge extension
│       └── MemberFilter/
│           └── ForceSensitivity.php    # ACP member filter
├── hooks/
│   └── memberCreate.php            # Registration detection hook
├── modules/
│   └── admin/
│       └── forcesensitivity/
│           ├── settings.php        # Admin settings module
│           ├── members.php         # Member management module
│           ├── modifiers.php       # Probability modifiers CRUD
│           └── logs.php            # Audit log viewer
├── sources/
│   ├── ForceSensitivity/
│   │   ├── Detector.php            # Core detection logic
│   │   ├── Status.php              # Status ActiveRecord model
│   │   ├── Modifier.php            # Probability modifier model
│   │   └── RatioManager.php        # Community ratio management
│   └── Log/
│       └── Entry.php               # Audit log entry model
├── dev/
│   ├── lang.php                    # Language strings (150+ keys)
│   ├── jslang.php                  # JavaScript language strings
│   ├── settings.json               # ACP settings definitions
│   ├── css/
│   │   └── forcesensitivity.css    # Badge and indicator styles
│   └── html/                       # Template files
│       ├── admin/                  # Admin templates
│       └── front/                  # Front-end templates
├── data/
│   ├── application.json            # App metadata
│   ├── extensions.json             # Extension definitions
│   ├── hooks.json                  # Hook definitions
│   └── modules.json                # Module definitions
└── setup/
    ├── install.php                 # Database installation
    └── uninstall.php               # Clean removal
```

---

## ⚙️ Configuration Options

### Core Probability Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `base_probability` | Float | 0.05 (5%) | Base chance of Force Sensitivity |
| `max_probability` | Float | 0.50 (50%) | Maximum allowed probability cap |
| `min_probability` | Float | 0.01 (1%) | Minimum allowed probability floor |
| `detection_enabled` | Boolean | true | Master toggle for auto-detection |

### Ratio Management

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `target_ratio` | Float | 0.10 (10%) | Desired FS:Total user ratio |
| `ratio_enforcement` | Enum | `soft` | `none`, `soft`, `hard` enforcement mode |
| `ratio_window` | Integer | 100 | Number of recent registrations to consider |
| `auto_adjust_probability` | Boolean | true | Dynamically adjust based on current ratio |

### Admin Override Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `admin_can_override` | Boolean | true | Allow admins to set FS status directly |
| `admin_probability_boost` | Float | 0.10 | Per-user probability modifier range |
| `bulk_operations_enabled` | Boolean | true | Enable bulk FS operations |
| `reroll_enabled` | Boolean | true | Allow re-determination of FS status |
| `reroll_cooldown` | Integer | 86400 | Cooldown between rerolls (seconds) |

### Display & Integration

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `show_badge` | Boolean | true | Display FS badge on profiles |
| `badge_style` | Enum | `glow` | `simple`, `glow`, `animated` |
| `show_in_posts` | Boolean | true | Show FS indicator in forum posts |
| `custom_field_name` | String | `Force Sensitive` | Profile field display name |
| `sensitive_label` | String | `Force Sensitive` | Label for FS users |
| `not_sensitive_label` | String | `Force Blind` | Label for non-FS users |

---

## 🔄 Detection Flow

### Registration Detection

```
┌─────────────────┐
│  User Registers │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ Check if Detection  │
│     is Enabled      │
└────────┬────────────┘
         │ Yes
         ▼
┌─────────────────────┐
│  Calculate Current  │
│   Community Ratio   │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│ Adjust Probability  │
│  Based on Ratio     │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│   Roll for Force    │
│    Sensitivity      │
└────────┬────────────┘
         │
    ┌────┴────┐
    │         │
    ▼         ▼
┌───────┐ ┌───────────┐
│  Yes  │ │    No     │
└───┬───┘ └─────┬─────┘
    │           │
    ▼           ▼
┌───────────────────────┐
│ Update Profile Field  │
│    & Log Result       │
└───────────────────────┘
```

### Admin Manual Trigger

```
┌─────────────────────┐
│ Admin Selects User  │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│   Choose Action:    │
│ • Set FS Status     │
│ • Reroll Detection  │
│ • Adjust Modifier   │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│   Apply Changes &   │
│     Log Action      │
└─────────────────────┘
```

---

## 🖥️ Admin Control Panel (ACP) Features

### 1. Dashboard Widget
- Current FS ratio visualization
- Recent detection activity
- Quick stats (total FS, recent conversions)

### 2. Settings Page
- All configuration options with inline help
- Import/Export settings
- Reset to defaults button

### 3. Member Management
- Searchable/filterable member list
- Inline FS status toggle
- Bulk selection operations:
  - Mass reroll
  - Set all selected to FS/Non-FS
  - Apply probability modifier
- Individual member actions:
  - View detection history
  - Adjust personal probability modifier
  - Force reroll with custom probability

### 4. Probability Modifiers
- Per-user probability adjustments
- Group-based probability bonuses
- Time-based events (e.g., "May the 4th" boost)
- Achievement/milestone triggers

### 5. Audit Logs
- Complete detection history
- Admin action tracking
- Export functionality
- Retention settings

---

## 📊 Database Schema

### `forcesensitivity_status`
```sql
CREATE TABLE `{prefix}forcesensitivity_status` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` BIGINT UNSIGNED NOT NULL,
  `is_force_sensitive` TINYINT(1) NOT NULL DEFAULT 0,
  `detection_date` DATETIME NOT NULL,
  `detection_method` ENUM('registration', 'admin', 'reroll', 'event') NOT NULL,
  `probability_used` DECIMAL(5,4) NOT NULL,
  `admin_modifier` DECIMAL(5,4) DEFAULT 0,
  `detected_by` BIGINT UNSIGNED NULL,
  `notes` TEXT NULL,
  INDEX `idx_member` (`member_id`),
  INDEX `idx_sensitive` (`is_force_sensitive`),
  INDEX `idx_date` (`detection_date`)
);
```

### `forcesensitivity_log`
```sql
CREATE TABLE `{prefix}forcesensitivity_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `member_id` BIGINT UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `old_value` VARCHAR(255) NULL,
  `new_value` VARCHAR(255) NULL,
  `performed_by` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `timestamp` DATETIME NOT NULL,
  `details` JSON NULL,
  INDEX `idx_member` (`member_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_timestamp` (`timestamp`)
);
```

### `forcesensitivity_modifiers`
```sql
CREATE TABLE `{prefix}forcesensitivity_modifiers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('member', 'group', 'global', 'event') NOT NULL,
  `target_id` BIGINT UNSIGNED NULL,
  `modifier` DECIMAL(5,4) NOT NULL,
  `reason` VARCHAR(255) NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_date` DATETIME NOT NULL,
  INDEX `idx_type_target` (`type`, `target_id`),
  INDEX `idx_active` (`is_active`)
);
```

---

## 🔌 Hooks & Events

### Available Hooks

| Hook | Trigger | Purpose |
|------|---------|---------|
| `onMemberRegistration` | User completes registration | Auto-detection trigger |
| `onForceSensitivityDetermined` | After FS roll completes | Custom actions on result |
| `onForceSensitivityChanged` | FS status modified | Notification/sync triggers |
| `onProbabilityCalculated` | Before roll | Custom probability modifiers |

### Example Hook Usage

```php
// Add custom probability modifier
\IPS\forcesensitivity\Detector::addProbabilityModifier(function($member, $baseProbability) {
    // Users with 1000+ posts get a 5% bonus
    if ($member->posts >= 1000) {
        return $baseProbability + 0.05;
    }
    return $baseProbability;
});

// React to FS determination
\IPS\Dispatcher::i()->on('onForceSensitivityDetermined', function($member, $isSensitive) {
    if ($isSensitive) {
        // Award achievement, send notification, etc.
        $member->grantAchievement('force_awakened');
    }
});
```

---

## 🎨 Theming & Display

### CSS Classes

```css
.fs-badge { }                    /* Base badge styling */
.fs-badge--sensitive { }         /* Force Sensitive badge */
.fs-badge--blind { }             /* Force Blind badge */
.fs-badge--glow { }              /* Glowing effect variant */
.fs-badge--animated { }          /* Animated variant */
.fs-indicator { }                /* In-post indicator */
.fs-profile-field { }            /* Profile field wrapper */
```

### Template Bits

- `forceSensitivityBadge` - Standalone badge
- `forceSensitivityIndicator` - Compact indicator
- `forceSensitivityProfileField` - Full profile field display

---

## 📦 Installation

1. Download the extension package
2. Navigate to **ACP → System → Applications & Plugins**
3. Click **Install** and upload the package
4. Configure settings in **ACP → Community → Force Sensitivity**
5. (Optional) Customize display templates and CSS

---

## 🔧 Development Roadmap

### Phase 1: Core Foundation ✅
- [x] Application structure setup
- [x] Database schema implementation
- [x] Core detection logic
- [x] Registration hook integration

### Phase 2: Admin Interface ✅
- [x] Settings page implementation
- [x] Member management interface
- [x] Bulk operations
- [x] Audit log viewer

### Phase 3: Display & Integration ✅
- [x] Profile field integration
- [x] Badge/flair system (simple, glow, animated styles)
- [x] Post indicator display
- [x] Theme compatibility (dark mode support)

### Phase 4: Advanced Features ✅
- [x] Event-based probability modifiers
- [x] Modifier management (member, group, global, event types)
- [ ] API endpoints (planned for v1.1)
- [ ] Webhook notifications (planned for v1.1)

### Phase 5: Polish & Documentation ✅
- [x] Comprehensive documentation
- [x] GitHub issue templates
- [x] Localization support (150+ language strings)
- [ ] Unit tests (planned for v1.1)

---

## 📄 License

This extension is released under the [MIT License](LICENSE).

---

## 🤝 Contributing

Contributions are welcome! Please read our contributing guidelines before submitting pull requests.

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/jimstratus/ICS-ext-forcesensitivity/issues)
- **Documentation**: See `/docs` folder
- **IPS Community**: [Forum Thread](#)

---

*May the Force be with you... or not. It's really up to the probability engine.*
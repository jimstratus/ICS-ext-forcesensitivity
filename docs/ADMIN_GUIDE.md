# Administrator Guide

> Force Sensitivity Detector - ICS v4.7.20 Extension

---

## Table of Contents

1. [Installation](#installation)
2. [Quick Start](#quick-start)
3. [Configuration Guide](#configuration-guide)
4. [Managing Members](#managing-members)
5. [Probability Modifiers](#probability-modifiers)
6. [Viewing Audit Logs](#viewing-audit-logs)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

---

## Installation

### Prerequisites
- Invision Community Suite v4.7.20
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Administrator access to ACP

### Installation Steps

1. **Download** the Force Sensitivity extension package (`.tar` file)

2. **Navigate** to your Admin Control Panel:
   ```
   AdminCP → System → Applications & Plugins → Applications
   ```

3. **Click** "Install" and upload the extension package

4. **Wait** for the installation to complete - this will:
   - Create required database tables
   - Add configuration settings
   - Set up the profile field (optional)

5. **Configure** the extension at:
   ```
   AdminCP → Community → Force Sensitivity → Settings
   ```

### Post-Installation Checklist

- [ ] Configure base probability settings
- [ ] Set desired community ratio
- [ ] Choose badge display style
- [ ] Review admin permissions
- [ ] Test with a test registration

---

## Quick Start

### Minimum Setup (5 Minutes)

1. **Enable Detection**
   - Go to Settings → Enable "Automatic Detection"
   - Leave probability at default 5%

2. **Set Display Preference**
   - Enable "Show Badge on Profiles"
   - Choose your preferred badge style

3. **Test**
   - Create a test account or trigger manual detection
   - Verify the result appears correctly

### Recommended Setup (15 Minutes)

1. **Define Your Community Goals**
   - How rare should Force Sensitivity be?
   - Should it adjust automatically?

2. **Configure Probability**
   - Base: 5% (1 in 20 users)
   - Target Ratio: 10%
   - Enforcement: Soft

3. **Customize Labels**
   - "Force Sensitive" → Your custom label
   - "Force Blind" → Your custom label

4. **Set Up Permissions**
   - Define which admin groups can manage
   - Enable/disable bulk operations

---

## Configuration Guide

### Understanding Probability Settings

#### Base Probability
The starting chance any user has of being Force Sensitive.

| Value | Meaning | Rarity |
|-------|---------|--------|
| 0.01 (1%) | Very Rare | 1 in 100 users |
| 0.05 (5%) | Rare | 1 in 20 users |
| 0.10 (10%) | Uncommon | 1 in 10 users |
| 0.25 (25%) | Common | 1 in 4 users |
| 0.50 (50%) | Very Common | 1 in 2 users |

**Recommendation**: Start at 5% and adjust based on community feedback.

#### Minimum/Maximum Probability
These are hard limits that prevent probability from going too low or too high.

- **Minimum** (default 1%): Even with all negative modifiers, users still have this chance
- **Maximum** (default 50%): Even with all positive modifiers, probability can't exceed this

### Ratio Management

The ratio system helps maintain a desired balance of Force Sensitive users.

#### Target Ratio
The percentage of your community that should be Force Sensitive.

**Example**: 
- Target: 10%
- Community: 1,000 users
- Expected FS users: ~100

#### Enforcement Modes

**None**
- Ratio is ignored
- Each user gets the same base probability
- Use when you want pure randomness

**Soft (Recommended)**
- Gently adjusts probability toward target
- If below target: slightly increases probability
- If above target: slightly decreases probability
- Feels natural to users

**Hard**
- Aggressively adjusts to meet target
- Can result in "streaks" of FS or non-FS users
- Use only if exact ratio is critical

#### Ratio Window
How many recent registrations to consider when calculating current ratio.

| Value | Effect |
|-------|--------|
| 50 | Quick to react, may fluctuate |
| 100 | Balanced (recommended) |
| 500 | Slow to react, very stable |
| All | Consider entire community |

### Admin Control Settings

#### Allow Admin Override
When enabled, administrators can:
- Directly set a user's FS status
- Override the random determination
- Useful for special circumstances

#### Allow Reroll
When enabled:
- Users who have been determined can be re-rolled
- Subject to cooldown period
- Creates new audit log entry

#### Reroll Cooldown
Time (in seconds) before a user can be re-rolled again.

| Seconds | Duration |
|---------|----------|
| 3600 | 1 hour |
| 86400 | 24 hours |
| 604800 | 1 week |
| 2592000 | 30 days |

### Display Settings

#### Show Badge on Profiles
Displays a visual indicator on user profiles.

#### Badge Styles

**Simple**
- Plain text badge
- Low visual impact
- Fastest loading

**Glow (Recommended)**
- Subtle glow effect
- Visually appealing
- Minimal performance impact

**Animated**
- Animated effects
- High visual impact
- May affect page performance

#### Show in Forum Posts
Adds a small indicator next to the user's name in posts.

---

## Managing Members

### Accessing Member Management

```
AdminCP → Community → Force Sensitivity → Members
```

### Member List Features

#### Filtering Options
- **Status**: All, Sensitive, Blind, Not Determined
- **Detection Method**: Registration, Admin, Reroll, Event
- **Date Range**: Filter by detection date
- **Search**: By username or email

#### Sorting
- Username (A-Z, Z-A)
- Detection Date (Newest, Oldest)
- Status (Sensitive first, Blind first)

### Individual Member Actions

#### View Details
Shows complete information:
- Current status
- Detection date and method
- Probability used
- All historical changes

#### Detect Now
For users without a determination:
- Uses current probability settings
- Creates audit log entry
- Updates profile field

#### Reroll
For users with existing status:
- Subject to cooldown
- Uses current probability settings
- Logs previous status

#### Set Status
Direct override:
- Bypass probability system
- Requires reason (optional but recommended)
- Creates audit log entry

#### Adjust Modifier
Add individual probability modifier:
- Positive: Increases their chance
- Negative: Decreases their chance
- Reason required

### Bulk Operations

#### Select Multiple Users
Use checkboxes to select users, then choose:

**Mass Detect**
- Determines status for all selected users without one
- Uses current probability settings
- Progress bar for large selections

**Mass Reroll**
- Re-determines for all selected (ignores cooldown)
- Creates individual log entries
- Use with caution

**Set All as Sensitive**
- Overrides all selected to Force Sensitive
- Logs as admin action
- Reason applies to all

**Set All as Blind**
- Overrides all selected to Force Blind
- Logs as admin action
- Reason applies to all

---

## Probability Modifiers

### What Are Modifiers?

Modifiers adjust the probability for specific users, groups, or time periods.

### Modifier Types

#### Member Modifier
Applies to a single user:
- Reward for contribution
- Special circumstance
- Permanent or temporary

#### Group Modifier
Applies to all users in a group:
- VIP members get a bonus
- Specific role advantages
- Stacks with other modifiers

#### Global Modifier
Applies to all users:
- Site-wide events
- Limited time promotions
- Affects all future detections

#### Event Modifier
Time-based global modifier:
- Automatic start/end dates
- Great for special occasions
- Example: "May the 4th" bonus

### Creating a Modifier

```
AdminCP → Community → Force Sensitivity → Modifiers → Add Modifier
```

1. **Select Type**: Member, Group, Global, or Event
2. **Select Target**: (If Member or Group)
3. **Set Modifier Value**: 
   - Positive (e.g., +0.10 = +10%)
   - Negative (e.g., -0.05 = -5%)
4. **Set Active Period**: (Optional)
5. **Add Reason**: Explain why
6. **Save**

### Example Modifiers

| Type | Target | Modifier | Reason |
|------|--------|----------|--------|
| Member | JediMaster123 | +0.15 | Contest winner |
| Group | Premium Members | +0.05 | VIP bonus |
| Event | N/A (May 1-5) | +0.20 | May the 4th Event |
| Global | All | -0.02 | Rebalancing community |

---

## Viewing Audit Logs

### Accessing Logs

```
AdminCP → Community → Force Sensitivity → Audit Logs
```

### Log Information

Each entry contains:
- **Member**: Who was affected
- **Action**: What happened
- **Previous Status**: Before the action
- **New Status**: After the action
- **Performed By**: Who did it (System or Admin)
- **Timestamp**: When it happened
- **Details**: Additional context (probability used, etc.)

### Filtering Logs

- **Action Type**: Detection, Override, Reroll, Modifier Change
- **Member**: Specific user
- **Admin**: Specific administrator
- **Date Range**: Time period

### Exporting Logs

1. Set your filters
2. Click "Export"
3. Choose format (CSV or JSON)
4. Download file

### Log Retention

By default, logs are kept indefinitely. To manage storage:

```
AdminCP → Community → Force Sensitivity → Settings → Advanced
```

Set retention period:
- Forever (default)
- 1 year
- 6 months
- 3 months
- 1 month

---

## Best Practices

### Setting Initial Probability

1. **Start Conservative** (5% or less)
   - Easier to increase than decrease
   - Creates excitement when someone is FS

2. **Consider Community Size**
   - Small (<500): Lower probability okay
   - Medium (500-5000): 5-10%
   - Large (>5000): Consider ratio management

3. **Think About Theme**
   - Canon accuracy: ~1% (very rare)
   - Gameplay fun: 10-25%
   - Everyone special: 50%+

### Managing Expectations

1. **Be Transparent**
   - Document how the system works
   - Share approximate odds
   - Explain that it's random

2. **Handle Complaints**
   - "I should be Force Sensitive!"
   - Consider offering paid rerolls
   - Or special event opportunities

3. **Celebrate Both Outcomes**
   - Make Force Blind interesting too
   - Consider exclusive non-FS content
   - Both should feel valuable

### Using Modifiers Wisely

1. **Document Everything**
   - Always add reasons
   - Review active modifiers regularly
   - Remove expired ones

2. **Avoid Modifier Creep**
   - Too many modifiers = confusion
   - Keep the system simple
   - Regular audits

3. **Special Events**
   - Plan in advance
   - Announce to community
   - Limited duration

### Monitoring Health

1. **Weekly Check**
   - Review current ratio
   - Check for anomalies
   - Review recent detections

2. **Monthly Review**
   - Audit modifier list
   - Review community feedback
   - Adjust settings if needed

---

## Troubleshooting

### Common Issues

#### Detection Not Triggering

**Symptoms**: New users don't get a status

**Solutions**:
1. Verify "Enable Automatic Detection" is ON
2. Check for PHP errors in logs
3. Verify hook is installed correctly
4. Test with manual detection

#### Wrong Probability

**Symptoms**: More/fewer FS than expected

**Solutions**:
1. Check active modifiers
2. Review ratio enforcement settings
3. Verify base probability setting
4. Check for global modifiers

#### Badge Not Showing

**Symptoms**: Profile field updated but no badge

**Solutions**:
1. Verify "Show Badge" is enabled
2. Check theme compatibility
3. Clear theme cache
4. Verify CSS is loading

#### Reroll Not Working

**Symptoms**: "Cooldown active" error

**Solutions**:
1. Check cooldown setting
2. Verify time since last roll
3. Admin can bypass via direct status set

### Error Messages

| Error | Meaning | Solution |
|-------|---------|----------|
| `2FS101/1` | Invalid member | Check member ID exists |
| `1FS101/2` | Cooldown active | Wait or use override |
| `1FS102/1` | Permission denied | Check admin permissions |

### Getting Help

1. **Check Documentation**: Review this guide
2. **Community Forum**: Post in support forum
3. **GitHub Issues**: File a bug report
4. **Contact Support**: Email for critical issues

---

## Appendix: Quick Reference

### Keyboard Shortcuts (Member Management)

| Key | Action |
|-----|--------|
| `Ctrl+A` | Select all visible |
| `Ctrl+D` | Deselect all |
| `Enter` | View selected |
| `Delete` | Remove status (if permitted) |

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/forcesensitivity/members/{id}` | Get member status |
| POST | `/api/forcesensitivity/members/{id}/detect` | Trigger detection |
| PUT | `/api/forcesensitivity/members/{id}` | Update status |

### Useful Queries (Advanced)

**Count FS Users**:
```sql
SELECT COUNT(*) FROM {prefix}forcesensitivity_status 
WHERE is_force_sensitive = 1;
```

**Current Ratio**:
```sql
SELECT 
    SUM(is_force_sensitive) / COUNT(*) as ratio
FROM {prefix}forcesensitivity_status;
```

**Recent Detections**:
```sql
SELECT * FROM {prefix}forcesensitivity_log 
WHERE action = 'detection' 
ORDER BY timestamp DESC 
LIMIT 50;
```

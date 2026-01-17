# Frequently Asked Questions

> Force Sensitivity Detector - ICS v4.7.20 Extension

---

## General Questions

### What is the Force Sensitivity Detector?

The Force Sensitivity Detector is an Invision Community Suite extension that adds a Star Wars-inspired gamification element to your community. When users register (or via admin trigger), the system randomly determines if they possess "Force Sensitivity" - a special trait displayed on their profile.

### Is this extension officially associated with Star Wars?

No. This is a fan-created extension for community gamification. Star Wars and related concepts are trademarks of Lucasfilm/Disney. This extension can be renamed and reskinned for any theme.

### What ICS versions are supported?

Currently, the extension is designed for **Invision Community Suite v4.7.20**. Future versions may support newer ICS releases.

### Is this a free or paid extension?

This extension is released under the MIT License and is free to use, modify, and distribute.

---

## Installation & Setup

### How do I install the extension?

1. Download the extension package from GitHub
2. Go to AdminCP → System → Applications & Plugins
3. Click "Install" and upload the package
4. Configure settings at AdminCP → Community → Force Sensitivity

### Does installation affect existing users?

No. Existing users will have "Not Determined" status until you:
- Manually trigger detection via Admin
- Use bulk operations to detect existing users
- Wait for them to have any account action (optional future feature)

### Can I uninstall cleanly?

Yes. The uninstall routine removes:
- All database tables
- Profile field (optional)
- All settings

User accounts themselves are never affected.

---

## Configuration

### What's a good base probability to start with?

**5% (0.05)** is recommended for most communities. This means roughly 1 in 20 users will be Force Sensitive, making it special but not extremely rare.

| Community Vibe | Probability | Result |
|---------------|-------------|--------|
| Very Exclusive | 1% | 1 in 100 users |
| Rare & Special | 5% | 1 in 20 users |
| Common Gift | 15% | ~1 in 7 users |
| Half & Half | 50% | 1 in 2 users |

### What does "ratio enforcement" do?

Ratio enforcement adjusts probability to maintain a target percentage of Force Sensitive users.

- **None**: Pure random, no adjustments
- **Soft**: Gentle adjustments toward target
- **Hard**: Aggressive adjustments to hit target

**Example**: Target 10%, currently at 15%
- None: Keep rolling at 5%
- Soft: Reduce probability slightly
- Hard: Significantly reduce probability

### Can I change settings after users are detected?

Yes! Settings changes affect **future** detections only. Existing statuses remain unchanged unless you:
- Manually override
- Bulk reroll

---

## Member Management

### How do I make a specific user Force Sensitive?

1. Go to AdminCP → Community → Force Sensitivity → Members
2. Find the user
3. Click Actions → Set as Sensitive
4. (Optional) Add a reason

### Can users change their own status?

By default, no. Only administrators can modify Force Sensitivity status. A future version may include user self-reroll options.

### What's the difference between "Detect" and "Set Status"?

- **Detect**: Runs the probability roll (may or may not result in Force Sensitive)
- **Set Status**: Directly assigns a status, bypassing probability

### What is the reroll cooldown?

A waiting period before a user can be re-detected. This prevents:
- Admin abuse
- Repeated rolling until desired result
- Log flooding

Default: 24 hours. Admins can directly set status to bypass.

---

## Probability & Modifiers

### How is final probability calculated?

```
Final = clamp(
    Base Probability
    + Ratio Adjustment
    + Member Modifier
    + Group Modifier  
    + Event Modifier
    + Custom Modifiers
, Min, Max)
```

### Can I give VIP members better odds?

Yes! Create a **Group Modifier**:
1. Go to Modifiers → Add Modifier
2. Type: Group
3. Target: Your VIP group
4. Modifier: +0.10 (adds 10%)

### How do event modifiers work?

Event modifiers are time-limited probability boosts:
1. Create modifier with start/end dates
2. System automatically applies during that period
3. Great for holidays ("May the 4th" bonus!)

### Why isn't the exact ratio being maintained?

Ratio enforcement uses a **window** (last N users), not all-time data. This:
- Responds to recent trends
- Doesn't heavily penalize for historical imbalance
- Creates natural fluctuation

For strict ratio: Use "Hard" enforcement with larger window.

---

## Display & Integration

### Where does the badge appear?

Depending on settings:
- User profile page
- User hover card
- Forum posts (next to username)
- Member directory

### Can I customize the badge appearance?

Yes! Three built-in styles:
- **Simple**: Plain text
- **Glow**: Subtle glow effect
- **Animated**: Moving animation

Custom CSS can further modify appearance.

### Can I use different terms instead of "Force Sensitive"?

Absolutely! Configure custom labels in Settings:
- Sensitive Label: "Jedi Potential", "Gift of Sight", etc.
- Blind Label: "Mundane", "Ordinary", etc.

### Does this integrate with the ICS points system?

Not in v1.0. Planned for future versions:
- Award points on detection
- Spend points for reroll
- FS-only rewards

---

## Technical Questions

### What data is stored?

The extension creates three tables:
- `forcesensitivity_status`: Current status per user
- `forcesensitivity_log`: Audit trail of all actions
- `forcesensitivity_modifiers`: Probability modifiers

### Does this slow down registration?

Minimally. The detection process adds approximately 5-10ms to registration. This includes:
- Probability calculation
- Random number generation
- Database writes

### Is the random number generator fair?

Yes. We use PHP's `random_int()` which provides cryptographically secure random numbers, ensuring truly random and unbiased results.

### Can I access this via API?

Yes! REST API endpoints:
- `GET /api/forcesensitivity/members/{id}`: Get status
- `POST /api/forcesensitivity/members/{id}/detect`: Trigger detection

Requires API authentication.

---

## Troubleshooting

### Detection isn't happening on registration

1. Check Settings → "Enable Automatic Detection" is ON
2. Check PHP error logs
3. Verify the hook is installed (AdminCP → System → Plugins → Hooks)
4. Test with manual detection

### Probability seems wrong

1. Check for active modifiers affecting probability
2. Verify ratio enforcement settings
3. Review the audit log for actual probability used
4. Remember: randomness can appear "streaky"

### Badge not showing

1. Verify "Show Badge" is enabled
2. Check profile field is properly linked
3. Clear theme cache
4. Check for theme CSS conflicts

### Getting "Permission Denied" errors

1. Check admin group permissions for Force Sensitivity
2. Verify you're logged in as an admin
3. Check session hasn't expired

---

## Still Have Questions?

- 📖 Read the [Admin Guide](docs/ADMIN_GUIDE.md)
- 🐛 [Report an Issue](https://github.com/jimstratus/ICS-ext-forcesensitivity/issues)
- 💬 [Community Discussion](https://github.com/jimstratus/ICS-ext-forcesensitivity/discussions)

---

*May the Force be with you!*

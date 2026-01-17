<?php
/**
 * Force Sensitivity Detector - Language Strings
 * 
 * @package     IPS\forcesensitivity
 * @subpackage  dev
 * @author      jimstratus
 * @copyright   2026
 * @license     MIT
 */

$lang = [
    // Application
    '__app_forcesensitivity' => 'Force Sensitivity',
    '__app_forcesensitivity_desc' => 'Determines if users are Force Sensitive through probability-based detection',
    
    // ACP Menu
    'menu__forcesensitivity' => 'Force Sensitivity',
    'menu__forcesensitivity_forcesensitivity' => 'Force Sensitivity',
    'menu__forcesensitivity_forcesensitivity_settings' => 'Settings',
    'menu__forcesensitivity_forcesensitivity_members' => 'Members',
    'menu__forcesensitivity_forcesensitivity_modifiers' => 'Modifiers',
    'menu__forcesensitivity_forcesensitivity_logs' => 'Audit Logs',
    
    // Settings Page
    'fs_settings_title' => 'Force Sensitivity Settings',
    'fs_tab_detection' => 'Detection',
    'fs_tab_ratio' => 'Ratio Management',
    'fs_tab_admin' => 'Admin Controls',
    'fs_tab_display' => 'Display',
    'fs_tab_advanced' => 'Advanced',
    
    // Detection Settings
    'fs_detection_settings' => 'Detection Settings',
    'fs_detection_enabled' => 'Enable Automatic Detection',
    'fs_detection_enabled_desc' => 'Automatically determine Force Sensitivity when users register',
    
    // Probability Settings
    'fs_probability_settings' => 'Probability Settings',
    'fs_base_probability' => 'Base Probability',
    'fs_base_probability_desc' => 'The base chance that a user will be Force Sensitive. 5% means approximately 1 in 20 users.',
    'fs_min_probability' => 'Minimum Probability',
    'fs_min_probability_desc' => 'The lowest possible probability, even with negative modifiers applied.',
    'fs_max_probability' => 'Maximum Probability',
    'fs_max_probability_desc' => 'The highest possible probability, even with positive modifiers applied.',
    
    // Ratio Settings
    'fs_ratio_settings' => 'Ratio Management',
    'fs_target_ratio' => 'Target Ratio',
    'fs_target_ratio_desc' => 'The desired percentage of Force Sensitive users in your community.',
    'fs_ratio_enforcement' => 'Ratio Enforcement Mode',
    'fs_ratio_enforcement_desc' => 'How aggressively should the system adjust probability to meet the target ratio?',
    'fs_ratio_none' => 'None (Pure Random)',
    'fs_ratio_soft' => 'Soft (Gradual Adjustment)',
    'fs_ratio_hard' => 'Hard (Aggressive Adjustment)',
    'fs_ratio_window' => 'Ratio Calculation Window',
    'fs_ratio_window_desc' => 'Number of recent users to consider when calculating the current ratio. Set to 0 to use all users.',
    'fs_auto_adjust' => 'Auto-Adjust Probability',
    'fs_auto_adjust_desc' => 'Automatically adjust probability based on current ratio vs target ratio.',
    'fs_users' => 'users',
    
    // Admin Settings
    'fs_admin_settings' => 'Admin Controls',
    'fs_admin_override' => 'Allow Admin Override',
    'fs_admin_override_desc' => 'Allow administrators to directly set Force Sensitivity status, bypassing probability.',
    'fs_reroll_enabled' => 'Allow Reroll',
    'fs_reroll_enabled_desc' => 'Allow re-determining a user\'s Force Sensitivity status.',
    'fs_reroll_cooldown' => 'Reroll Cooldown',
    'fs_reroll_cooldown_desc' => 'Time (in seconds) before a user can be re-rolled again.',
    'fs_no_cooldown' => 'No Cooldown',
    'fs_seconds' => 'seconds',
    'fs_bulk_operations' => 'Enable Bulk Operations',
    'fs_bulk_operations_desc' => 'Allow bulk detection, reroll, and status changes for multiple users.',
    
    // Display Settings
    'fs_display_settings' => 'Display Settings',
    'fs_show_badge' => 'Show Badge on Profiles',
    'fs_show_badge_desc' => 'Display a Force Sensitivity badge on user profiles.',
    'fs_badge_style' => 'Badge Style',
    'fs_badge_style_desc' => 'Visual style for the Force Sensitivity badge.',
    'fs_badge_simple' => 'Simple',
    'fs_badge_glow' => 'Glowing',
    'fs_badge_animated' => 'Animated',
    'fs_show_in_posts' => 'Show in Forum Posts',
    'fs_show_in_posts_desc' => 'Display a Force Sensitivity indicator next to usernames in forum posts.',
    
    // Label Settings
    'fs_label_settings' => 'Custom Labels',
    'fs_sensitive_label' => 'Force Sensitive Label',
    'fs_sensitive_label_desc' => 'Custom label for Force Sensitive users. Leave blank for default.',
    'fs_blind_label' => 'Force Blind Label',
    'fs_blind_label_desc' => 'Custom label for Force Blind (non-sensitive) users. Leave blank for default.',
    
    // Advanced Settings
    'fs_advanced_settings' => 'Advanced Settings',
    'fs_log_retention' => 'Log Retention Period',
    'fs_log_retention_desc' => 'Number of days to keep audit logs. Set to 0 to keep forever.',
    'fs_keep_forever' => 'Keep Forever',
    'fs_days' => 'days',
    
    // Status Labels
    'fs_status_sensitive' => 'Force Sensitive',
    'fs_status_blind' => 'Force Blind',
    'fs_status_unknown' => 'Not Determined',
    
    // Detection Methods
    'fs_method_registration' => 'Registration',
    'fs_method_admin' => 'Admin',
    'fs_method_reroll' => 'Reroll',
    'fs_method_event' => 'Event',
    'fs_method_api' => 'API',
    
    // Members Page
    'fs_members_title' => 'Force Sensitivity - Members',
    'fs_member_id' => 'Member',
    'fs_is_force_sensitive' => 'Status',
    'fs_detection_date' => 'Detection Date',
    'fs_detection_method' => 'Method',
    'fs_probability_used' => 'Probability',
    'fs_filter_sensitive' => 'Force Sensitive Only',
    'fs_filter_blind' => 'Force Blind Only',
    'fs_deleted_member' => 'Deleted Member',
    
    // Member Actions
    'fs_detect_now' => 'Detect Now',
    'fs_reroll' => 'Reroll',
    'fs_reroll_confirm' => 'Are you sure you want to reroll this user\'s Force Sensitivity? This will replace their current status.',
    'fs_set_sensitive' => 'Set as Sensitive',
    'fs_set_blind' => 'Set as Blind',
    'fs_view_history' => 'View History',
    'fs_member_history' => 'Force Sensitivity History',
    
    // Bulk Actions
    'fs_bulk_reroll' => 'Reroll Selected',
    'fs_bulk_set_sensitive' => 'Set All as Sensitive',
    'fs_bulk_set_blind' => 'Set All as Blind',
    'fs_bulk_complete' => 'Bulk operation completed for %d members.',
    'fs_detect_undetermined' => 'Detect All Undetermined',
    'fs_detect_undetermined_confirm' => 'This will detect Force Sensitivity for all members who don\'t have a status yet. Continue?',
    'fs_detected_count' => 'Force Sensitivity detected for %d members.',
    
    // Modifiers Page
    'fs_modifiers_title' => 'Force Sensitivity - Modifiers',
    'fs_add_modifier' => 'Add Modifier',
    'fs_edit_modifier' => 'Edit Modifier',
    'fs_modifier_type' => 'Type',
    'fs_modifier_target' => 'Target',
    'fs_modifier_value' => 'Modifier Value',
    'fs_modifier_reason' => 'Reason',
    'fs_modifier_start' => 'Start Date',
    'fs_modifier_end' => 'End Date',
    'fs_modifier_active' => 'Active',
    
    // Modifier Types
    'fs_modifier_type_member' => 'Member',
    'fs_modifier_type_group' => 'Group',
    'fs_modifier_type_global' => 'Global',
    'fs_modifier_type_event' => 'Event',
    'fs_all_members' => 'All Members',
    'fs_event' => 'Event',
    'fs_deleted_group' => 'Deleted Group',
    
    // Modifier Form
    'fs_modifier_member' => 'Select Member',
    'fs_modifier_group' => 'Select Group',
    'fs_error_member_required' => 'Please select a member for member-type modifiers.',
    'fs_error_group_required' => 'Please select a group for group-type modifiers.',
    
    // Modifier Status
    'fs_active' => 'Active',
    'fs_inactive' => 'Inactive',
    'fs_activate' => 'Activate',
    'fs_deactivate' => 'Deactivate',
    'fs_immediately' => 'Immediately',
    'fs_never' => 'Never',
    'fs_from' => 'From',
    'fs_until' => 'Until',
    'fs_always' => 'Always Active',
    
    // Modifier Filters
    'fs_filter_active' => 'Active Only',
    'fs_filter_inactive' => 'Inactive Only',
    'fs_filter_member' => 'Member Modifiers',
    'fs_filter_group' => 'Group Modifiers',
    'fs_filter_global' => 'Global Modifiers',
    'fs_filter_event' => 'Event Modifiers',
    
    // Logs Page
    'fs_logs_title' => 'Force Sensitivity - Audit Logs',
    'fs_log_timestamp' => 'Timestamp',
    'fs_log_member_id' => 'Member',
    'fs_log_action' => 'Action',
    'fs_log_old_value' => 'Previous',
    'fs_log_new_value' => 'New',
    'fs_log_performed_by' => 'Performed By',
    'fs_log_ip_address' => 'IP Address',
    'fs_log_details' => 'Details',
    'fs_view_details' => 'View Details',
    'fs_system' => 'System',
    
    // Log Actions
    'fs_log_action_detection' => 'Detection',
    'fs_log_action_admin_override' => 'Admin Override',
    'fs_log_action_reroll' => 'Reroll',
    'fs_log_action_bulk_operation' => 'Bulk Operation',
    'fs_log_action_settings_changed' => 'Settings Changed',
    'fs_log_action_modifier_added' => 'Modifier Added',
    'fs_log_action_modifier_removed' => 'Modifier Removed',
    
    // Log Filters
    'fs_filter_detection' => 'Detections Only',
    'fs_filter_override' => 'Overrides Only',
    'fs_filter_reroll' => 'Rerolls Only',
    'fs_filter_bulk' => 'Bulk Operations Only',
    
    // Log Export
    'fs_export_csv' => 'Export as CSV',
    'fs_export_json' => 'Export as JSON',
    'fs_prune_logs' => 'Prune Old Logs',
    'fs_prune_days' => 'Delete logs older than',
    'fs_days_old' => 'days',
    'fs_logs_pruned' => '%d log entries deleted.',
    'fs_log_not_found' => 'Log entry not found.',
    
    // Settings Actions
    'fs_export_settings' => 'Export Settings',
    'fs_import_settings' => 'Import Settings',
    'fs_import_file' => 'Settings File (JSON)',
    'fs_reset_defaults' => 'Reset to Defaults',
    'fs_reset_confirm' => 'Are you sure you want to reset all settings to their default values?',
    
    // Success Messages
    'fs_settings_saved' => 'Settings saved successfully.',
    'fs_settings_reset' => 'Settings reset to defaults.',
    'fs_settings_imported' => 'Settings imported successfully.',
    'fs_detected_sensitive' => 'User determined to be Force Sensitive!',
    'fs_detected_blind' => 'User determined to be Force Blind.',
    'fs_rerolled_sensitive' => 'Reroll complete: User is Force Sensitive!',
    'fs_rerolled_blind' => 'Reroll complete: User is Force Blind.',
    'fs_set_sensitive_success' => 'User status set to Force Sensitive.',
    'fs_set_blind_success' => 'User status set to Force Blind.',
    'fs_modifier_saved' => 'Modifier saved successfully.',
    'fs_modifier_activated' => 'Modifier activated.',
    'fs_modifier_deactivated' => 'Modifier deactivated.',
    'fs_modifier_deleted' => 'Modifier deleted.',
    
    // Error Messages
    'fs_invalid_member' => 'Invalid member specified.',
    'fs_override_disabled' => 'Admin override is currently disabled.',
    'fs_modifier_not_found' => 'Modifier not found.',
    'fs_import_invalid_json' => 'Invalid JSON file. Please upload a valid settings export.',
    'fs_cooldown_active' => 'Reroll cooldown is still active for this user.',
    
    // Installation Messages
    'fs_install_status_table' => 'Created status table.',
    'fs_install_log_table' => 'Created audit log table.',
    'fs_install_modifiers_table' => 'Created modifiers table.',
    'fs_install_field_exists' => 'Found existing Force Sensitivity profile field.',
    'fs_install_field_manual' => 'Profile field can be configured in settings.',
    'fs_install_settings' => 'Default settings configured.',
    
    // Uninstall Messages
    'fs_uninstall_status_table' => 'Removed status table.',
    'fs_uninstall_log_table' => 'Removed audit log table.',
    'fs_uninstall_modifiers_table' => 'Removed modifiers table.',
    'fs_uninstall_settings' => 'Removed application settings.',
    'fs_uninstall_complete' => 'Uninstallation complete.',
    
    // Member Filter
    'fs_filter_status' => 'Force Sensitivity',
    'fs_filter_any' => 'Any Status',
    'fs_filter_undetermined' => 'Not Determined',
    
    // Permissions
    'fs_settings_manage' => 'Can manage Force Sensitivity settings',
    'fs_members_view' => 'Can view Force Sensitivity member list',
    'fs_members_manage' => 'Can manage member Force Sensitivity',
    'fs_modifiers_view' => 'Can view Force Sensitivity modifiers',
    'fs_modifiers_manage' => 'Can manage Force Sensitivity modifiers',
    'fs_logs_view' => 'Can view Force Sensitivity logs',
    'fs_logs_prune' => 'Can prune Force Sensitivity logs',
    
    // Dashboard Widget
    'fs_dashboard_title' => 'Force Sensitivity Overview',
    'fs_dashboard_total' => 'Total Determined',
    'fs_dashboard_sensitive' => 'Force Sensitive',
    'fs_dashboard_blind' => 'Force Blind',
    'fs_dashboard_ratio' => 'Current Ratio',
    'fs_dashboard_target' => 'Target Ratio',
    'fs_dashboard_modifiers' => 'Active Modifiers',
];

<?php

$prefix = (env('DB_PREFIX', '') != '') ? env('DB_PREFIX', '') . '_' : '';
return [
    'users'         => $prefix . 'users',
    'roles'         => $prefix . 'roles',
    'verifications' => $prefix . 'verifications',

    // OTHERS
    'email_templates' => $prefix . 'email_tem' . 'plates',
    'otp_templates'   => $prefix . 'otp_tem' . 'plates',
    
    'profiles'        => $prefix . 'profiles',
    'sites'           => $prefix . 'sites',
    'check_schedules' => $prefix . 'check_schedules',
    'check_logs'      => $prefix . 'check_logs',

    'ai_assessments'      => $prefix . 'ai_assessments',
    'ai_assessment_files' => $prefix . 'ai_assessment_files',
    'case_journals'       => $prefix . 'case_journals',
    'case_journal_files'  => $prefix . 'case_journal_files',

    'site_settings' => $prefix . 'site_settings',

    'faqs'          => $prefix . 'faqs',
    'cms_pages'     => $prefix . 'cms_pages',
    'sections'      => $prefix . 'sections',

    'plans'         => $prefix . 'plans',
    'plan_features' => $prefix . 'plan_features',
    'blogs'         => $prefix . 'blogs',

    /* Post */
    'posts'             => $prefix . 'posts',
    'categories'        => $prefix . 'categories',
    'category_post'     => $prefix . 'category_post',
    'menus'             => $prefix . 'menus',
    'menu_items'        => $prefix . 'menu_items',
    'comments'          => $prefix . 'comments',
    'section_templates' => $prefix . 'section_templates',
    'post_meta'        => $prefix . 'post_meta',

    /* Deployment Checklist */
    'tech_stacks'         => $prefix . 'tech_stacks',
    'projects'            => $prefix . 'projects',
    'checklist_categories' => $prefix . 'checklist_categories',
    'checklist_items'     => $prefix . 'checklist_items',
    'project_checklists'  => $prefix . 'project_checklists',
    'shared_reports'      => $prefix . 'shared_reports',
    'project_users'       => $prefix . 'project_users',

    /* Assessments */
    'assessments'            => $prefix . 'assessments',
    'assessment_checklists'  => $prefix . 'assessment_checklists',
    'assessment_users'       => $prefix . 'assessment_users',

];

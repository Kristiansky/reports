<?php
    
    return [
        
        'main_navigation'               => 'MAIN NAVIGATION',
        'blog'                          => 'Blog',
        'pages'                         => 'Pages',
        'account_settings'              => 'ACCOUNT SETTINGS',
        'profile'                       => 'Profile',
        'change_password'               => 'Change Password',
        'multilevel'                    => 'Multi Level',
        'level_one'                     => 'Level 1',
        'level_two'                     => 'Level 2',
        'level_three'                   => 'Level 3',
        'labels'                        => 'LABELS',
        'important'                     => 'Important',
        'warning'                       => 'Warning',
        'information'                   => 'Information',
        'english' => 'English',
        'bulgarian' => 'Български',
        'locale' => 'ЛОКАЛИЗАЦИЯ',
        'dashboard' => 'Табло',
        'current_client' => session('client') ? session('client')->NumeClient1 .' ['. session('client')->group->name . ']' : '',
    ];

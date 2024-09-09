<?php
  return [
    'perpage_showdata' => 10,
    
    'roles' => [
        1 => "Super Admin",
        11 => "Admin",
        13 => "Owner",

        21 => "Doctor",
        22 => "Pharmacist",
        23 => "CCM Coordinator",
        24 => "Team Lead"
    ],

  	'menu' => [
         
        [
            'label'=> 'Users',
            'route'=> 'users',
            'access_roles'=> [1,21,22,23] 
        ],
        [
            'label'=> 'Specialists',
            'route'=> 'specialists',
            'access_roles'=> [1,21,22,23] 
        ],
        [
            'label'=> 'Insurances',
            'route'=> 'insurances',
            'access_roles'=> [1,21,22,23] 
        ],
        [
            'label'=> 'Patients',
            'route'=> 'patients',
            'access_roles'=> [1,2,3] 
        ],
        [
            'label'=> 'Programs',
            'route'=> 'programs',
            'access_roles'=> [1,2,3] 
        ],
        [
            'label'=> 'Question Survey',
            'route'=> 'questionaires-survey',
            'access_roles'=> [1,2,3] 
        ],
        [
            'label'=> 'Physicians',
            'route'=> 'physicians',
            'access_roles'=> [1,21,22,23,24,25] 
        ],
        [
            'label'=> 'Clinics',
            'route'=> 'clinics',
            'access_roles'=> [1,21,22,23] 
        ],
        [
            'label'=> 'Clinic Admins',
            'route'=> 'clinicAdmins',
            'access_roles'=> [1,21,22,23] 
        ],
  	],

    'agree_options' => ['Yes','No'],
    
    'condition_options' => ['Normal','Abnormal'],

    'physical_intense' => [
        'light' => 'Light (like stretching or slow walking)',
        'moderate' => 'Moderate (like stretching or slow walking)',
        'heavy' => 'Heavy (like stretching or slow walking)',
        'veryheavy' => 'Very Heavy (like stretching or slow walking)',
        'noexercise' => 'I am currently not exercising'
    ],

    'follow_up_cardio' => [
        'following_up' => 'Patient is following up as recommended by cardiologist',
        'not_following_up' => 'Patient is not following up per recommendation, advised to set up and appointment with cardiologist'
    ],

    'not_follow_up_cardio' => [
        'chf_is_controlled' => 'CHF is controlled and cardiology wants patient to follow up as needed',
        'patient_does_not_have_cardiologist' => 'Patient does not have a cardiologist as of now, will set up with cardiology'
    ],

    'no_echodiogram' =>[
        'patient_adviced' => 'Patient advised on importance of echocardiograms done every 1-2 years to evaluate heart function in patients with CHF. Patient agrees to get echocardiogram done.',
        'patient_refused' => 'Patient refused to get echocardiogram at this time. Patient advised in detail on the possible complications of not following up regularly to evaluate heart function.'
    ],

    'alcohol_average_use' => [
        'never' => 'Never',
        'once_per_week' => 'Once during the week',
        '2_3_times_per_week' => '2–3 times during the week',
        'more_then_three_per_week' => 'More than 3 times during the week'
    ],

    'tobacco_alternate' => [
        'wellbutrin' => 'Wellbutrin',
        'chantix' => 'Chantix',
        'nicotine_patches' => 'Nicotine Patches',
        'no' => 'Refused'
    ],

    'tobacco_alternate_qty' => [
        '21_mg_per_hour' => '21 mg/hr ',
        '14_mg_per_hour' => '14 mg/hr ',
        '7_mg_per_hour' => '7 mg/hr '
    ],

    'depression_phq_9' => [
        'Almost all of the time' => 3,
        'Most of the time' => 2,
        'Some of the time' => 1,
        'Almost never' => 0
    ],

    'problem_difficulty' => [
        'extremely_difficult' => 'Extremely difficult',
        'very_difficult' => 'Very difficult',
        'somewhat_difficult' => 'Somewhat difficult',
        'not_difficult_at_all' => 'Not difficult at all'
    ],

    'high_stress' => [
        'never_or_rarely' => 'Never or Rarely',
        'sometimes' => 'Sometimes',
        'often' => 'Often',
        'always' => 'Always'
    ],
    
    'general_health' => [
        'excellent' => 'Excellent',
        'very_good' => 'Very Good',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor'
    ],

    'social_emotional_support' => [
        'always' => 'Always',
        'usually' => 'Usually',
        'sometimes' => 'Sometimes',
        'rarely' => 'Rarely',
        'never' => 'Never'
    ],
    
    'pain' => [
        'none' => 'None',
        'some' => 'Some',
        'a_lot' => 'Alot'
    ],
    
    'fall_screening' => [
        'one' => 'One',
        'more_then_one' => 'More then one',
        'do_not_remember' => 'Do not remember'
    ],
    
    'physical_therapy' => [
        'referred' => 'Referred',
        'refused' => 'Refused',
        'already_receiving' => 'Already receiving'
    ],
    
    'assistance_device' => [
        'cane' => 'Cane',
        'walker' => 'Walker',
        'wheel_char' => 'Wheel Chair',
        'crutches' => 'Crutches',
        'none' => 'None'
    ],
    
    'diabetec_eye_exam_report' => [
        'Report Requested' => 'report_requested',
        'Patient will call with the name of the doctor to request report' => 'patient_call_doctor',
        'Report available' => 'report_available'
    ],
    
    'urine_microalbumin_report' => ['Positive', 'Negative'],
    
    'inhibitor' => [
        'ACE Inhibitor' => 'ace_inhibitor', 
        'ARB' => 'arb',
        'None' => 'none'
    ],
    
    'ckd_stage_4_options' => [
        'CKD Stage 4' => 'ckd_stage_4', 
        'Patient see Nephrologist' => 'patient_see_nephrologist'
    ],

    'statin_medical_reason' => [
        'Adverse side effect',
        'Allergy, Acute liver disease/ Hepatic insufficiency',
        'ESRD',
        'Rhabdomyolysis',
        'Pregnancy/Breastfeeding',
        'In Hospice',
    ],

    'statin_name' => [
        'Atorvastatin',
        'Fluvastatin',
        'Lovastatin',
        'Pitavastatin',
        'Pravastatin',
        'Rosuvastatin',
        'Simvastatin',
    ],
    
    'moderate_intensity_statin' => [
        'Atorvastatin' => '10 to 20 mg',
        'Fluvastatin' => '40 mg 2×/day; XL 80 mg',
        'Lovastatin' => '40 mg',
        'Pitavastatin' => '2 to 4 mg',
        'Pravastatin' => '40 to 80 mg',
        'Rosuvastatin' => '5 to 10 mg',
        'Simvastatin' => '20 to 40 mg',
    ],

    'high_intensity_statin' => [
        'Atorvastatin' => '40 to 80 mg',
        'Fluvastatin' => '',
        'Lovastatin' => '',
        'Pitavastatin' => '',
        'Pravastatin' => '',
        'Rosuvastatin' => '20 to 40 mg',
        'Simvastatin' => '',
    ],

    'colon_test_type' => [
        'Colonoscopy',
        'FIT Test',
        'Cologuard',
    ],

    'error_options_a' => ['correct', 'incorrect'],
    
    'error_options_b' => ['correct', '1 error', 'more than 1 error'],
    
    'error_options_c' => [
        '0'=> 'correct',
        '2'=> '1 error',
        '4'=> '2 errors',
        '6'=> '3 errors',
        '8'=> '4 errors',
        '10'=> 'All wrong'
    ],
    
    'sections' => [
        1 => [
            [
                'step_no' => 1,
                'label' => 'Physical Health-Fall Screening',
                'section_file' => 'physical-health-fall-screen',
                'database_variable' => 'fall_screening',
            ],
            [
                'step_no' => 2,
                'label' => 'Depression PHQ-9',
                'section_file' => 'depression-phq9',
                'database_variable' => 'depression_phq9',
            ],
            [
                'step_no' => 3,
                'label' => 'High Stress',
                'section_file' => 'high-stress',
                'database_variable' => 'high_stress',
            ],
            [
                'step_no' => 4,
                'label' => 'General Health',
                'section_file' => 'general-health',
                'database_variable' => 'general_health',
            ],
            [
                'step_no' => 5,
                'label' => 'Social/Emotional Support',
                'section_file' => 'social-emotional-support',
                'database_variable' => 'social_emotional_support',
            ],
            [
                'step_no' => 6,
                'label' => 'Pain',
                'section_file' => 'pain',
                'database_variable' => 'pain',
            ],
            [
                'step_no' => 7,
                'label' => 'Cognitive Assesment',
                'section_file' => 'physical-health-fall-screen',
                'database_variable' => 'fall_screening',
            ],
            [
                'step_no' => 8,
                'label' => 'Physical Activity',
                'section_file' => 'physical-health-fall-screen',
                'database_variable' => 'fall_screening',
            ],
            [
                'step_no' => 9,
                'label' => 'Alcohol Use',
                'section_file' => 'physical-health-fall-screen',
                'database_variable' => 'fall_screening',
            ],
            [
                'step_no' => 1,
                'label' => 'Tobacco Use',
                'section_file' => 'physical-health-fall-screen',
                'database_variable' => 'fall_screening',
            ],
            [
                'step_no' => 1,
                'label' => 'Physical Health-Fall Screening',
                'section_file' => 'physical-health-fall-screen',
                'database_variable' => 'fall_screening',
            ],
        ],
        
        
    ],
    
    "chronic_diseases" => [
        "Depression" => ["F32.0", "F32.1", "F32.2", "F32.3", "F32.4", "F32.5", "F33.0", "F33.1", "F33.2", "F33.3", "F33.40", "F33.41", "F33.42", "F33.9", "F39"],
        "CongestiveHeartFailure" => ["I50.9", "I50.30", "I50.22", "I50.32", "I11.0", "I50.20"],
        "ChronicObstructivePulmonaryDisease" => ["J44.9", "J44.1", "J44.0"],
        "CKD" => ["N18.1", "N18.2", "N18.30", "N18.31", "N18.32", "N18.4", "N18.5", "N18.9", "I13.0"],
        "DiabetesMellitus" => ["E11.9", "E11.21", "E11.22", "E11.29", "E11.39", "E11.311", "E11.319", "E11.3211", "E11.3291", "E11.3293", "E11.36", "E11.39", "E11.40", "E11.41", "E11.42", "E11.49", "E11.51", "E11.52", "E11.59", "E11.618", "E11.620", "E11.621", "E11.622", "E11.628", "E11.649", "E11.65", "E11.8", "E11.638", "E11.3599", "E11.3211", "E113292"],
        "Hypertensions" => ["I10", "I11.0", "I11.9", "I13.0", "I13.10", "I13.11", "I13.2"],
        "Obesity" => ["E66.01", "E66.2", "E66.9", "Z68.41", "Z68.42", "Z68.43", "Z68.44", "Z68.45"],
        "Hypercholesterolemia" => ["E78.00", "E78.01", "E78.1", "E78.2", "E78.3", "E78.41", "E78.49", "E78.5", "E78.9"],
        "Anemia" => ["D59.0", "D59.10", "D59.12", "D59.13", "D59.19", "D59.1", "D59.5", "D59.9", "D61.9", "D61.818", "D64.0", "D64.1", "D64.2", "D64.3"],
        "Hyperthyrodism" => ["E02", "E03", "E03.0", "E03.1", "E03.9", "E03.3", "E03.8", "E03.2", "E89.0"],
        "Asthma" => ["J45.20", "J45.3", "J45.4", "J45.5", "J45.90", "J45.909", "J45.99"],
    ],


    "diseases_steps" => [
        "hypercholesterolemia" => "9",
        "diabetes_mellitus" => "10",
        "chronic_obstructive_pulmonary_disease" => "11",
        "chronic_kidney_disease" => "12",
        "hypertension" => "13",
        "obesity" => "14",
        "congestive_heart_failure" => "15"
    ],

    'caregaps' => [
        'hcpw-001' => [
            'breast_cancer_gap',
            'colorectal_cancer_gap',
            'eye_exam_gap',
            'hba1c_poor_gap',
            'high_bp_gap',
            'statin_therapy_gap',
            'osteoporosis_mgmt_gap',
            'adults_medic_gap',
            'pain_screening_gap',
            'post_disc_gap',
            'adults_func_gap',
            'after_inp_disc_gap',
            'awv_gap',
        ],

        //humana
        'hum-001' => [
            'breast_cancer_gap',
            'colorectal_cancer_gap',
            'high_bp_gap',
            'eye_exam_gap',
            'faed_visit_gap',
            'hba1c_poor_gap',
            'omw_fracture_gap',
            'pc_readmissions_gap',
            'spc_disease_gap',
            'post_disc_gap',
            'after_inp_disc_gap',
            'ma_cholesterol_gap',
            'mad_medications_gap',
            'ma_hypertension_gap',
            'sup_diabetes_gap',
            'awv_gap',
        ],

        // Aetna
        'aet-001' => [
            'breast_cancer_gap',
            'colorectal_cancer_gap',
            'eye_exam_gap',
            'hba1c_gap',
            'spc_disease_gap',
            'faed_visit_gap',
            'mad_medications_gap',
            'ma_hypertension_gap',
            'sup_diabetes_gap',
            'ma_cholesterol_gap',
            'omw_fracture_gap',
            'pc_readmissions_gap',
            'awv_gap',
        ],

        // AllWell
        'allwell-001' => [
            'breast_cancer_gap',
            'high_bp_gap',
            'eye_exam_gap',
            'hba1c_gap',
            'pain_screening_gap',
            'adults_medic_gap',
            'colorectal_cancer_gap',
            'm_high_risk_cc_gap',
            'med_adherence_diabetic_gap',
            'med_adherence_ras_gap',
            'med_adherence_statins_gap',
            'spc_statin_therapy_cvd_gap',
            'sup_diabetes_gap',
            'trc_eng_after_disc_gap',
            'trc_mr_post_disc_gap',
            'kidney_health_diabetes_gap',
            'awv_gap',
        ],
        
        // HCA
        'hcarz-001' => [
            'breast_cancer_gap',
            'cervical_cancer_gap',
            'opioids_high_dosage_gap',
            'hba1c_poor_gap',
            'ppc1_gap',
            'ppc2_gap',
            'well_child_visits_gap',
            'chlamydia_gap',
            'high_bp_gap',
            'fuh_30Day_gap',
            'fuh_7Day_gap',
            'awv_gap',
        ],
        
        // MARZ
        'med-arz-001' => [
            'breast_cancer_gap',
            'colorectal_cancer_gap',
            'high_bp_gap',
            'hba1c_gap',
            'awv_gap',
        ],
        
        // UHC Medicare
        'uhc-001' => [
            'breast_cancer_gap',
            'colorectal_cancer_gap',
            'adults_medic_gap',
            'adults_fun_status_gap',
            'pain_screening_gap',
            'eye_exam_gap',
            'kidney_health_diabetes_gap',
            'hba1c_gap',
            'high_bp_gap',
            'statin_therapy_gap',
            'med_adherence_diabetes_gap',
            'med_adherence_ras_gap',
            'med_adherence_statins_gap',
            'mtm_cmr_gap',
            'sup_diabetes_gap',
            'awv_gap',
        ]

    ]
  ]
?>
<?php
/**
 * NexClinic workspace shell — role-based experience architecture.
 * Spec: docs/NEXCLINIC_WORKSPACES.md (MANDATORY, 2026-08-07).
 *
 * Each role owns a workspace: its own dashboard, sidebar, homepage, quick
 * actions and accent colour. A workspace's sidebar contains ONLY that
 * workspace's items — other workspaces' entries are never emitted, not
 * CSS-hidden. Hiding menu items is explicitly forbidden by the spec.
 *
 * This file holds the resolution + navigation data. Rendering lives in
 * backend/admin/assets/inc/sidebar.php, which reads nex_workspace_nav().
 *
 * Adding a workspace = one entry in nex_workspace_catalogue() plus one
 * case in nex_workspace_nav(). Nothing else in the codebase should branch
 * on role to decide navigation.
 */

require_once __DIR__ . '/nex_sectors.php';
require_once __DIR__ . '/nex_paths.php';

/**
 * All workspaces. `sector` is the nex_sectors code that grants it (null =
 * granted by platform tier instead).
 *
 * NOTE (docs/NEXCLINIC_WORKSPACES.md §3.1 — unresolved): the mandate names 8
 * workspaces; the sector model defines 11. hr/payroll/inventory/theatre are
 * given their own workspaces here because they already have sectors and built
 * pages. Folding any of them into administration later is a change to this
 * table alone.
 */
function nex_workspace_catalogue() {
    return [
        'reception' => [
            'label' => 'Reception',
            'tagline' => 'Patient Intake & Flow',
            'icon' => 'mdi-desk',
            'accent' => 'hms-ws-reception',
            'sector' => 'reception',
            'home' => 'reception/his_admin_reception_dashboard.php',
        ],
        'doctor' => [
            'label' => 'Clinical',
            'tagline' => 'Clinical Care',
            'icon' => 'mdi-stethoscope',
            'accent' => 'hms-ws-doctor',
            'sector' => 'clinical',
            'home' => 'clinical/his_admin_view_patients.php',
        ],
        'nursing' => [
            'label' => 'Nursing',
            'tagline' => 'Patient Monitoring',
            'icon' => 'mdi-hospital-box-outline',
            'accent' => 'hms-ws-nursing',
            'sector' => 'clinical',
            'home' => 'clinical/his_admin_view_patients.php',
        ],
        'laboratory' => [
            'label' => 'Laboratory',
            'tagline' => 'Laboratory Operations',
            'icon' => 'mdi-flask',
            'accent' => 'hms-ws-laboratory',
            'sector' => 'laboratory',
            'home' => 'laboratory/his_admin_lab_report.php',
        ],
        'pharmacy' => [
            'label' => 'Pharmacy',
            'tagline' => 'Medication Management',
            'icon' => 'mdi-pill',
            'accent' => 'hms-ws-pharmacy',
            'sector' => 'pharmacy',
            'home' => 'pharmacy/his_admin_pharmacy_queue.php',
        ],
        'accounting' => [
            'label' => 'Accounting',
            'tagline' => 'Financial Operations',
            'icon' => 'mdi-cash-multiple',
            'accent' => 'hms-ws-accounting',
            'sector' => 'accounting',
            'home' => 'accounting/his_admin_accounting_records.php',
        ],
        'hr' => [
            'label' => 'Human Resources',
            'tagline' => 'Staff Management',
            'icon' => 'mdi-account-group',
            'accent' => 'hms-ws-hr',
            'sector' => 'hr',
            'home' => 'hr/his_admin_view_employee.php',
        ],
        'payroll' => [
            'label' => 'Payroll',
            'tagline' => 'Salary Operations',
            'icon' => 'mdi-cash',
            'accent' => 'hms-ws-payroll',
            'sector' => 'payroll',
            'home' => 'payroll/his_admin_manage_payrolls.php',
        ],
        'inventory' => [
            'label' => 'Inventory',
            'tagline' => 'Supplies & Equipment',
            'icon' => 'mdi-package-variant',
            'accent' => 'hms-ws-inventory',
            'sector' => 'inventory',
            'home' => 'inventory/his_admin_equipments_inventory.php',
        ],
        'theatre' => [
            'label' => 'Theatre',
            'tagline' => 'Surgical Operations',
            'icon' => 'mdi-scalpel',
            'accent' => 'hms-ws-theatre',
            'sector' => 'theatre',
            'home' => 'theatre/his_admin_surgery_records.php',
        ],
        'administration' => [
            'label' => 'Administration',
            'tagline' => 'Hospital Operations',
            'icon' => 'mdi-view-dashboard-outline',
            'accent' => 'hms-ws-administration',
            'sector' => null,
            'home' => 'his_admin_dashboard.php',
        ],
        'system' => [
            'label' => 'Platform',
            'tagline' => 'Platform Administration',
            'icon' => 'mdi-server',
            'accent' => 'hms-ws-system',
            'sector' => null,
            'home' => 'system/his_admin_manage_nex_users.php',
        ],
    ];
}

function nex_workspace_meta($workspace) {
    $all = nex_workspace_catalogue();
    return $all[$workspace] ?? $all['administration'];
}

/**
 * Clinical splits by rank: nurses get the Nursing workspace, everyone else
 * clinical gets Doctor. Rank comes from his_docs.clinical_rank, which the
 * authz kernel already reads for prescription.create.
 */
function nex_workspace_for_sector($sector_code) {
    if ($sector_code !== 'clinical') {
        return $sector_code;
    }
    global $mysqli;
    $actor = nex_actor_id();
    if ($actor && function_exists('nex_clinical_rank')) {
        return nex_clinical_rank($mysqli, $actor) === 'nurse' ? 'nursing' : 'doctor';
    }
    return 'doctor';
}

/**
 * The workspace the current user is in.
 * Explicit ?workspace= or session choice wins, then active sector, then a
 * single assignment, then platform tier. Legacy admin sessions with no nex
 * identity land on administration, which is what they see today.
 */
function nex_workspace_for_user() {
    $catalogue = nex_workspace_catalogue();

    if (!empty($_SESSION['nex_workspace']) && isset($catalogue[$_SESSION['nex_workspace']])) {
        return $_SESSION['nex_workspace'];
    }

    $active = function_exists('nex_active_sector') ? nex_active_sector() : null;
    if ($active) {
        $ws = nex_workspace_for_sector($active);
        if (isset($catalogue[$ws])) {
            return $ws;
        }
    }

    $assignments = $_SESSION['nex_assignments'] ?? [];
    if (count($assignments) === 1) {
        $ws = nex_workspace_for_sector($assignments[0]['sector_code']);
        if (isset($catalogue[$ws])) {
            return $ws;
        }
    }

    if (function_exists('nex_is_superadmin') && nex_is_superadmin()) {
        return 'system';
    }
    return 'administration';
}

function nex_workspace_set($workspace) {
    $catalogue = nex_workspace_catalogue();
    if (!isset($catalogue[$workspace])) {
        return false;
    }
    $sector = $catalogue[$workspace]['sector'];
    if ($sector !== null && function_exists('nex_has_sector') && !nex_has_sector($sector)) {
        return false;
    }
    if ($sector === null && function_exists('nex_is_hospital_admin') && !nex_is_hospital_admin()) {
        return false;
    }
    $_SESSION['nex_workspace'] = $workspace;
    if ($sector !== null && function_exists('nex_set_active_sector')) {
        nex_set_active_sector($sector);
    }
    return true;
}

/** Workspaces this user may switch to — drives the workspace switcher. */
function nex_workspace_available() {
    $out = [];
    $admin = function_exists('nex_is_hospital_admin') && nex_is_hospital_admin();
    $super = function_exists('nex_is_superadmin') && nex_is_superadmin();

    foreach (nex_workspace_catalogue() as $code => $meta) {
        if ($meta['sector'] === null) {
            if ($code === 'system' ? $super : $admin) {
                $out[$code] = $meta;
            }
            continue;
        }
        if (function_exists('nex_has_sector') && nex_has_sector($meta['sector'])) {
            if ($meta['sector'] === 'clinical' && $code !== nex_workspace_for_sector('clinical') && !$admin) {
                continue;
            }
            $out[$code] = $meta;
        }
    }
    return $out;
}

/**
 * Sidebar contents for ONE workspace. Sections: ['title' => ..., 'items' => [
 *   ['label','href','icon'] or ['label','icon','children'=>[['label','href'],…]]
 * ]]
 */
function nex_workspace_nav($workspace) {
    switch ($workspace) {
        case 'reception':
            return [
                ['title' => 'Front Desk', 'items' => [
                    ['label' => 'Reception Desk', 'icon' => 'mdi-desk', 'href' => 'reception/his_admin_reception_dashboard.php'],
                    ['label' => 'Register Patient', 'icon' => 'mdi-account-plus', 'href' => 'reception/his_admin_register_patient.php'],
                ]],
                ['title' => 'Appointments', 'items' => [
                    ['label' => 'Today & Upcoming', 'icon' => 'mdi-calendar-month-outline', 'href' => 'reception/his_admin_appointments.php'],
                    ['label' => 'Book Appointment', 'icon' => 'mdi-calendar-plus', 'href' => 'reception/his_admin_add_appointment.php'],
                ]],
            ];

        case 'doctor':
            return [
                ['title' => 'Clinical', 'items' => [
                    ['label' => 'My Patients', 'icon' => 'mdi-hospital-box-outline', 'href' => 'clinical/his_admin_view_patients.php'],
                    ['label' => 'Appointments', 'icon' => 'mdi-calendar-month-outline', 'href' => 'reception/his_admin_appointments.php'],
                ]],
                ['title' => 'Consultation', 'items' => [
                    ['label' => 'Medical Records', 'icon' => 'mdi-file-document', 'children' => [
                        ['label' => 'Add Record', 'href' => 'clinical/his_admin_add_medical_record.php'],
                        ['label' => 'View Records', 'href' => 'clinical/his_admin_medical_records.php'],
                    ]],
                    ['label' => 'Prescriptions', 'icon' => 'mdi-prescription', 'children' => [
                        ['label' => 'Write Prescription', 'href' => 'clinical/his_admin_add_presc.php'],
                        ['label' => 'View Prescriptions', 'href' => 'clinical/his_admin_view_presc.php'],
                    ]],
                    ['label' => 'Laboratory', 'icon' => 'mdi-flask', 'children' => [
                        ['label' => 'Order Test', 'href' => 'laboratory/his_admin_patient_lab_test.php'],
                        ['label' => 'Review Results', 'href' => 'laboratory/his_admin_patient_lab_result.php'],
                    ]],
                ]],
                ['title' => 'Patient Flow', 'items' => [
                    ['label' => 'Admissions', 'icon' => 'mdi-bed', 'children' => [
                        ['label' => 'InPatients', 'href' => 'clinical/his_admin_inpatient_records.php'],
                        ['label' => 'OutPatients', 'href' => 'clinical/his_admin_outpatient_records.php'],
                    ]],
                    ['label' => 'Discharge', 'icon' => 'mdi-exit-run', 'href' => 'clinical/his_admin_discharge_patient.php'],
                    ['label' => 'Transfers', 'icon' => 'mdi-swap-horizontal', 'href' => 'clinical/his_admin_patient_transfer.php'],
                ]],
            ];

        case 'nursing':
            return [
                ['title' => 'Ward', 'items' => [
                    ['label' => 'Patients', 'icon' => 'mdi-hospital-box-outline', 'href' => 'clinical/his_admin_view_patients.php'],
                    ['label' => 'InPatients', 'icon' => 'mdi-bed', 'href' => 'clinical/his_admin_inpatient_records.php'],
                ]],
                ['title' => 'Monitoring', 'items' => [
                    ['label' => 'Patient Vitals', 'icon' => 'mdi-heart-pulse', 'href' => 'clinical/his_admin_patient_lab_vitals.php'],
                    ['label' => 'Transfers', 'icon' => 'mdi-swap-horizontal', 'href' => 'clinical/his_admin_patient_transfer.php'],
                ]],
            ];

        case 'laboratory':
            return [
                ['title' => 'Laboratory', 'items' => [
                    ['label' => 'Lab Reports', 'icon' => 'mdi-flask', 'href' => 'laboratory/his_admin_lab_report.php'],
                    ['label' => 'Patient Tests', 'icon' => 'mdi-test-tube', 'href' => 'laboratory/his_admin_patient_lab_test.php'],
                    ['label' => 'Results', 'icon' => 'mdi-clipboard-check-outline', 'href' => 'laboratory/his_admin_patient_lab_result.php'],
                ]],
                ['title' => 'Vitals', 'items' => [
                    ['label' => 'Employee Vitals', 'icon' => 'mdi-heart-pulse', 'href' => 'laboratory/his_admin_employee_lab_vitals.php'],
                ]],
                ['title' => 'Equipment', 'items' => [
                    ['label' => 'Lab Equipment', 'icon' => 'mdi-microscope', 'children' => [
                        ['label' => 'Add Equipment', 'href' => 'laboratory/his_admin_add_lab_equipment.php'],
                        ['label' => 'Manage Equipment', 'href' => 'laboratory/his_admin_manage_lab_equipment.php'],
                    ]],
                ]],
            ];

        case 'pharmacy':
            return [
                ['title' => 'Dispensing', 'items' => [
                    ['label' => 'Dispense Queue', 'icon' => 'mdi-pill', 'href' => 'pharmacy/his_admin_pharmacy_queue.php'],
                    ['label' => 'Prescriptions', 'icon' => 'mdi-prescription', 'href' => 'clinical/his_admin_view_presc.php'],
                ]],
                ['title' => 'Catalogue', 'items' => [
                    ['label' => 'Pharmaceuticals', 'icon' => 'mdi-medical-bag', 'children' => [
                        ['label' => 'Add', 'href' => 'pharmacy/his_admin_add_pharmaceuticals.php'],
                        ['label' => 'View', 'href' => 'pharmacy/his_admin_view_pharmaceuticals.php'],
                        ['label' => 'Manage', 'href' => 'pharmacy/his_admin_manage_pharmaceuticals.php'],
                    ]],
                    ['label' => 'Categories', 'icon' => 'mdi-tag-outline', 'children' => [
                        ['label' => 'Add', 'href' => 'pharmacy/his_admin_add_pharm_cat.php'],
                        ['label' => 'Manage', 'href' => 'pharmacy/his_admin_manage_pharm_cat.php'],
                    ]],
                ]],
                ['title' => 'Stock', 'items' => [
                    ['label' => 'Drug Inventory', 'icon' => 'mdi-package-variant', 'href' => 'inventory/his_admin_pharm_inventory.php'],
                    ['label' => 'Records', 'icon' => 'mdi-chart-line', 'href' => 'pharmacy/his_admin_pharmaceutical_records.php'],
                ]],
            ];

        case 'accounting':
            return [
                ['title' => 'Receivables', 'items' => [
                    ['label' => 'Add Receivable', 'icon' => 'mdi-cash-plus', 'href' => 'accounting/his_admin_add_acc_receivable.php'],
                    ['label' => 'Manage Receivables', 'icon' => 'mdi-cash-check', 'href' => 'accounting/his_admin_manage_acc_receivable.php'],
                ]],
                ['title' => 'Payables', 'items' => [
                    ['label' => 'Add Payable', 'icon' => 'mdi-cash-minus', 'href' => 'accounting/his_admin_add_acc.payable.php'],
                    ['label' => 'Manage Payables', 'icon' => 'mdi-cash-remove', 'href' => 'accounting/his_admin_manage_acc_payable.php'],
                ]],
                ['title' => 'Reporting', 'items' => [
                    ['label' => 'Accounting Records', 'icon' => 'mdi-chart-line', 'href' => 'accounting/his_admin_accounting_records.php'],
                ]],
            ];

        case 'hr':
            return [
                ['title' => 'Staff', 'items' => [
                    ['label' => 'Employees', 'icon' => 'mdi-account-group', 'children' => [
                        ['label' => 'Add Employee', 'href' => 'hr/his_admin_add_employee.php'],
                        ['label' => 'View Employees', 'href' => 'hr/his_admin_view_employee.php'],
                        ['label' => 'Manage Employees', 'href' => 'hr/his_admin_manage_employee.php'],
                    ]],
                ]],
                ['title' => 'Organisation', 'items' => [
                    ['label' => 'Assign Department', 'icon' => 'mdi-office-building', 'href' => 'hr/his_admin_assaign_dept.php'],
                    ['label' => 'Transfer Employee', 'icon' => 'mdi-swap-horizontal', 'href' => 'hr/his_admin_transfer_employee.php'],
                ]],
                ['title' => 'Reporting', 'items' => [
                    ['label' => 'Employee Records', 'icon' => 'mdi-chart-line', 'href' => 'hr/his_admin_employee_records.php'],
                ]],
            ];

        case 'payroll':
            return [
                ['title' => 'Payroll', 'items' => [
                    ['label' => 'Add Payroll', 'icon' => 'mdi-cash-plus', 'href' => 'payroll/his_admin_add_payroll.php'],
                    ['label' => 'Generate Payrolls', 'icon' => 'mdi-cash-sync', 'href' => 'payroll/his_admin_generate_payrolls.php'],
                    ['label' => 'Manage Payrolls', 'icon' => 'mdi-cash-multiple', 'href' => 'payroll/his_admin_manage_payrolls.php'],
                ]],
            ];

        case 'inventory':
            return [
                ['title' => 'Stock', 'items' => [
                    ['label' => 'Pharmaceuticals', 'icon' => 'mdi-pill', 'href' => 'inventory/his_admin_pharm_inventory.php'],
                    ['label' => 'Assets & Equipment', 'icon' => 'mdi-package-variant', 'href' => 'inventory/his_admin_equipments_inventory.php'],
                ]],
                ['title' => 'Equipment', 'items' => [
                    ['label' => 'Add Equipment', 'icon' => 'mdi-plus-box', 'href' => 'inventory/his_admin_add_equipment.php'],
                    ['label' => 'Manage Equipment', 'icon' => 'mdi-wrench', 'href' => 'inventory/his_admin_manage_equipment.php'],
                ]],
                ['title' => 'Suppliers', 'items' => [
                    ['label' => 'Vendors', 'icon' => 'mdi-truck-outline', 'children' => [
                        ['label' => 'Add Vendor', 'href' => 'inventory/his_admin_add_vendor.php'],
                        ['label' => 'Manage Vendors', 'href' => 'inventory/his_admin_manage_vendor.php'],
                    ]],
                ]],
            ];

        case 'theatre':
            return [
                ['title' => 'Surgery', 'items' => [
                    ['label' => 'Surgery Records', 'icon' => 'mdi-scalpel', 'href' => 'theatre/his_admin_surgery_records.php'],
                    ['label' => 'Add Theatre Patient', 'icon' => 'mdi-account-plus', 'href' => 'theatre/his_admin_add_theatre_patient.php'],
                    ['label' => 'Manage Patients', 'icon' => 'mdi-clipboard-list-outline', 'href' => 'theatre/his_admin_manage_theatre_patient.php'],
                ]],
            ];

        case 'system':
            return [
                ['title' => 'Platform', 'items' => [
                    ['label' => 'Users', 'icon' => 'mdi-account-group', 'href' => 'system/his_admin_manage_nex_users.php'],
                    ['label' => 'Add Admin User', 'icon' => 'mdi-account-plus', 'href' => 'his_admin_register.php'],
                ]],
                ['title' => 'Security', 'items' => [
                    ['label' => 'Password Resets', 'icon' => 'mdi-account-key', 'href' => 'system/his_admin_manage_password_resets.php'],
                ]],
            ];

        case 'administration':
        default:
            return [
                ['title' => 'Overview', 'items' => [
                    ['label' => 'Hospital Activity', 'icon' => 'mdi-view-dashboard-outline', 'href' => 'his_admin_dashboard.php'],
                ]],
                ['title' => 'Operations', 'items' => [
                    ['label' => 'Patients', 'icon' => 'mdi-hospital-box-outline', 'children' => [
                        ['label' => 'View Patients', 'href' => 'clinical/his_admin_view_patients.php'],
                        ['label' => 'Manage Patients', 'href' => 'clinical/his_admin_manage_patient.php'],
                        ['label' => 'InPatient Records', 'href' => 'clinical/his_admin_inpatient_records.php'],
                        ['label' => 'OutPatient Records', 'href' => 'clinical/his_admin_outpatient_records.php'],
                    ]],
                    ['label' => 'Appointments', 'icon' => 'mdi-calendar-month-outline', 'href' => 'reception/his_admin_appointments.php'],
                    ['label' => 'Medical Records', 'icon' => 'mdi-file-document', 'href' => 'clinical/his_admin_medical_records.php'],
                ]],
                ['title' => 'Reporting', 'items' => [
                    ['label' => 'Reports', 'icon' => 'mdi-chart-line', 'children' => [
                        ['label' => 'Accounting Records', 'href' => 'accounting/his_admin_accounting_records.php'],
                        ['label' => 'Employee Records', 'href' => 'hr/his_admin_employee_records.php'],
                        ['label' => 'Pharmaceutical Records', 'href' => 'pharmacy/his_admin_pharmaceutical_records.php'],
                    ]],
                ]],
                ['title' => 'Account', 'items' => [
                    ['label' => 'My Account', 'icon' => 'mdi-account-circle', 'href' => 'his_admin_account.php'],
                ]],
            ];
    }
}

/** Large primary buttons for the workspace homepage. */
function nex_workspace_quick_actions($workspace) {
    switch ($workspace) {
        case 'reception':
            return [
                ['label' => 'Register Patient', 'icon' => 'mdi-account-plus', 'href' => 'reception/his_admin_register_patient.php'],
                ['label' => 'Book Appointment', 'icon' => 'mdi-calendar-plus', 'href' => 'reception/his_admin_add_appointment.php'],
            ];
        case 'doctor':
            return [
                ['label' => 'New Consultation', 'icon' => 'mdi-file-document-edit', 'href' => 'clinical/his_admin_add_medical_record.php'],
                ['label' => 'Write Prescription', 'icon' => 'mdi-prescription', 'href' => 'clinical/his_admin_add_presc.php'],
                ['label' => 'Order Lab Test', 'icon' => 'mdi-flask', 'href' => 'laboratory/his_admin_patient_lab_test.php'],
            ];
        case 'nursing':
            return [
                ['label' => 'Record Vitals', 'icon' => 'mdi-heart-pulse', 'href' => 'clinical/his_admin_patient_lab_vitals.php'],
            ];
        case 'laboratory':
            return [
                ['label' => 'Enter Result', 'icon' => 'mdi-clipboard-check-outline', 'href' => 'laboratory/his_admin_patient_lab_result.php'],
            ];
        case 'pharmacy':
            return [
                ['label' => 'Dispense Queue', 'icon' => 'mdi-pill', 'href' => 'pharmacy/his_admin_pharmacy_queue.php'],
            ];
        case 'accounting':
            return [
                ['label' => 'Add Receivable', 'icon' => 'mdi-cash-plus', 'href' => 'accounting/his_admin_add_acc_receivable.php'],
            ];
        default:
            return [];
    }
}

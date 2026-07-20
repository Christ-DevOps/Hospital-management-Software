<?php
/**
 * NexClinic Stage A — Cameroon patient registration helpers.
 * Village / division / sub-division are free text until Region→Division→Subdivision dropdowns ship.
 */

function nex_blood_groups() {
    return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
}

function nex_marital_statuses() {
    return ['Single', 'Married', 'Divorced', 'Widowed'];
}

function nex_sex_options() {
    return ['Male', 'Female', 'Other'];
}

/**
 * @return array{ok:bool,err?:string,data?:array}
 */
function nex_collect_patient_post($require_legacy = true) {
    $required = [
        'pat_fname' => 'First name',
        'pat_lname' => 'Last name',
        'pat_dob' => 'Date of birth',
        'pat_sex' => 'Sex',
        'pat_phone' => 'Phone',
        'pat_blood_group' => 'Blood group',
        'pat_emergency_name' => 'Emergency contact name',
        'pat_emergency_phone' => 'Emergency contact phone',
    ];
    if ($require_legacy) {
        $required['pat_age'] = 'Age';
        $required['pat_addr'] = 'Address';
        $required['pat_ailment'] = 'Ailment';
        $required['pat_type'] = 'Patient type';
        $required['pat_number'] = 'Patient number';
    }

    $data = [];
    foreach ($required as $key => $label) {
        $val = trim((string) ($_POST[$key] ?? ''));
        if ($val === '' || ($key === 'pat_type' && strtolower($val) === 'choose')) {
            return ['ok' => false, 'err' => $label . ' is required.'];
        }
        $data[$key] = $val;
    }

    $bg = $data['pat_blood_group'];
    if (!in_array($bg, nex_blood_groups(), true)) {
        return ['ok' => false, 'err' => 'Invalid blood group.'];
    }
    if (!in_array($data['pat_sex'], nex_sex_options(), true)) {
        return ['ok' => false, 'err' => 'Invalid sex.'];
    }

    $optional = [
        'pat_marital_status',
        'pat_religion',
        'pat_village',
        'pat_division',
        'pat_subdivision',
        'pat_email',
    ];
    foreach ($optional as $key) {
        $data[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if (!empty($data['pat_marital_status']) && !in_array($data['pat_marital_status'], nex_marital_statuses(), true)) {
        return ['ok' => false, 'err' => 'Invalid marital status.'];
    }

    return ['ok' => true, 'data' => $data];
}

function nex_render_select($name, $options, $selected = '', $required = false, $placeholder = 'Choose') {
    $req = $required ? ' required="required"' : '';
    $html = '<select id="' . escape_output($name) . '" name="' . escape_output($name) . '" class="form-control"' . $req . '>';
    $html .= '<option value="">' . escape_output($placeholder) . '</option>';
    foreach ($options as $opt) {
        $sel = ((string) $selected === (string) $opt) ? ' selected' : '';
        $html .= '<option value="' . escape_output($opt) . '"' . $sel . '>' . escape_output($opt) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

/**
 * Extra Cameroon fields for register/update forms.
 * @param object|null $row Existing patient row for update, or null for register.
 */
function nex_render_cameroon_fields($row = null) {
    $get = static function ($prop) use ($row) {
        if ($row === null || !isset($row->$prop)) {
            return '';
        }
        return $row->$prop;
    };
    ?>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label class="col-form-label">Sex <span class="text-danger">*</span></label>
            <?php echo nex_render_select('pat_sex', nex_sex_options(), $get('pat_sex'), true); ?>
        </div>
        <div class="form-group col-md-4">
            <label class="col-form-label">Blood Group <span class="text-danger">*</span></label>
            <?php echo nex_render_select('pat_blood_group', nex_blood_groups(), $get('pat_blood_group'), true); ?>
        </div>
        <div class="form-group col-md-4">
            <label class="col-form-label">Marital Status</label>
            <?php echo nex_render_select('pat_marital_status', nex_marital_statuses(), $get('pat_marital_status'), false, 'Optional'); ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label class="col-form-label">Religion</label>
            <input type="text" name="pat_religion" class="form-control" value="<?php echo escape_output($get('pat_religion')); ?>" placeholder="Optional">
        </div>
        <div class="form-group col-md-6">
            <label class="col-form-label">Email</label>
            <input type="email" name="pat_email" class="form-control" value="<?php echo escape_output($get('pat_email')); ?>" placeholder="Optional">
        </div>
    </div>
    <!-- TODO: village/division/subdivision → Region→Division→Subdivision linked dropdowns (census/mairie) -->
    <div class="form-row">
        <div class="form-group col-md-4">
            <label class="col-form-label">Village / Quarter</label>
            <input type="text" name="pat_village" class="form-control" value="<?php echo escape_output($get('pat_village')); ?>" placeholder="Optional">
        </div>
        <div class="form-group col-md-4">
            <label class="col-form-label">Division</label>
            <input type="text" name="pat_division" class="form-control" value="<?php echo escape_output($get('pat_division')); ?>" placeholder="Optional">
        </div>
        <div class="form-group col-md-4">
            <label class="col-form-label">Sub-division</label>
            <input type="text" name="pat_subdivision" class="form-control" value="<?php echo escape_output($get('pat_subdivision')); ?>" placeholder="Optional">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label class="col-form-label">Emergency Contact Name <span class="text-danger">*</span></label>
            <input type="text" required="required" name="pat_emergency_name" class="form-control" value="<?php echo escape_output($get('pat_emergency_name')); ?>" placeholder="Next of kin">
        </div>
        <div class="form-group col-md-6">
            <label class="col-form-label">Emergency Contact Phone <span class="text-danger">*</span></label>
            <input type="text" required="required" name="pat_emergency_phone" class="form-control" value="<?php echo escape_output($get('pat_emergency_phone')); ?>" placeholder="Phone number">
        </div>
    </div>
    <?php
}

function nex_render_cameroon_view($row) {
    $fields = [
        'Sex' => $row->pat_sex ?? '',
        'Blood Group' => $row->pat_blood_group ?? '',
        'Marital Status' => $row->pat_marital_status ?? '',
        'Religion' => $row->pat_religion ?? '',
        'Email' => $row->pat_email ?? '',
        'Village / Quarter' => $row->pat_village ?? '',
        'Division' => $row->pat_division ?? '',
        'Sub-division' => $row->pat_subdivision ?? '',
        'Emergency Contact' => $row->pat_emergency_name ?? '',
        'Emergency Phone' => $row->pat_emergency_phone ?? '',
        'Registration Hour' => $row->pat_reg_hour ?? '',
    ];
    foreach ($fields as $label => $value) {
        $display = ($value !== null && $value !== '') ? $value : '—';
        echo '<p class="text-muted mb-2 font-13"><strong>' . escape_output($label) . ' :</strong> <span class="ml-2">' . escape_output($display) . '</span></p>';
    }
}

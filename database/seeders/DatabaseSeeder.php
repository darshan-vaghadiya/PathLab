<?php

namespace Database\Seeders;

use App\Models\B2bClient;
use App\Models\LabSetting;
use App\Models\Order;
use App\Models\OrderTest;
use App\Models\OrderTestResult;
use App\Models\ParameterRange;
use App\Models\Patient;
use App\Models\ReferringDoctor;
use App\Models\Report;
use App\Models\Test;
use App\Models\TestCategory;
use App\Models\TestPackage;
use App\Models\TestPackageItem;
use App\Models\TestParameter;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Admin User ─────────────────────────────────────────
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@pathlab.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // ── Test Categories ────────────────────────────────────
        $hematology = TestCategory::create(['name' => 'Hematology', 'description' => 'Blood cell related tests']);
        $biochemistry = TestCategory::create(['name' => 'Biochemistry', 'description' => 'Blood chemistry and metabolic tests']);
        $microbiology = TestCategory::create(['name' => 'Microbiology', 'description' => 'Infection and culture tests']);
        $serology = TestCategory::create(['name' => 'Serology', 'description' => 'Antibody and antigen tests']);
        $clinicalPathology = TestCategory::create(['name' => 'Clinical Pathology', 'description' => 'General clinical tests']);
        $urineAnalysis = TestCategory::create(['name' => 'Urine Analysis', 'description' => 'Urine examination tests']);

        // ── Hematology Tests ───────────────────────────────────

        // CBC
        $cbc = Test::create([
            'test_category_id' => $hematology->id,
            'name' => 'Complete Blood Count (CBC)',
            'short_name' => 'CBC',
            'price' => 350.00,
            'sample_type' => 'EDTA Blood',
            'method' => 'Automated Hematology Analyzer',
            'description' => 'Includes WBC, RBC, Hemoglobin, Hematocrit, Platelets, MCV, MCH, MCHC',
            'report_type' => 'parametric',
            'sort_order' => 1,
        ]);

        $this->createParameterWithRanges($cbc->id, 'Hemoglobin', 'numeric', 'g/dL', 1, [
            ['range_group' => 'Male', 'min_value' => 13.0, 'max_value' => 17.0],
            ['range_group' => 'Female', 'min_value' => 12.0, 'max_value' => 15.5],
            ['range_group' => 'Child', 'min_value' => 11.0, 'max_value' => 14.0],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Total WBC Count', 'numeric', 'cells/cumm', 2, [
            ['range_group' => 'Male', 'min_value' => 4000, 'max_value' => 11000],
            ['range_group' => 'Female', 'min_value' => 4000, 'max_value' => 11000],
            ['range_group' => 'Child', 'min_value' => 5000, 'max_value' => 13000],
        ]);
        $this->createParameterWithRanges($cbc->id, 'RBC Count', 'numeric', 'million/cumm', 3, [
            ['range_group' => 'Male', 'min_value' => 4.5, 'max_value' => 5.5],
            ['range_group' => 'Female', 'min_value' => 3.8, 'max_value' => 4.8],
            ['range_group' => 'Child', 'min_value' => 4.0, 'max_value' => 5.2],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Platelet Count', 'numeric', 'lakhs/cumm', 4, [
            ['range_group' => 'Male', 'min_value' => 1.5, 'max_value' => 4.0],
            ['range_group' => 'Female', 'min_value' => 1.5, 'max_value' => 4.0],
            ['range_group' => 'Child', 'min_value' => 1.5, 'max_value' => 4.0],
        ]);
        $this->createParameterWithRanges($cbc->id, 'PCV/Hematocrit', 'numeric', '%', 5, [
            ['range_group' => 'Male', 'min_value' => 40, 'max_value' => 50],
            ['range_group' => 'Female', 'min_value' => 36, 'max_value' => 44],
            ['range_group' => 'Child', 'min_value' => 35, 'max_value' => 45],
        ]);
        $this->createParameterWithRanges($cbc->id, 'MCV', 'numeric', 'fL', 6, [
            ['range_group' => 'All', 'min_value' => 80, 'max_value' => 100],
        ]);
        $this->createParameterWithRanges($cbc->id, 'MCH', 'numeric', 'pg', 7, [
            ['range_group' => 'All', 'min_value' => 27, 'max_value' => 32],
        ]);
        $this->createParameterWithRanges($cbc->id, 'MCHC', 'numeric', 'g/dL', 8, [
            ['range_group' => 'All', 'min_value' => 32, 'max_value' => 36],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Neutrophils', 'numeric', '%', 9, [
            ['range_group' => 'All', 'min_value' => 40, 'max_value' => 70],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Lymphocytes', 'numeric', '%', 10, [
            ['range_group' => 'All', 'min_value' => 20, 'max_value' => 40],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Eosinophils', 'numeric', '%', 11, [
            ['range_group' => 'All', 'min_value' => 1, 'max_value' => 6],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Monocytes', 'numeric', '%', 12, [
            ['range_group' => 'All', 'min_value' => 2, 'max_value' => 8],
        ]);
        $this->createParameterWithRanges($cbc->id, 'Basophils', 'numeric', '%', 13, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 1],
        ]);

        // Hemoglobin
        $hemoglobin = Test::create([
            'test_category_id' => $hematology->id,
            'name' => 'Hemoglobin',
            'short_name' => 'Hb',
            'price' => 80.00,
            'sample_type' => 'EDTA Blood',
            'method' => 'Cyanmethemoglobin',
            'report_type' => 'parametric',
            'sort_order' => 2,
        ]);

        $this->createParameterWithRanges($hemoglobin->id, 'Hemoglobin', 'numeric', 'g/dL', 1, [
            ['range_group' => 'Male', 'min_value' => 13.0, 'max_value' => 17.0],
            ['range_group' => 'Female', 'min_value' => 12.0, 'max_value' => 15.5],
            ['range_group' => 'Child', 'min_value' => 11.0, 'max_value' => 14.0],
        ]);

        // ESR
        $esr = Test::create([
            'test_category_id' => $hematology->id,
            'name' => 'Erythrocyte Sedimentation Rate',
            'short_name' => 'ESR',
            'price' => 80.00,
            'sample_type' => 'EDTA Blood',
            'method' => 'Westergren',
            'report_type' => 'parametric',
            'sort_order' => 3,
        ]);

        $this->createParameterWithRanges($esr->id, 'ESR', 'numeric', 'mm/hr', 1, [
            ['range_group' => 'Male', 'min_value' => 0, 'max_value' => 10],
            ['range_group' => 'Female', 'min_value' => 0, 'max_value' => 20],
            ['range_group' => 'Child', 'min_value' => 0, 'max_value' => 10],
        ]);

        // Blood Group
        $bloodGroup = Test::create([
            'test_category_id' => $hematology->id,
            'name' => 'Blood Group & Rh Typing',
            'short_name' => 'BG',
            'price' => 100.00,
            'sample_type' => 'EDTA Blood',
            'method' => 'Slide Agglutination',
            'report_type' => 'text',
            'sort_order' => 4,
        ]);

        TestParameter::create([
            'test_id' => $bloodGroup->id,
            'name' => 'Blood Group & Rh Type',
            'field_type' => 'text',
            'unit' => null,
            'sort_order' => 1,
        ]);

        // ── Biochemistry Tests ─────────────────────────────────

        // Blood Sugar Fasting
        $bsFasting = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Blood Sugar Fasting',
            'short_name' => 'BSF',
            'price' => 80.00,
            'sample_type' => 'Fluoride Blood',
            'method' => 'GOD-POD',
            'report_type' => 'parametric',
            'sort_order' => 1,
        ]);

        $this->createParameterWithRanges($bsFasting->id, 'Blood Sugar (Fasting)', 'numeric', 'mg/dL', 1, [
            ['range_group' => 'All', 'min_value' => 70, 'max_value' => 110],
        ]);

        // Blood Sugar PP
        $bsPP = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Blood Sugar Post Prandial',
            'short_name' => 'BSPP',
            'price' => 80.00,
            'sample_type' => 'Fluoride Blood',
            'method' => 'GOD-POD',
            'report_type' => 'parametric',
            'sort_order' => 2,
        ]);

        $this->createParameterWithRanges($bsPP->id, 'Blood Sugar (PP)', 'numeric', 'mg/dL', 1, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 140],
        ]);

        // HbA1c
        $hba1c = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Glycosylated Hemoglobin',
            'short_name' => 'HbA1c',
            'price' => 350.00,
            'sample_type' => 'EDTA Blood',
            'method' => 'HPLC',
            'report_type' => 'parametric',
            'sort_order' => 3,
        ]);

        $this->createParameterWithRanges($hba1c->id, 'HbA1c', 'numeric', '%', 1, [
            ['range_group' => 'All', 'min_value' => 4.0, 'max_value' => 5.6, 'text_value' => 'Normal <5.7, Pre-diabetic 5.7-6.4, Diabetic >6.5'],
        ]);

        // Lipid Profile
        $lipidProfile = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Lipid Profile',
            'short_name' => 'Lipid',
            'price' => 500.00,
            'sample_type' => 'Serum',
            'method' => 'Enzymatic',
            'description' => 'Includes Total Cholesterol, Triglycerides, HDL, LDL, VLDL',
            'report_type' => 'parametric',
            'sort_order' => 4,
        ]);

        $this->createParameterWithRanges($lipidProfile->id, 'Total Cholesterol', 'numeric', 'mg/dL', 1, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 200],
        ]);
        $this->createParameterWithRanges($lipidProfile->id, 'Triglycerides', 'numeric', 'mg/dL', 2, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 150],
        ]);
        $this->createParameterWithRanges($lipidProfile->id, 'HDL Cholesterol', 'numeric', 'mg/dL', 3, [
            ['range_group' => 'Male', 'min_value' => 40, 'max_value' => 60],
            ['range_group' => 'Female', 'min_value' => 50, 'max_value' => 60],
        ]);
        $this->createParameterWithRanges($lipidProfile->id, 'LDL Cholesterol', 'numeric', 'mg/dL', 4, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 100],
        ]);
        $this->createParameterWithRanges($lipidProfile->id, 'VLDL Cholesterol', 'numeric', 'mg/dL', 5, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 30],
        ]);
        $this->createParameterWithRanges($lipidProfile->id, 'Total Cholesterol/HDL Ratio', 'numeric', 'ratio', 6, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 4.5],
        ]);

        // LFT
        $lft = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Liver Function Test',
            'short_name' => 'LFT',
            'price' => 450.00,
            'sample_type' => 'Serum',
            'method' => 'Kinetic / Colorimetric',
            'description' => 'Includes Bilirubin Total/Direct, SGOT, SGPT, ALP, Total Protein, Albumin, Globulin',
            'report_type' => 'parametric',
            'sort_order' => 5,
        ]);

        $this->createParameterWithRanges($lft->id, 'Bilirubin Total', 'numeric', 'mg/dL', 1, [
            ['range_group' => 'All', 'min_value' => 0.1, 'max_value' => 1.2],
        ]);
        $this->createParameterWithRanges($lft->id, 'Bilirubin Direct', 'numeric', 'mg/dL', 2, [
            ['range_group' => 'All', 'min_value' => 0.0, 'max_value' => 0.3],
        ]);
        $this->createParameterWithRanges($lft->id, 'Bilirubin Indirect', 'numeric', 'mg/dL', 3, [
            ['range_group' => 'All', 'min_value' => 0.1, 'max_value' => 0.9],
        ]);
        $this->createParameterWithRanges($lft->id, 'SGOT (AST)', 'numeric', 'U/L', 4, [
            ['range_group' => 'All', 'min_value' => 5, 'max_value' => 40],
        ]);
        $this->createParameterWithRanges($lft->id, 'SGPT (ALT)', 'numeric', 'U/L', 5, [
            ['range_group' => 'All', 'min_value' => 5, 'max_value' => 40],
        ]);
        $this->createParameterWithRanges($lft->id, 'Alkaline Phosphatase', 'numeric', 'U/L', 6, [
            ['range_group' => 'All', 'min_value' => 30, 'max_value' => 120],
        ]);
        $this->createParameterWithRanges($lft->id, 'Total Protein', 'numeric', 'g/dL', 7, [
            ['range_group' => 'All', 'min_value' => 6.0, 'max_value' => 8.3],
        ]);
        $this->createParameterWithRanges($lft->id, 'Albumin', 'numeric', 'g/dL', 8, [
            ['range_group' => 'All', 'min_value' => 3.5, 'max_value' => 5.5],
        ]);
        $this->createParameterWithRanges($lft->id, 'Globulin', 'numeric', 'g/dL', 9, [
            ['range_group' => 'All', 'min_value' => 2.0, 'max_value' => 3.5],
        ]);
        $this->createParameterWithRanges($lft->id, 'A/G Ratio', 'numeric', 'ratio', 10, [
            ['range_group' => 'All', 'min_value' => 1.0, 'max_value' => 2.0],
        ]);

        // KFT
        $kft = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Kidney Function Test',
            'short_name' => 'KFT',
            'price' => 450.00,
            'sample_type' => 'Serum',
            'method' => 'Kinetic / Colorimetric',
            'description' => 'Includes Urea, Creatinine, Uric Acid, BUN, Electrolytes',
            'report_type' => 'parametric',
            'sort_order' => 6,
        ]);

        $this->createParameterWithRanges($kft->id, 'Blood Urea', 'numeric', 'mg/dL', 1, [
            ['range_group' => 'All', 'min_value' => 15, 'max_value' => 40],
        ]);
        $this->createParameterWithRanges($kft->id, 'Serum Creatinine', 'numeric', 'mg/dL', 2, [
            ['range_group' => 'Male', 'min_value' => 0.7, 'max_value' => 1.3],
            ['range_group' => 'Female', 'min_value' => 0.6, 'max_value' => 1.1],
        ]);
        $this->createParameterWithRanges($kft->id, 'Uric Acid', 'numeric', 'mg/dL', 3, [
            ['range_group' => 'Male', 'min_value' => 3.5, 'max_value' => 7.2],
            ['range_group' => 'Female', 'min_value' => 2.6, 'max_value' => 6.0],
        ]);
        $this->createParameterWithRanges($kft->id, 'BUN', 'numeric', 'mg/dL', 4, [
            ['range_group' => 'All', 'min_value' => 7, 'max_value' => 20],
        ]);
        $this->createParameterWithRanges($kft->id, 'Sodium', 'numeric', 'mEq/L', 5, [
            ['range_group' => 'All', 'min_value' => 136, 'max_value' => 145],
        ]);
        $this->createParameterWithRanges($kft->id, 'Potassium', 'numeric', 'mEq/L', 6, [
            ['range_group' => 'All', 'min_value' => 3.5, 'max_value' => 5.1],
        ]);
        $this->createParameterWithRanges($kft->id, 'Chloride', 'numeric', 'mEq/L', 7, [
            ['range_group' => 'All', 'min_value' => 98, 'max_value' => 106],
        ]);

        // Thyroid Profile
        $thyroid = Test::create([
            'test_category_id' => $biochemistry->id,
            'name' => 'Thyroid Profile (T3, T4, TSH)',
            'short_name' => 'Thyroid',
            'price' => 400.00,
            'sample_type' => 'Serum',
            'method' => 'CLIA / ELISA',
            'description' => 'Includes T3, T4, TSH',
            'report_type' => 'parametric',
            'sort_order' => 7,
        ]);

        $this->createParameterWithRanges($thyroid->id, 'T3', 'numeric', 'ng/dL', 1, [
            ['range_group' => 'All', 'min_value' => 80, 'max_value' => 200],
        ]);
        $this->createParameterWithRanges($thyroid->id, 'T4', 'numeric', 'µg/dL', 2, [
            ['range_group' => 'All', 'min_value' => 4.5, 'max_value' => 12.0],
        ]);
        $this->createParameterWithRanges($thyroid->id, 'TSH', 'numeric', 'µIU/mL', 3, [
            ['range_group' => 'All', 'min_value' => 0.3, 'max_value' => 4.2],
        ]);

        // ── Urine Analysis Tests ───────────────────────────────

        $urineRoutine = Test::create([
            'test_category_id' => $urineAnalysis->id,
            'name' => 'Urine Routine & Microscopy',
            'short_name' => 'Urine R/M',
            'price' => 100.00,
            'sample_type' => 'Urine',
            'method' => 'Dipstick & Microscopy',
            'description' => 'Includes Color, Appearance, pH, Specific Gravity, Protein, Glucose, Pus Cells, RBC, Epithelial Cells, Casts',
            'report_type' => 'mixed',
            'sort_order' => 1,
        ]);

        // Text parameters (no ranges)
        TestParameter::create(['test_id' => $urineRoutine->id, 'name' => 'Colour', 'field_type' => 'text', 'unit' => null, 'sort_order' => 1]);
        TestParameter::create(['test_id' => $urineRoutine->id, 'name' => 'Appearance', 'field_type' => 'text', 'unit' => null, 'sort_order' => 2]);

        // Numeric parameters with ranges
        $this->createParameterWithRanges($urineRoutine->id, 'pH', 'numeric', null, 3, [
            ['range_group' => 'All', 'min_value' => 4.5, 'max_value' => 8.0],
        ]);
        $this->createParameterWithRanges($urineRoutine->id, 'Specific Gravity', 'numeric', null, 4, [
            ['range_group' => 'All', 'min_value' => 1.005, 'max_value' => 1.030],
        ]);

        // Options parameters
        $proteinParam = TestParameter::create([
            'test_id' => $urineRoutine->id,
            'name' => 'Protein',
            'field_type' => 'options',
            'unit' => null,
            'options' => ['Nil', 'Trace', '+', '++', '+++'],
            'sort_order' => 5,
        ]);
        ParameterRange::create([
            'test_parameter_id' => $proteinParam->id,
            'range_group' => 'All',
            'text_value' => 'Nil',
        ]);

        $sugarParam = TestParameter::create([
            'test_id' => $urineRoutine->id,
            'name' => 'Sugar',
            'field_type' => 'options',
            'unit' => null,
            'options' => ['Nil', 'Trace', '+', '++', '+++'],
            'sort_order' => 6,
        ]);
        ParameterRange::create([
            'test_parameter_id' => $sugarParam->id,
            'range_group' => 'All',
            'text_value' => 'Nil',
        ]);

        // Numeric microscopy parameters
        $this->createParameterWithRanges($urineRoutine->id, 'Pus Cells', 'numeric', '/HPF', 7, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 5],
        ]);
        $this->createParameterWithRanges($urineRoutine->id, 'RBCs', 'numeric', '/HPF', 8, [
            ['range_group' => 'All', 'min_value' => 0, 'max_value' => 2],
        ]);

        // Text microscopy parameters (no ranges)
        TestParameter::create(['test_id' => $urineRoutine->id, 'name' => 'Epithelial Cells', 'field_type' => 'text', 'unit' => null, 'sort_order' => 9]);
        TestParameter::create(['test_id' => $urineRoutine->id, 'name' => 'Casts', 'field_type' => 'text', 'unit' => null, 'sort_order' => 10]);
        TestParameter::create(['test_id' => $urineRoutine->id, 'name' => 'Crystals', 'field_type' => 'text', 'unit' => null, 'sort_order' => 11]);
        TestParameter::create(['test_id' => $urineRoutine->id, 'name' => 'Bacteria', 'field_type' => 'text', 'unit' => null, 'sort_order' => 12]);

        // ── Test Packages ──────────────────────────────────────
        $fullBody = TestPackage::create([
            'name' => 'Full Body Checkup',
            'description' => 'Comprehensive health screening package including CBC, Blood Sugar, Lipid Profile, LFT, KFT, Thyroid, Urine Routine',
            'price' => 1999.00,
        ]);

        foreach ([$cbc, $bsFasting, $lipidProfile, $lft, $kft, $thyroid, $urineRoutine] as $test) {
            TestPackageItem::create([
                'test_package_id' => $fullBody->id,
                'test_id' => $test->id,
            ]);
        }

        $diabetesPanel = TestPackage::create([
            'name' => 'Diabetes Panel',
            'description' => 'Diabetes screening and monitoring package including Blood Sugar Fasting, Blood Sugar PP, HbA1c, KFT, Urine Routine',
            'price' => 599.00,
        ]);

        foreach ([$bsFasting, $bsPP, $hba1c, $kft, $urineRoutine] as $test) {
            TestPackageItem::create([
                'test_package_id' => $diabetesPanel->id,
                'test_id' => $test->id,
            ]);
        }

        // ── Lab Settings ───────────────────────────────────────
        LabSetting::set('lab_name', 'PathLab');
        LabSetting::set('lab_phone', '+91-9876543210');
        LabSetting::set('lab_email', 'info@pathlab.com');
        LabSetting::set('lab_address', '123, Medical Complex, Main Road, City - 400001');
        LabSetting::set('lab_logo', null);

        // ── Referring Doctor ───────────────────────────────────
        ReferringDoctor::create([
            'name' => 'Dr. Sharma',
            'phone' => '+91-9876543211',
            'email' => 'dr.sharma@sharmaclinic.com',
            'clinic_name' => 'Sharma Clinic',
            'address' => '45, Health Street, City',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
        ]);

        // ── B2B Client ─────────────────────────────────────────
        B2bClient::create([
            'name' => 'City Hospital Lab',
            'contact_person' => 'Mr. Rajesh Kumar',
            'phone' => '+91-9876543212',
            'email' => 'lab@cityhospital.com',
            'address' => '78, Hospital Road, City',
            'payment_terms' => 'monthly',
        ]);

        // ── Permissions ──────────────────────────────────────────
        $this->seedPermissions();

        // ── Additional Staff ─────────────────────────────────────
        $receptionist = User::factory()->create([
            'name' => 'Priya Patel',
            'email' => 'priya@pathlab.com',
            'password' => 'password',
            'role' => 'receptionist',
            'phone' => '9876543220',
        ]);

        $technician = User::factory()->create([
            'name' => 'Amit Singh',
            'email' => 'amit@pathlab.com',
            'password' => 'password',
            'role' => 'technician',
            'phone' => '9876543221',
        ]);

        $doctor = User::factory()->create([
            'name' => 'Dr. Meena Gupta',
            'email' => 'meena@pathlab.com',
            'password' => 'password',
            'role' => 'doctor',
            'phone' => '9876543222',
        ]);

        $admin = User::where('email', 'admin@pathlab.com')->first();

        // ── Referring Doctors (more) ─────────────────────────────
        $drSharma = ReferringDoctor::first();
        $drVerma = ReferringDoctor::create([
            'name' => 'Dr. Verma',
            'phone' => '9876543230',
            'email' => 'dr.verma@clinic.com',
            'clinic_name' => 'Verma Health Centre',
            'address' => '12, Station Road, City',
            'commission_type' => 'fixed',
            'commission_value' => 50.00,
        ]);
        $drKhan = ReferringDoctor::create([
            'name' => 'Dr. Khan',
            'phone' => '9876543231',
            'email' => 'dr.khan@hospital.com',
            'clinic_name' => 'Khan Nursing Home',
            'address' => '99, Mall Road, City',
            'commission_type' => 'percentage',
            'commission_value' => 15.00,
        ]);

        // ── Patients ─────────────────────────────────────────────
        $patients = [];
        $patientData = [
            ['name' => 'Rajesh Kumar', 'age' => 45, 'gender' => 'male', 'phone' => '9811001001', 'email' => 'rajesh@email.com', 'address' => '12, Nehru Nagar, City'],
            ['name' => 'Sunita Devi', 'age' => 38, 'gender' => 'female', 'phone' => '9811001002', 'email' => null, 'address' => '34, Gandhi Road, City'],
            ['name' => 'Mohammed Imran', 'age' => 52, 'gender' => 'male', 'phone' => '9811001003', 'email' => 'imran@email.com', 'address' => '56, MG Road, City'],
            ['name' => 'Anita Sharma', 'age' => 29, 'gender' => 'female', 'phone' => '9811001004', 'email' => null, 'address' => '78, Park Street, City'],
            ['name' => 'Vikram Patel', 'age' => 61, 'gender' => 'male', 'phone' => '9811001005', 'email' => 'vikram@email.com', 'address' => '90, Lake View, City'],
            ['name' => 'Pooja Mishra', 'age' => 25, 'gender' => 'female', 'phone' => '9811001006', 'email' => null, 'address' => '11, Civil Lines, City'],
            ['name' => 'Rahul Verma', 'age' => 8, 'age_unit' => 'years', 'gender' => 'male', 'phone' => '9811001007', 'email' => null, 'address' => '22, Model Town, City'],
            ['name' => 'Deepika Joshi', 'age' => 34, 'gender' => 'female', 'phone' => '9811001008', 'email' => 'deepika@email.com', 'address' => '33, Ashok Vihar, City'],
            ['name' => 'Suresh Yadav', 'age' => 55, 'gender' => 'male', 'phone' => '9811001009', 'email' => null, 'address' => '44, Rajpur Road, City'],
            ['name' => 'Kavita Reddy', 'age' => 42, 'gender' => 'female', 'phone' => '9811001010', 'email' => 'kavita@email.com', 'address' => '55, Banjara Hills, City'],
        ];

        foreach ($patientData as $pd) {
            $patients[] = Patient::create(array_merge(['age_unit' => 'years'], $pd));
        }

        // ── Sample Orders with full workflow ─────────────────────

        // Helper to get random result within or outside range
        $randomResult = function ($param, $abnormal = false) {
            $range = $param->ranges->first();
            if (!$range || is_null($range->min_value)) return null;
            $min = (float) $range->min_value;
            $max = (float) $range->max_value;
            if ($abnormal) {
                return rand(0, 1) ? round($max + ($max * rand(5, 20) / 100), 1) : round($min - ($min * rand(5, 20) / 100), 1);
            }
            return round($min + (($max - $min) * rand(30, 70) / 100), 1);
        };

        // ORDER 1: Rajesh - CBC + Lipid - FULLY APPROVED (has report)
        $order1 = Order::create([
            'patient_id' => $patients[0]->id,
            'source' => 'referral',
            'referring_doctor_id' => $drSharma->id,
            'total_amount' => 850.00,
            'discount' => 0,
            'net_amount' => 850.00,
            'amount_paid' => 850.00,
            'payment_status' => 'paid',
            'payment_mode' => 'upi',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        foreach ([$cbc, $lipidProfile] as $test) {
            $ot = OrderTest::create([
                'order_id' => $order1->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'approved',
                'sample_collected_at' => Carbon::now()->subDays(3),
                'sample_collected_by' => $technician->id,
                'result_entered_at' => Carbon::now()->subDays(3),
                'result_entered_by' => $technician->id,
                'approved_at' => Carbon::now()->subDays(2),
                'approved_by' => $doctor->id,
            ]);
            foreach ($test->parameters as $param) {
                if ($param->field_type === 'numeric') {
                    $isAbnormal = rand(1, 5) === 1;
                    $val = $randomResult($param, $isAbnormal);
                    if ($val !== null) {
                        OrderTestResult::create([
                            'order_test_id' => $ot->id,
                            'test_parameter_id' => $param->id,
                            'result_value' => (string) $val,
                        ]);
                    }
                } elseif ($param->field_type === 'text') {
                    OrderTestResult::create([
                        'order_test_id' => $ot->id,
                        'test_parameter_id' => $param->id,
                        'result_value' => 'Normal',
                    ]);
                }
            }
        }

        Report::create([
            'order_id' => $order1->id,
            'approved_by' => $doctor->id,
            'approved_at' => Carbon::now()->subDays(2),
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // ORDER 2: Sunita - LFT + KFT - FULLY APPROVED (has report)
        $order2 = Order::create([
            'patient_id' => $patients[1]->id,
            'source' => 'walk_in',
            'total_amount' => 900.00,
            'discount' => 100,
            'net_amount' => 800.00,
            'amount_paid' => 800.00,
            'payment_status' => 'paid',
            'payment_mode' => 'cash',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        foreach ([$lft, $kft] as $test) {
            $ot = OrderTest::create([
                'order_id' => $order2->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'approved',
                'sample_collected_at' => Carbon::now()->subDays(2),
                'sample_collected_by' => $technician->id,
                'result_entered_at' => Carbon::now()->subDays(2),
                'result_entered_by' => $technician->id,
                'approved_at' => Carbon::now()->subDays(1),
                'approved_by' => $doctor->id,
            ]);
            foreach ($test->parameters as $param) {
                if ($param->field_type === 'numeric') {
                    $val = $randomResult($param, rand(1, 4) === 1);
                    if ($val !== null) {
                        OrderTestResult::create(['order_test_id' => $ot->id, 'test_parameter_id' => $param->id, 'result_value' => (string) $val]);
                    }
                }
            }
        }

        Report::create([
            'order_id' => $order2->id,
            'approved_by' => $doctor->id,
            'approved_at' => Carbon::now()->subDays(1),
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // ORDER 3: Mohammed - Thyroid + BSF - RESULTS ENTERED, PENDING APPROVAL
        $order3 = Order::create([
            'patient_id' => $patients[2]->id,
            'source' => 'referral',
            'referring_doctor_id' => $drVerma->id,
            'total_amount' => 480.00,
            'discount' => 0,
            'net_amount' => 480.00,
            'amount_paid' => 480.00,
            'payment_status' => 'paid',
            'payment_mode' => 'card',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subDay(),
        ]);

        foreach ([$thyroid, $bsFasting] as $test) {
            $ot = OrderTest::create([
                'order_id' => $order3->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'completed',
                'sample_collected_at' => Carbon::now()->subDay(),
                'sample_collected_by' => $technician->id,
                'result_entered_at' => Carbon::now()->subHours(6),
                'result_entered_by' => $technician->id,
            ]);
            foreach ($test->parameters as $param) {
                if ($param->field_type === 'numeric') {
                    $val = $randomResult($param, rand(1, 3) === 1);
                    if ($val !== null) {
                        OrderTestResult::create(['order_test_id' => $ot->id, 'test_parameter_id' => $param->id, 'result_value' => (string) $val]);
                    }
                }
            }
        }

        // ORDER 4: Anita - CBC - SAMPLE COLLECTED, PENDING RESULTS
        $order4 = Order::create([
            'patient_id' => $patients[3]->id,
            'source' => 'walk_in',
            'total_amount' => 350.00,
            'discount' => 0,
            'net_amount' => 350.00,
            'amount_paid' => 200.00,
            'payment_status' => 'partial',
            'payment_mode' => 'cash',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subHours(4),
        ]);

        OrderTest::create([
            'order_id' => $order4->id,
            'test_id' => $cbc->id,
            'price' => $cbc->price,
            'status' => 'sample_collected',
            'sample_collected_at' => Carbon::now()->subHours(3),
            'sample_collected_by' => $technician->id,
        ]);

        // ORDER 5: Vikram - Full Body Package - PENDING SAMPLE COLLECTION
        $order5 = Order::create([
            'patient_id' => $patients[4]->id,
            'source' => 'referral',
            'referring_doctor_id' => $drKhan->id,
            'total_amount' => 1999.00,
            'discount' => 0,
            'net_amount' => 1999.00,
            'amount_paid' => 1999.00,
            'payment_status' => 'paid',
            'payment_mode' => 'upi',
            'created_by' => $admin->id,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        foreach ([$cbc, $bsFasting, $lipidProfile, $lft, $kft, $thyroid, $urineRoutine] as $test) {
            OrderTest::create([
                'order_id' => $order5->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'pending',
            ]);
        }

        // ORDER 6: Pooja - HbA1c + BSF + BSPP - PENDING SAMPLE
        $order6 = Order::create([
            'patient_id' => $patients[5]->id,
            'source' => 'walk_in',
            'total_amount' => 510.00,
            'discount' => 10,
            'net_amount' => 500.00,
            'amount_paid' => 0,
            'payment_status' => 'pending',
            'payment_mode' => 'cash',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subHour(),
        ]);

        foreach ([$hba1c, $bsFasting, $bsPP] as $test) {
            OrderTest::create([
                'order_id' => $order6->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'pending',
            ]);
        }

        // ORDER 7: Deepika - Urine R/M + CBC - RESULTS ENTERED, PENDING APPROVAL
        $order7 = Order::create([
            'patient_id' => $patients[7]->id,
            'source' => 'b2b',
            'b2b_client_id' => B2bClient::first()->id,
            'total_amount' => 450.00,
            'discount' => 50,
            'net_amount' => 400.00,
            'amount_paid' => 0,
            'payment_status' => 'pending',
            'payment_mode' => 'cash',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subHours(5),
        ]);

        foreach ([$urineRoutine, $cbc] as $test) {
            $ot = OrderTest::create([
                'order_id' => $order7->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'completed',
                'sample_collected_at' => Carbon::now()->subHours(4),
                'sample_collected_by' => $technician->id,
                'result_entered_at' => Carbon::now()->subHours(2),
                'result_entered_by' => $technician->id,
            ]);
            foreach ($test->parameters as $param) {
                if ($param->field_type === 'numeric') {
                    $val = $randomResult($param, rand(1, 4) === 1);
                    if ($val !== null) {
                        OrderTestResult::create(['order_test_id' => $ot->id, 'test_parameter_id' => $param->id, 'result_value' => (string) $val]);
                    }
                } elseif ($param->field_type === 'text') {
                    OrderTestResult::create(['order_test_id' => $ot->id, 'test_parameter_id' => $param->id, 'result_value' => 'Normal']);
                } elseif ($param->field_type === 'options') {
                    OrderTestResult::create(['order_test_id' => $ot->id, 'test_parameter_id' => $param->id, 'result_value' => 'Nil']);
                }
            }
        }

        // ORDER 8: Suresh - ESR + Blood Group - SAMPLE COLLECTED, PENDING RESULTS
        $order8 = Order::create([
            'patient_id' => $patients[8]->id,
            'source' => 'referral',
            'referring_doctor_id' => $drSharma->id,
            'total_amount' => 180.00,
            'discount' => 0,
            'net_amount' => 180.00,
            'amount_paid' => 180.00,
            'payment_status' => 'paid',
            'payment_mode' => 'cash',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subHours(3),
        ]);

        foreach ([$esr, $bloodGroup] as $test) {
            OrderTest::create([
                'order_id' => $order8->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'sample_collected',
                'sample_collected_at' => Carbon::now()->subHours(2),
                'sample_collected_by' => $technician->id,
            ]);
        }

        // ORDER 9: Kavita - Lipid + Thyroid - APPROVED (today's report)
        $order9 = Order::create([
            'patient_id' => $patients[9]->id,
            'source' => 'walk_in',
            'total_amount' => 900.00,
            'discount' => 0,
            'net_amount' => 900.00,
            'amount_paid' => 500.00,
            'payment_status' => 'partial',
            'payment_mode' => 'cash',
            'created_by' => $receptionist->id,
            'created_at' => Carbon::now()->subHours(8),
        ]);

        foreach ([$lipidProfile, $thyroid] as $test) {
            $ot = OrderTest::create([
                'order_id' => $order9->id,
                'test_id' => $test->id,
                'price' => $test->price,
                'status' => 'approved',
                'sample_collected_at' => Carbon::now()->subHours(7),
                'sample_collected_by' => $technician->id,
                'result_entered_at' => Carbon::now()->subHours(5),
                'result_entered_by' => $technician->id,
                'approved_at' => Carbon::now()->subHours(2),
                'approved_by' => $doctor->id,
            ]);
            foreach ($test->parameters as $param) {
                if ($param->field_type === 'numeric') {
                    $val = $randomResult($param, rand(1, 3) === 1);
                    if ($val !== null) {
                        OrderTestResult::create(['order_test_id' => $ot->id, 'test_parameter_id' => $param->id, 'result_value' => (string) $val]);
                    }
                }
            }
        }

        Report::create([
            'order_id' => $order9->id,
            'approved_by' => $doctor->id,
            'approved_at' => Carbon::now()->subHours(2),
        ]);

        // ORDER 10: Rahul (child) - CBC - PENDING SAMPLE (today)
        $order10 = Order::create([
            'patient_id' => $patients[6]->id,
            'source' => 'walk_in',
            'total_amount' => 350.00,
            'discount' => 0,
            'net_amount' => 350.00,
            'amount_paid' => 350.00,
            'payment_status' => 'paid',
            'payment_mode' => 'cash',
            'created_by' => $admin->id,
            'created_at' => Carbon::now(),
        ]);

        OrderTest::create([
            'order_id' => $order10->id,
            'test_id' => $cbc->id,
            'price' => $cbc->price,
            'status' => 'pending',
        ]);

        // ── Bulk test data (40-50 records per table) ──
        $this->call(BulkTestDataSeeder::class);
    }

    /**
     * Helper: create a TestParameter and its ParameterRange records.
     */
    private function createParameterWithRanges(
        int $testId,
        string $name,
        string $fieldType,
        ?string $unit,
        int $sortOrder,
        array $ranges
    ): TestParameter {
        $param = TestParameter::create([
            'test_id' => $testId,
            'name' => $name,
            'field_type' => $fieldType,
            'unit' => $unit,
            'sort_order' => $sortOrder,
        ]);

        foreach ($ranges as $range) {
            ParameterRange::create(array_merge(
                ['test_parameter_id' => $param->id],
                $range,
            ));
        }

        return $param;
    }

    private function seedPermissions(): void
    {
        $perms = [
            ['name' => 'View Dashboard', 'slug' => 'view_dashboard', 'group' => 'General'],
            ['name' => 'Manage Patients', 'slug' => 'manage_patients', 'group' => 'Front Desk'],
            ['name' => 'Create Orders', 'slug' => 'create_orders', 'group' => 'Front Desk'],
            ['name' => 'View Orders', 'slug' => 'view_orders', 'group' => 'Front Desk'],
            ['name' => 'Collect Samples', 'slug' => 'collect_samples', 'group' => 'Lab'],
            ['name' => 'Enter Results', 'slug' => 'enter_results', 'group' => 'Lab'],
            ['name' => 'Approve Reports', 'slug' => 'approve_reports', 'group' => 'Doctor'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'group' => 'Reports'],
            ['name' => 'Download Reports', 'slug' => 'download_reports', 'group' => 'Reports'],
            ['name' => 'Manage Test Categories', 'slug' => 'manage_test_categories', 'group' => 'Administration'],
            ['name' => 'Manage Tests', 'slug' => 'manage_tests', 'group' => 'Administration'],
            ['name' => 'Manage Packages', 'slug' => 'manage_packages', 'group' => 'Administration'],
            ['name' => 'Manage Referring Doctors', 'slug' => 'manage_referring_doctors', 'group' => 'Administration'],
            ['name' => 'Manage B2B Clients', 'slug' => 'manage_b2b_clients', 'group' => 'Administration'],
            ['name' => 'Manage Staff', 'slug' => 'manage_users', 'group' => 'Administration'],
            ['name' => 'Manage Permissions', 'slug' => 'manage_permissions', 'group' => 'Administration'],
            ['name' => 'View Commission Report', 'slug' => 'view_commission_report', 'group' => 'Administration'],
        ];

        foreach ($perms as $p) {
            Permission::create($p);
        }

        $all = Permission::all();
        $defaults = [
            'admin' => $all->pluck('id')->toArray(),
            'receptionist' => $all->whereIn('slug', ['view_dashboard', 'manage_patients', 'create_orders', 'view_orders', 'view_reports', 'download_reports'])->pluck('id')->toArray(),
            'technician' => $all->whereIn('slug', ['view_dashboard', 'view_orders', 'collect_samples', 'enter_results', 'view_reports'])->pluck('id')->toArray(),
            'doctor' => $all->whereIn('slug', ['view_dashboard', 'view_orders', 'approve_reports', 'view_reports', 'download_reports'])->pluck('id')->toArray(),
        ];

        foreach ($defaults as $role => $permIds) {
            foreach ($permIds as $id) {
                RolePermission::create(['role' => $role, 'permission_id' => $id]);
            }
        }
    }
}

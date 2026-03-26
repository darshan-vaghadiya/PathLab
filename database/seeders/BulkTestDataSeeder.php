<?php

namespace Database\Seeders;

use App\Models\B2bClient;
use App\Models\Order;
use App\Models\OrderTest;
use App\Models\OrderTestResult;
use App\Models\Patient;
use App\Models\ReferringDoctor;
use App\Models\Report;
use App\Models\Test;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class BulkTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_IN');

        $admin = User::where('role', 'admin')->first();
        $receptionist = User::where('role', 'receptionist')->first();
        $technician = User::where('role', 'technician')->first();
        $doctor = User::where('role', 'doctor')->first();

        $tests = Test::with('parameters.ranges')->where('is_active', true)->get();
        $referringDoctors = ReferringDoctor::where('is_active', true)->get();
        $b2bClients = B2bClient::where('is_active', true)->get();

        // ── 40 more Referring Doctors ──
        $indianDoctorNames = ['Agarwal','Bansal','Chauhan','Desai','Ghosh','Iyer','Jain','Kapoor','Malhotra','Nair','Pandey','Rao','Saxena','Tiwari','Upadhyay','Bhatia','Choudhary','Dubey','Goyal','Hegde','Khanna','Luthra','Mehra','Oberoi','Pillai','Rathore','Sinha','Thakur','Varma','Yadav','Ahuja','Bhatt','Deshpande','Grover','Kashyap','Mahajan','Naik','Patil','Reddy','Sethi'];
        foreach ($indianDoctorNames as $i => $surname) {
            $rd = ReferringDoctor::create([
                'name' => 'Dr. ' . $faker->firstName() . ' ' . $surname,
                'phone' => $faker->numerify('98########'),
                'email' => strtolower($surname) . $faker->numberBetween(1, 99) . '@clinic.com',
                'clinic_name' => $surname . ' ' . $faker->randomElement(['Clinic', 'Hospital', 'Health Centre', 'Nursing Home', 'Medical Centre']),
                'address' => $faker->address(),
                'commission_type' => $faker->randomElement(['percentage', 'fixed']),
                'commission_value' => $faker->randomElement([5, 8, 10, 12, 15, 20, 25, 30, 40, 50]),
                'is_active' => $faker->boolean(90),
            ]);
            $referringDoctors->push($rd);
        }

        // ── 5 more B2B Clients ──
        $b2bNames = ['Metro Hospital', 'Sunrise Diagnostics', 'Green Valley Clinic', 'Apollo Health Lab', 'LifeCare Medical'];
        foreach ($b2bNames as $name) {
            $bc = B2bClient::create([
                'name' => $name,
                'contact_person' => $faker->name(),
                'phone' => $faker->numerify('98########'),
                'email' => strtolower(str_replace(' ', '', $name)) . '@lab.com',
                'address' => $faker->address(),
                'payment_terms' => $faker->randomElement(['monthly', 'weekly', 'immediate']),
                'is_active' => true,
            ]);
            $b2bClients->push($bc);
        }

        // ── 50 Patients ──
        $patients = [];
        for ($i = 0; $i < 50; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $patients[] = Patient::create([
                'name' => $faker->firstName($gender) . ' ' . $faker->lastName(),
                'age' => $faker->numberBetween(2, 80),
                'age_unit' => $faker->randomElement(['years', 'years', 'years', 'months']),
                'gender' => $gender,
                'phone' => $faker->numerify('98########'),
                'email' => $faker->boolean(30) ? $faker->email() : null,
                'address' => $faker->address(),
            ]);
        }

        // Also include existing patients
        $existingPatients = Patient::whereNotIn('id', collect($patients)->pluck('id'))->get()->all();
        $allPatients = array_merge($existingPatients, $patients);

        // ── 50 Orders across different stages and dates ──
        $statuses = ['pending', 'sample_collected', 'completed', 'approved'];
        $sources = ['walk_in', 'referral', 'b2b', 'home_visit'];
        $paymentModes = ['cash', 'upi', 'card', 'cash', 'cash', 'upi'];

        for ($i = 0; $i < 50; $i++) {
            $patient = $faker->randomElement($allPatients);
            $source = $faker->randomElement($sources);
            $daysAgo = $faker->numberBetween(0, 30);
            $createdAt = Carbon::now()->subDays($daysAgo)->subHours($faker->numberBetween(0, 12));

            // Pick 1-4 random tests
            $orderTests = $tests->random($faker->numberBetween(1, min(4, $tests->count())));
            $totalAmount = $orderTests->sum('price');
            $discount = $faker->randomElement([0, 0, 0, 0, 10, 20, 50, 100]);
            $netAmount = max(0, $totalAmount - $discount);
            $amountPaid = $faker->randomElement([$netAmount, $netAmount, $netAmount, round($netAmount * 0.5), 0]);
            $paymentStatus = $amountPaid >= $netAmount ? 'paid' : ($amountPaid > 0 ? 'partial' : 'pending');

            $orderData = [
                'patient_id' => $patient->id,
                'source' => $source,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'amount_paid' => $amountPaid,
                'payment_status' => $paymentStatus,
                'payment_mode' => $faker->randomElement($paymentModes),
                'created_by' => $faker->randomElement([$admin->id, $receptionist->id]),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if ($source === 'referral' && $referringDoctors->count()) {
                $orderData['referring_doctor_id'] = $referringDoctors->random()->id;
            }
            if ($source === 'b2b' && $b2bClients->count()) {
                $orderData['b2b_client_id'] = $b2bClients->random()->id;
            }

            $order = Order::create($orderData);

            // Decide what workflow stage this order is at
            $stage = $faker->randomElement([
                'pending', 'pending',
                'sample_collected', 'sample_collected',
                'completed', 'completed',
                'approved', 'approved', 'approved',
            ]);

            $allApproved = true;

            foreach ($orderTests as $test) {
                $otStatus = $stage;
                $otData = [
                    'order_id' => $order->id,
                    'test_id' => $test->id,
                    'price' => $test->price,
                    'status' => $otStatus,
                ];

                if (in_array($otStatus, ['sample_collected', 'completed', 'approved'])) {
                    $otData['sample_collected_at'] = $createdAt->copy()->addHours($faker->numberBetween(1, 3));
                    $otData['sample_collected_by'] = $technician->id;
                }
                if (in_array($otStatus, ['completed', 'approved'])) {
                    $otData['result_entered_at'] = $createdAt->copy()->addHours($faker->numberBetween(4, 8));
                    $otData['result_entered_by'] = $technician->id;
                }
                if ($otStatus === 'approved') {
                    $otData['approved_at'] = $createdAt->copy()->addHours($faker->numberBetween(9, 16));
                    $otData['approved_by'] = $doctor->id;
                } else {
                    $allApproved = false;
                }

                $ot = OrderTest::create($otData);

                // Enter results for completed/approved tests
                if (in_array($otStatus, ['completed', 'approved'])) {
                    $parameters = $test->parameters ?? collect();
                    foreach ($parameters->where('is_active', true) as $param) {
                        $value = null;
                        if ($param->field_type === 'numeric') {
                            $range = $param->ranges->first();
                            if ($range && !is_null($range->min_value) && !is_null($range->max_value)) {
                                $min = (float) $range->min_value;
                                $max = (float) $range->max_value;
                                if ($faker->boolean(20)) {
                                    // Abnormal
                                    $value = $faker->boolean(50)
                                        ? round($max + ($max * $faker->numberBetween(5, 25) / 100), 1)
                                        : round($min - ($min * $faker->numberBetween(5, 25) / 100), 1);
                                } else {
                                    $value = round($min + (($max - $min) * $faker->numberBetween(20, 80) / 100), 1);
                                }
                            } else {
                                $value = $faker->randomFloat(1, 1, 100);
                            }
                        } elseif ($param->field_type === 'text') {
                            $value = $faker->randomElement(['Normal', 'Normal', 'Normal', 'Few seen', 'Nil', 'Clear', 'Pale Yellow']);
                        } elseif ($param->field_type === 'options') {
                            $options = $param->options ?? ['Nil', 'Trace', '+'];
                            $value = $faker->randomElement($options);
                        }

                        if ($value !== null) {
                            OrderTestResult::create([
                                'order_test_id' => $ot->id,
                                'test_parameter_id' => $param->id,
                                'result_value' => (string) $value,
                            ]);
                        }
                    }

                    // Random remarks
                    if ($faker->boolean(20)) {
                        $ot->update(['result_remarks' => $faker->randomElement([
                            'Please correlate clinically.',
                            'Repeat test advised after 2 weeks.',
                            'Values slightly elevated, monitor.',
                            'Within normal limits.',
                            'Further investigation recommended.',
                        ])]);
                    }
                }
            }

            // Create report for fully approved orders
            if ($allApproved && $stage === 'approved') {
                Report::create([
                    'order_id' => $order->id,
                    'approved_by' => $doctor->id,
                    'approved_at' => $createdAt->copy()->addHours($faker->numberBetween(10, 18)),
                    'created_at' => $createdAt->copy()->addHours($faker->numberBetween(10, 18)),
                ]);
            }
        }
    }
}

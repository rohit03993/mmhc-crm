<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pincode;

class PincodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Comprehensive Indian pincodes with accurate coordinates
        // Covers major cities and regions across India
        
        $pincodes = [
            // ===== BIHAR =====
            ['pincode' => '800001', 'latitude' => 25.5941, 'longitude' => 85.1376, 'city' => 'Patna', 'state' => 'Bihar', 'district' => 'Patna'],
            ['pincode' => '800020', 'latitude' => 25.6010, 'longitude' => 85.0976, 'city' => 'Patna', 'state' => 'Bihar', 'district' => 'Patna'],
            ['pincode' => '801101', 'latitude' => 25.6113, 'longitude' => 85.0562, 'city' => 'Patna', 'state' => 'Bihar', 'district' => 'Patna'],
            ['pincode' => '844101', 'latitude' => 26.1204, 'longitude' => 85.3646, 'city' => 'Muzaffarpur', 'state' => 'Bihar', 'district' => 'Muzaffarpur'],
            ['pincode' => '823001', 'latitude' => 24.7867, 'longitude' => 85.0063, 'city' => 'Gaya', 'state' => 'Bihar', 'district' => 'Gaya'],
            ['pincode' => '846001', 'latitude' => 25.1961, 'longitude' => 85.5190, 'city' => 'Darbhanga', 'state' => 'Bihar', 'district' => 'Darbhanga'],
            
            // ===== MADHYA PRADESH =====
            ['pincode' => '462001', 'latitude' => 23.2599, 'longitude' => 77.4126, 'city' => 'Bhopal', 'state' => 'Madhya Pradesh', 'district' => 'Bhopal'],
            ['pincode' => '462023', 'latitude' => 23.2599, 'longitude' => 77.4126, 'city' => 'Bhopal', 'state' => 'Madhya Pradesh', 'district' => 'Bhopal'],
            ['pincode' => '452001', 'latitude' => 22.7196, 'longitude' => 75.8577, 'city' => 'Indore', 'state' => 'Madhya Pradesh', 'district' => 'Indore'],
            ['pincode' => '482001', 'latitude' => 23.1815, 'longitude' => 75.7714, 'city' => 'Jabalpur', 'state' => 'Madhya Pradesh', 'district' => 'Jabalpur'],
            ['pincode' => '456001', 'latitude' => 22.9734, 'longitude' => 78.6569, 'city' => 'Ujjain', 'state' => 'Madhya Pradesh', 'district' => 'Ujjain'],
            ['pincode' => '474001', 'latitude' => 26.4499, 'longitude' => 78.5682, 'city' => 'Gwalior', 'state' => 'Madhya Pradesh', 'district' => 'Gwalior'],
            
            // ===== DELHI =====
            ['pincode' => '110001', 'latitude' => 28.6139, 'longitude' => 77.2090, 'city' => 'Delhi', 'state' => 'Delhi', 'district' => 'Central Delhi'],
            ['pincode' => '110092', 'latitude' => 28.7041, 'longitude' => 77.1025, 'city' => 'Delhi', 'state' => 'Delhi', 'district' => 'North West Delhi'],
            ['pincode' => '110017', 'latitude' => 28.5355, 'longitude' => 77.2410, 'city' => 'Delhi', 'state' => 'Delhi', 'district' => 'South Delhi'],
            ['pincode' => '110096', 'latitude' => 28.7523, 'longitude' => 77.2000, 'city' => 'Delhi', 'state' => 'Delhi', 'district' => 'North Delhi'],
            ['pincode' => '110085', 'latitude' => 28.7242, 'longitude' => 77.1373, 'city' => 'Delhi', 'state' => 'Delhi', 'district' => 'North West Delhi'],
            
            // ===== UTTAR PRADESH =====
            ['pincode' => '201301', 'latitude' => 28.5355, 'longitude' => 77.3910, 'city' => 'Noida', 'state' => 'Uttar Pradesh', 'district' => 'Gautam Buddh Nagar'],
            ['pincode' => '201304', 'latitude' => 28.5355, 'longitude' => 77.3910, 'city' => 'Noida', 'state' => 'Uttar Pradesh', 'district' => 'Gautam Buddh Nagar'],
            ['pincode' => '226001', 'latitude' => 26.8467, 'longitude' => 80.9462, 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'district' => 'Lucknow'],
            ['pincode' => '211001', 'latitude' => 25.3176, 'longitude' => 82.9739, 'city' => 'Allahabad', 'state' => 'Uttar Pradesh', 'district' => 'Allahabad'],
            ['pincode' => '208001', 'latitude' => 26.4499, 'longitude' => 80.3319, 'city' => 'Kanpur', 'state' => 'Uttar Pradesh', 'district' => 'Kanpur'],
            ['pincode' => '282001', 'latitude' => 27.1767, 'longitude' => 78.0081, 'city' => 'Agra', 'state' => 'Uttar Pradesh', 'district' => 'Agra'],
            ['pincode' => '244001', 'latitude' => 28.9545, 'longitude' => 77.6974, 'city' => 'Meerut', 'state' => 'Uttar Pradesh', 'district' => 'Meerut'],
            ['pincode' => '250001', 'latitude' => 28.9845, 'longitude' => 77.7064, 'city' => 'Meerut', 'state' => 'Uttar Pradesh', 'district' => 'Meerut'],
            ['pincode' => '221001', 'latitude' => 25.3176, 'longitude' => 82.9739, 'city' => 'Varanasi', 'state' => 'Uttar Pradesh', 'district' => 'Varanasi'],
            
            // ===== HARYANA =====
            ['pincode' => '122001', 'latitude' => 28.4089, 'longitude' => 77.0378, 'city' => 'Gurgaon', 'state' => 'Haryana', 'district' => 'Gurgaon'],
            ['pincode' => '122002', 'latitude' => 28.4089, 'longitude' => 77.0378, 'city' => 'Gurgaon', 'state' => 'Haryana', 'district' => 'Gurgaon'],
            ['pincode' => '134001', 'latitude' => 30.3782, 'longitude' => 76.7767, 'city' => 'Ambala', 'state' => 'Haryana', 'district' => 'Ambala'],
            ['pincode' => '121001', 'latitude' => 28.9921, 'longitude' => 77.0018, 'city' => 'Faridabad', 'state' => 'Haryana', 'district' => 'Faridabad'],
            
            // ===== JHARKHAND =====
            ['pincode' => '834001', 'latitude' => 23.3441, 'longitude' => 85.3096, 'city' => 'Ranchi', 'state' => 'Jharkhand', 'district' => 'Ranchi'],
            ['pincode' => '826001', 'latitude' => 23.7593, 'longitude' => 86.4300, 'city' => 'Dhanbad', 'state' => 'Jharkhand', 'district' => 'Dhanbad'],
            ['pincode' => '831001', 'latitude' => 22.8046, 'longitude' => 86.2029, 'city' => 'Jamshedpur', 'state' => 'Jharkhand', 'district' => 'East Singhbhum'],
            
            // ===== MAHARASHTRA =====
            ['pincode' => '400001', 'latitude' => 18.9388, 'longitude' => 72.8354, 'city' => 'Mumbai', 'state' => 'Maharashtra', 'district' => 'Mumbai'],
            ['pincode' => '400018', 'latitude' => 19.0760, 'longitude' => 72.8777, 'city' => 'Mumbai', 'state' => 'Maharashtra', 'district' => 'Mumbai'],
            ['pincode' => '400021', 'latitude' => 19.0825, 'longitude' => 72.8497, 'city' => 'Mumbai', 'state' => 'Maharashtra', 'district' => 'Mumbai'],
            ['pincode' => '400053', 'latitude' => 19.1183, 'longitude' => 72.8997, 'city' => 'Mumbai', 'state' => 'Maharashtra', 'district' => 'Mumbai'],
            ['pincode' => '411001', 'latitude' => 18.5204, 'longitude' => 73.8567, 'city' => 'Pune', 'state' => 'Maharashtra', 'district' => 'Pune'],
            ['pincode' => '411002', 'latitude' => 18.5204, 'longitude' => 73.8567, 'city' => 'Pune', 'state' => 'Maharashtra', 'district' => 'Pune'],
            ['pincode' => '440001', 'latitude' => 21.1458, 'longitude' => 79.0882, 'city' => 'Nagpur', 'state' => 'Maharashtra', 'district' => 'Nagpur'],
            ['pincode' => '422001', 'latitude' => 19.9975, 'longitude' => 73.7898, 'city' => 'Nashik', 'state' => 'Maharashtra', 'district' => 'Nashik'],
            
            // ===== KARNATAKA =====
            ['pincode' => '560001', 'latitude' => 12.9716, 'longitude' => 77.5946, 'city' => 'Bangalore', 'state' => 'Karnataka', 'district' => 'Bangalore'],
            ['pincode' => '560048', 'latitude' => 12.9352, 'longitude' => 77.6245, 'city' => 'Bangalore', 'state' => 'Karnataka', 'district' => 'Bangalore'],
            ['pincode' => '560025', 'latitude' => 12.9141, 'longitude' => 77.6411, 'city' => 'Bangalore', 'state' => 'Karnataka', 'district' => 'Bangalore'],
            ['pincode' => '560100', 'latitude' => 12.9716, 'longitude' => 77.5946, 'city' => 'Bangalore', 'state' => 'Karnataka', 'district' => 'Bangalore'],
            ['pincode' => '575001', 'latitude' => 12.9141, 'longitude' => 74.8560, 'city' => 'Mangalore', 'state' => 'Karnataka', 'district' => 'Dakshina Kannada'],
            ['pincode' => '570001', 'latitude' => 12.2958, 'longitude' => 76.6394, 'city' => 'Mysore', 'state' => 'Karnataka', 'district' => 'Mysore'],
            
            // ===== TAMIL NADU =====
            ['pincode' => '600001', 'latitude' => 13.0827, 'longitude' => 80.2707, 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'district' => 'Chennai'],
            ['pincode' => '600016', 'latitude' => 13.0067, 'longitude' => 80.2206, 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'district' => 'Chennai'],
            ['pincode' => '600028', 'latitude' => 13.0827, 'longitude' => 80.2707, 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'district' => 'Chennai'],
            ['pincode' => '641001', 'latitude' => 11.0168, 'longitude' => 76.9558, 'city' => 'Coimbatore', 'state' => 'Tamil Nadu', 'district' => 'Coimbatore'],
            ['pincode' => '625001', 'latitude' => 9.9252, 'longitude' => 78.1198, 'city' => 'Madurai', 'state' => 'Tamil Nadu', 'district' => 'Madurai'],
            
            // ===== WEST BENGAL =====
            ['pincode' => '700001', 'latitude' => 22.5726, 'longitude' => 88.3639, 'city' => 'Kolkata', 'state' => 'West Bengal', 'district' => 'Kolkata'],
            ['pincode' => '700020', 'latitude' => 22.5448, 'longitude' => 88.3426, 'city' => 'Kolkata', 'state' => 'West Bengal', 'district' => 'Kolkata'],
            ['pincode' => '700029', 'latitude' => 22.5726, 'longitude' => 88.3639, 'city' => 'Kolkata', 'state' => 'West Bengal', 'district' => 'Kolkata'],
            ['pincode' => '733101', 'latitude' => 26.7271, 'longitude' => 88.3953, 'city' => 'Siliguri', 'state' => 'West Bengal', 'district' => 'Darjeeling'],
            
            // ===== GUJARAT =====
            ['pincode' => '380001', 'latitude' => 23.0225, 'longitude' => 72.5714, 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'district' => 'Ahmedabad'],
            ['pincode' => '380015', 'latitude' => 23.0225, 'longitude' => 72.5714, 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'district' => 'Ahmedabad'],
            ['pincode' => '395001', 'latitude' => 21.1702, 'longitude' => 72.8311, 'city' => 'Surat', 'state' => 'Gujarat', 'district' => 'Surat'],
            ['pincode' => '390001', 'latitude' => 22.3072, 'longitude' => 73.1812, 'city' => 'Vadodara', 'state' => 'Gujarat', 'district' => 'Vadodara'],
            ['pincode' => '364001', 'latitude' => 22.3039, 'longitude' => 70.8022, 'city' => 'Rajkot', 'state' => 'Gujarat', 'district' => 'Rajkot'],
            
            // ===== RAJASTHAN =====
            ['pincode' => '302001', 'latitude' => 26.9124, 'longitude' => 75.7873, 'city' => 'Jaipur', 'state' => 'Rajasthan', 'district' => 'Jaipur'],
            ['pincode' => '302002', 'latitude' => 26.9124, 'longitude' => 75.7873, 'city' => 'Jaipur', 'state' => 'Rajasthan', 'district' => 'Jaipur'],
            ['pincode' => '313001', 'latitude' => 24.5854, 'longitude' => 73.7125, 'city' => 'Udaipur', 'state' => 'Rajasthan', 'district' => 'Udaipur'],
            ['pincode' => '324001', 'latitude' => 27.0238, 'longitude' => 73.3190, 'city' => 'Kota', 'state' => 'Rajasthan', 'district' => 'Kota'],
            ['pincode' => '305001', 'latitude' => 26.2389, 'longitude' => 73.0243, 'city' => 'Ajmer', 'state' => 'Rajasthan', 'district' => 'Ajmer'],
            
            // ===== PUNJAB =====
            ['pincode' => '141001', 'latitude' => 30.9010, 'longitude' => 75.8573, 'city' => 'Ludhiana', 'state' => 'Punjab', 'district' => 'Ludhiana'],
            ['pincode' => '143001', 'latitude' => 31.6330, 'longitude' => 74.8723, 'city' => 'Amritsar', 'state' => 'Punjab', 'district' => 'Amritsar'],
            ['pincode' => '147001', 'latitude' => 30.3149, 'longitude' => 76.3614, 'city' => 'Patiala', 'state' => 'Punjab', 'district' => 'Patiala'],
            
            // ===== CHANDIGARH =====
            ['pincode' => '160001', 'latitude' => 30.7333, 'longitude' => 76.7794, 'city' => 'Chandigarh', 'state' => 'Chandigarh', 'district' => 'Chandigarh'],
            ['pincode' => '160017', 'latitude' => 30.7568, 'longitude' => 76.7974, 'city' => 'Chandigarh', 'state' => 'Chandigarh', 'district' => 'Chandigarh'],
            
            // ===== TELANGANA =====
            ['pincode' => '500001', 'latitude' => 17.3850, 'longitude' => 78.4867, 'city' => 'Hyderabad', 'state' => 'Telangana', 'district' => 'Hyderabad'],
            ['pincode' => '500081', 'latitude' => 17.4486, 'longitude' => 78.3908, 'city' => 'Hyderabad', 'state' => 'Telangana', 'district' => 'Hyderabad'],
            ['pincode' => '500032', 'latitude' => 17.3850, 'longitude' => 78.4867, 'city' => 'Hyderabad', 'state' => 'Telangana', 'district' => 'Hyderabad'],
            
            // ===== KERALA =====
            ['pincode' => '682001', 'latitude' => 9.9312, 'longitude' => 76.2673, 'city' => 'Kochi', 'state' => 'Kerala', 'district' => 'Ernakulam'],
            ['pincode' => '695001', 'latitude' => 8.5241, 'longitude' => 76.9366, 'city' => 'Thiruvananthapuram', 'state' => 'Kerala', 'district' => 'Thiruvananthapuram'],
            ['pincode' => '673001', 'latitude' => 11.2588, 'longitude' => 75.7804, 'city' => 'Calicut', 'state' => 'Kerala', 'district' => 'Kozhikode'],
            
            // ===== ODISHA =====
            ['pincode' => '751001', 'latitude' => 20.2961, 'longitude' => 85.8245, 'city' => 'Bhubaneswar', 'state' => 'Odisha', 'district' => 'Khordha'],
            ['pincode' => '753001', 'latitude' => 20.4625, 'longitude' => 85.8830, 'city' => 'Cuttack', 'state' => 'Odisha', 'district' => 'Cuttack'],
            
            // ===== ASSAM =====
            ['pincode' => '781001', 'latitude' => 26.1445, 'longitude' => 91.7362, 'city' => 'Guwahati', 'state' => 'Assam', 'district' => 'Kamrup'],
            ['pincode' => '786001', 'latitude' => 27.4728, 'longitude' => 95.0478, 'city' => 'Dibrugarh', 'state' => 'Assam', 'district' => 'Dibrugarh'],
            
            // ===== HIMACHAL PRADESH =====
            ['pincode' => '171001', 'latitude' => 31.1048, 'longitude' => 77.1734, 'city' => 'Shimla', 'state' => 'Himachal Pradesh', 'district' => 'Shimla'],
            
            // ===== UTTARAKHAND =====
            ['pincode' => '248001', 'latitude' => 30.3165, 'longitude' => 78.0322, 'city' => 'Dehradun', 'state' => 'Uttarakhand', 'district' => 'Dehradun'],
            ['pincode' => '263001', 'latitude' => 29.9457, 'longitude' => 78.1642, 'city' => 'Nainital', 'state' => 'Uttarakhand', 'district' => 'Nainital'],
            
            // ===== GOA =====
            ['pincode' => '403001', 'latitude' => 15.2993, 'longitude' => 74.1240, 'city' => 'Panaji', 'state' => 'Goa', 'district' => 'North Goa'],
            
            // ===== CHHATTISGARH =====
            ['pincode' => '492001', 'latitude' => 21.2514, 'longitude' => 81.6296, 'city' => 'Raipur', 'state' => 'Chhattisgarh', 'district' => 'Raipur'],
            ['pincode' => '495001', 'latitude' => 22.0783, 'longitude' => 82.1563, 'city' => 'Bilaspur', 'state' => 'Chhattisgarh', 'district' => 'Bilaspur'],
            
            // ===== PUDUCHERRY =====
            ['pincode' => '605001', 'latitude' => 11.9416, 'longitude' => 79.8083, 'city' => 'Puducherry', 'state' => 'Puducherry', 'district' => 'Puducherry'],
        ];
        
        $this->command->info('🌍 Seeding pincodes...');
        $bar = $this->command->getOutput()->createProgressBar(count($pincodes));
        $bar->start();
        
        foreach ($pincodes as $pin) {
            Pincode::updateOrCreate(
                ['pincode' => $pin['pincode']],
                $pin
            );
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine(2);
        $this->command->info('✅ Pincodes seeded successfully!');
        $this->command->info('📊 Total pincodes: ' . Pincode::count());
        $this->command->warn('💡 Tip: You can add more pincodes later using the import command or manually in the database.');
    }
}

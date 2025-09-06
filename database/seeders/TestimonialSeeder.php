<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name'=>'Sarah Mitchell','role'=>'Fashion Designer','company'=>'Elegant Threads Co.','quote'=>'Incredible quality and production ready from the first stitch file.','display_order'=>1],
            ['name'=>'Mike Rodriguez','role'=>'Shop Owner','company'=>'Custom Caps Plus','quote'=>'Runs clean on every machine we use. Dependable turnaround.','display_order'=>2],
            ['name'=>'Jennifer Chen','role'=>'Marketing Director','company'=>'TechStart Inc.','quote'=>'Perfect brand accuracy and stitch density — exceeded expectations.','display_order'=>3],
        ];
        foreach($data as $row){ Testimonial::updateOrCreate(['name'=>$row['name'],'company'=>$row['company']??null], $row); }
    }
}

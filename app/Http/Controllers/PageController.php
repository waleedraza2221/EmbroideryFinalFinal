<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about(){ return view('static.about'); }
    public function contact(){ return view('static.contact'); }

    public function serviceEmbroidery(){ return view('static.services.embroidery-digitizing'); }
    public function serviceStitchEstimator(){ return view('static.services.stitch-estimator'); }
    public function serviceVectorTracing(){ return view('static.services.vector-tracing'); }
    public function serviceFormatConverter(){ return view('static.services.format-converter'); }
}
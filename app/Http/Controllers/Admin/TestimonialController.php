<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TestimonialController extends Controller
{
    public function index()
    {
        $items = Testimonial::orderBy('display_order')->paginate(25);
        return view('admin.testimonials.index', compact('items'));
    }

    public function create(){ return view('admin.testimonials.create'); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
    Testimonial::create($data);
    Cache::forget('landing:testimonials');
        return redirect()->route('admin.testimonials.index')->with('success','Created');
    }

    public function edit(Testimonial $testimonial){ return view('admin.testimonials.edit', compact('testimonial')); }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->validated($request, $testimonial->id);
    $testimonial->update($data);
    Cache::forget('landing:testimonials');
        return back()->with('success','Updated');
    }

    public function destroy(Testimonial $testimonial)
    {
    $testimonial->delete();
    Cache::forget('landing:testimonials');
        return back()->with('success','Deleted');
    }

    protected function validated(Request $request, $id=null): array
    {
        return $request->validate([
            'name'=>'required|string|max:120',
            'role'=>'nullable|string|max:120',
            'company'=>'nullable|string|max:150',
            'avatar'=>'nullable|url|max:500',
            'quote'=>'required|string|max:1000',
            'display_order'=>'nullable|integer|min:0',
            'is_active'=>'sometimes|boolean'
        ]);
    }
}
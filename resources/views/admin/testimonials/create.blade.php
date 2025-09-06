@extends('layouts.app')
@section('title','Add Testimonial')
@section('content')
<div class="max-w-3xl mx-auto px-6">
  <h1 class="text-2xl font-bold mb-6">Add Testimonial</h1>
  @if($errors->any())<div class="mb-4 text-sm bg-red-100 text-red-700 px-3 py-2 rounded"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ route('admin.testimonials.store') }}" class="space-y-6">
    @csrf
    @include('admin.testimonials._form')
    <div class="flex justify-end">
      <a href="{{ route('admin.testimonials.index') }}" class="px-4 py-2 border rounded-md text-sm mr-3">Cancel</a>
      <button class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm">Save</button>
    </div>
  </form>
</div>
@endsection

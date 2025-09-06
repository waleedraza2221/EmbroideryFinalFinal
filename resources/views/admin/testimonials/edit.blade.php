@extends('layouts.app')
@section('title','Edit Testimonial')
@section('content')
<div class="max-w-3xl mx-auto px-6">
  <h1 class="text-2xl font-bold mb-6">Edit Testimonial</h1>
  @if(session('success'))<div class="mb-4 text-sm bg-green-100 text-green-700 px-3 py-2 rounded">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="mb-4 text-sm bg-red-100 text-red-700 px-3 py-2 rounded"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ route('admin.testimonials.update',$testimonial) }}" class="space-y-6">
    @csrf @method('PUT')
    @include('admin.testimonials._form')
    <div class="flex justify-between items-center">
      <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-gray-600 hover:underline">Back</a>
      <button class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm">Update</button>
    </div>
  </form>
</div>
@endsection
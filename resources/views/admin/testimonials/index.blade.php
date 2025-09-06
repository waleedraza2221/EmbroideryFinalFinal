@extends('layouts.app')
@section('title','Testimonials')
@section('content')
<div class="max-w-6xl mx-auto px-6">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Testimonials</h1>
    <a href="{{ route('admin.testimonials.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Add</a>
  </div>
  @if(session('success'))<div class="mb-4 text-sm bg-green-100 text-green-700 px-3 py-2 rounded">{{ session('success') }}</div>@endif
  <div class="overflow-x-auto bg-white rounded shadow">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2 text-left">Order</th>
          <th class="px-3 py-2 text-left">Name</th>
          <th class="px-3 py-2 text-left">Quote</th>
          <th class="px-3 py-2">Active</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($items as $t)
          <tr>
            <td class="px-3 py-2">{{ $t->display_order }}</td>
            <td class="px-3 py-2">{{ $t->name }}</td>
            <td class="px-3 py-2 max-w-md truncate" title="{{ $t->quote }}">{{ $t->quote }}</td>
            <td class="px-3 py-2 text-center">@if($t->is_active) <span class="text-green-600 font-semibold">Yes</span> @else <span class="text-gray-400">No</span> @endif</td>
            <td class="px-3 py-2 text-right space-x-2">
              <a href="{{ route('admin.testimonials.edit',$t) }}" class="text-indigo-600 hover:underline">Edit</a>
              <form action="{{ route('admin.testimonials.destroy',$t) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Del</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">No testimonials.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection

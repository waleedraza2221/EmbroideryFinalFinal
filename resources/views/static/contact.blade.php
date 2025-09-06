@extends('layouts.landing')
@section('title','Contact Us')
@section('content')
<div class="max-w-4xl mx-auto px-6 py-16">
  <h1 class="text-3xl font-bold mb-6">Contact Us</h1>
  <p class="text-gray-600 dark:text-gray-300 mb-8">Provide ways for users to reach you. Replace with a working form if needed.</p>
  <form class="space-y-4 max-w-xl">
    <input type="text" placeholder="Name" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md" />
    <input type="email" placeholder="Email" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md" />
    <textarea rows="5" placeholder="Message" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-md"></textarea>
    <button class="px-5 py-2 rounded-md bg-indigo-600 text-white font-medium">Send</button>
  </form>
</div>
@endsection

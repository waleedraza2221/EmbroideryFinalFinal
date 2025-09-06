@php($unread = auth()->user()->unreadNotifications ?? collect())
<div class="relative" x-data="{open:false}">
  <button @click="open=!open" class="relative inline-flex items-center p-2 rounded hover:bg-gray-100 focus:outline-none">
    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    @if($unread->count())
      <span class="absolute -top-0 -right-0 bg-red-600 text-white text-xs rounded-full px-1">{{$unread->count()}}</span>
    @endif
  </button>
  <div x-cloak x-show="open" @click.away="open=false" class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-lg border border-gray-200 z-40">
    <div class="p-3 border-b flex items-center justify-between">
      <span class="font-semibold text-gray-700">Notifications</span>
      <form action="{{ route('notifications.markAllRead') }}" method="POST" class="ml-2">
        @csrf
        <button class="text-xs text-blue-600 hover:underline" @click.stop>Mark all read</button>
      </form>
    </div>
    <ul class="max-h-96 overflow-y-auto divide-y divide-gray-100">
      @forelse(auth()->user()->notifications()->latest()->limit(20)->get() as $n)
        @php($data = $n->data)
        <li class="p-3 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-blue-50' }}">
          <a href="{{ $data['action_url'] ?? '#' }}" class="block" @click.prevent="document.getElementById('mark-read-{{$n->id}}').submit()">
            <p class="text-sm text-gray-800">{{ $data['message'] ?? 'Notification' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</p>
          </a>
          <form id="mark-read-{{$n->id}}" action="{{ route('notifications.markRead', $n->id) }}" method="POST" class="hidden">
            @csrf
          </form>
        </li>
      @empty
        <li class="p-4 text-sm text-gray-500">No notifications</li>
      @endforelse
    </ul>
  </div>
</div>

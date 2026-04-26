@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <main class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-semibold mb-6">Lists</h1>

        @if (isset($error))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-700">
                {{ $error }}
            </div>
        @elseif (empty($lists))
            <p class="text-gray-500">No lists found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribers</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($lists as $list)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $list['listId'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $list['name'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $counts[$list['listId']] ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('lists.show', $list['listId']) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-gray-800 rounded hover:bg-gray-700">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </main>
@endsection

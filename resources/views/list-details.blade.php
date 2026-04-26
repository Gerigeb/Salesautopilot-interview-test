@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <main class="max-w-5xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">List #{{ $id }} – Subscribers</h1>
            <a href="{{ route('home') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-gray-800 rounded hover:bg-gray-700">
                &larr; Back to Lists
            </a>
        </div>

        @if (isset($error))
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-700">
                {{ $error }}
            </div>
        @elseif (empty($subscribers))
            <p class="text-gray-500">No subscribers found.</p>
        @else
            <input type="text" id="subscriber-search" placeholder="Search subscribers…"
                class="mb-4 w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-md">
                    <thead class="bg-gray-50">
                        <tr>
                            <th data-col="0" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100">
                                ID <span class="sort-icon">↕</span>
                            </th>
                            <th data-col="1" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100">
                                Email <span class="sort-icon">↕</span>
                            </th>
                            <th data-col="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100">
                                First Name <span class="sort-icon">↕</span>
                            </th>
                            <th data-col="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100">
                                Last Name <span class="sort-icon">↕</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="subscriber-tbody" class="bg-white divide-y divide-gray-100">
                        @foreach ($subscribers as $subscriber)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $subscriber['id'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $subscriber['email'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $subscriber['mssys_firstname'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $subscriber['mssys_lastname'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <script>
                (function () {
                    const tbody = document.getElementById('subscriber-tbody');
                    const searchInput = document.getElementById('subscriber-search');
                    const headers = document.querySelectorAll('th[data-col]');

                    let sortCol = null;
                    let sortAsc = true;
                    let searchTerm = '';

                    function allRows() {
                        return Array.from(tbody.querySelectorAll('tr'));
                    }

                    function cellText(row, col) {
                        return (row.cells[col]?.textContent ?? '').trim().toLowerCase();
                    }

                    function applyFilterAndSort() {
                        let rows = allRows();

                        rows.forEach(row => {
                            const matches = searchTerm === '' || Array.from(row.cells).some(
                                cell => cell.textContent.trim().toLowerCase().includes(searchTerm)
                            );
                            row.style.display = matches ? '' : 'none';
                        });

                        if (sortCol === null) {
                            return;
                        }

                        const visibleRows = rows.filter(r => r.style.display !== 'none');
                        const hiddenRows = rows.filter(r => r.style.display === 'none');

                        visibleRows.sort((a, b) => {
                            const av = cellText(a, sortCol);
                            const bv = cellText(b, sortCol);
                            const numeric = !isNaN(av) && !isNaN(bv) && av !== '' && bv !== '';
                            const cmp = numeric ? Number(av) - Number(bv) : av.localeCompare(bv);
                            return sortAsc ? cmp : -cmp;
                        });

                        [...visibleRows, ...hiddenRows].forEach(row => tbody.appendChild(row));
                    }

                    searchInput.addEventListener('input', function () {
                        searchTerm = this.value.trim().toLowerCase();
                        applyFilterAndSort();
                    });

                    headers.forEach(th => {
                        th.addEventListener('click', function () {
                            const col = parseInt(this.dataset.col, 10);

                            if (sortCol === col) {
                                if (sortAsc) {
                                    sortAsc = false;
                                } else {
                                    sortCol = null;
                                    sortAsc = true;
                                }
                            } else {
                                sortCol = col;
                                sortAsc = true;
                            }

                            headers.forEach(h => {
                                const icon = h.querySelector('.sort-icon');
                                const hCol = parseInt(h.dataset.col, 10);
                                if (hCol === sortCol) {
                                    icon.textContent = sortAsc ? '↑' : '↓';
                                } else {
                                    icon.textContent = '↕';
                                }
                            });

                            applyFilterAndSort();
                        });
                    });
                })();
            </script>
        @endif
    </main>
@endsection

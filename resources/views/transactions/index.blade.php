@extends('layouts.app')

@section('title', 'Transactions — NEXTDEV POS')

@section('content')
    <div style="display:flex;flex-direction:column;height:100vh;overflow:hidden;">

        <div class="page-header">
            <div>
                <div class="page-title">Transactions</div>
                <div class="page-subtitle">SALES · HISTORY · REPORTS</div>
            </div>
            <div style="display:flex;gap:10px;">
                <input type="date" class="form-control" style="width:160px" id="dateFilter" onchange="filterDate()">
                <button class="btn btn-secondary" onclick="exportCSV()">↓ Export CSV</button>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-bar" style="grid-template-columns:repeat(4,1fr)">
            <div class="stat-item">
                <span class="stat-val" style="color:var(--green)" id="totalRevenue">
                    ₱{{ number_format($totalRevenue, 2) }}
                </span>
                <span class="stat-label">Total Revenue</span>
            </div>
            <div class="stat-item">
                <span class="stat-val">{{ $totalTransactions }}</span>
                <span class="stat-label">Transactions</span>
            </div>
            <div class="stat-item">
                <span class="stat-val" style="color:var(--accent)">
                    ₱{{ number_format($avgSale, 2) }}
                </span>
                <span class="stat-label">Avg. Sale</span>
            </div>
            <div class="stat-item">
                <span class="stat-val" style="color:var(--amber)">{{ $itemCount }}</span>
                <span class="stat-label">Items Sold</span>
            </div>
        </div>

        <!-- Table -->
        <div style="flex:1;overflow-y:auto;padding:24px 32px;">
            <div class="card">
                <table class="data-table" id="txTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Items</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th>Cash</th>
                            <th>Change</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr data-date="{{ $tx->created_at->format('Y-m-d') }}">
                                <td style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted)">
                                    #{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td style="font-family:var(--font-mono);font-size:11px;">
                                    {{ $tx->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span
                                        style="font-family:var(--font-mono);font-size:13px;font-weight:700">{{ $tx->item_count }}</span>
                                    <span style="font-size:11px;color:var(--text-muted);"> items</span>
                                </td>
                                <td style="font-family:var(--font-mono)">₱{{ number_format($tx->subtotal, 2) }}</td>
                                <td style="font-family:var(--font-mono);color:var(--red)">
                                    -₱{{ number_format($tx->discount, 2) }}</td>
                                <td style="font-family:var(--font-mono);color:var(--text-secondary)">
                                    ₱{{ number_format($tx->tax, 2) }}</td>
                                <td style="font-family:var(--font-mono);font-weight:700;color:var(--accent)">
                                    ₱{{ number_format($tx->total, 2) }}</td>
                                <td style="font-family:var(--font-mono)">₱{{ number_format($tx->cash, 2) }}</td>
                                <td style="font-family:var(--font-mono);color:var(--green)">
                                    ₱{{ number_format($tx->change, 2) }}</td>
                                <td><span class="badge badge-green">COMPLETE</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10"
                                    style="text-align:center;padding:60px;color:var(--text-muted);font-family:var(--font-mono);font-size:12px;">
                                    No transactions yet. <a href="{{ route('pos.index') }}"
                                        style="color:var(--accent)">Start selling →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Laravel Pagination Links -->
                <div
                    style="margin-top: 20px; display: flex; justify-content: end; gap: 8px; font-family: Arial, sans-serif;">
                    <style>
                        .pagination {
                            display: flex;
                            gap: 8px;
                            list-style: none;
                            padding: 0;
                            margin: 0;
                        }

                        .pagination li {
                            display: inline-block;
                        }

                        .pagination li a,
                        .pagination li span {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 36px;
                            height: 36px;
                            padding: 0 6px;
                            border-radius: 8px;
                            background: white;
                            border: 1px solid #e2e8f0;
                            color: #4a5568;
                            font-size: 14px;
                            font-weight: 500;
                            text-decoration: none;
                            transition: all 0.2s ease;
                        }

                        .pagination li a:hover {
                            background: #f7fafc;
                            border-color: #cbd5e0;
                            transform: translateY(-1px);
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        }

                        .pagination li.active span {
                            background: #4299e1;
                            border-color: #4299e1;
                            color: white;
                        }

                        .pagination li.disabled span {
                            background: #f7fafc;
                            color: #cbd5e0;
                            cursor: not-allowed;
                        }
                    </style>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function filterDate() {
            const date = document.getElementById('dateFilter').value;
            document.querySelectorAll('#txTable tbody tr[data-date]').forEach(row => {
                row.style.display = !date || row.dataset.date === date ? '' : 'none';
            });
        }

        function exportCSV() {
            const rows = [
                ['ID', 'Date', 'Items', 'Subtotal', 'Discount', 'Tax', 'Total', 'Cash', 'Change']
            ];
            document.querySelectorAll('#txTable tbody tr').forEach(row => {
                const cells = [...row.querySelectorAll('td')].map(td => td.textContent.trim());
                rows.push(cells.slice(0, 9));
            });
            const csv = rows.map(r => r.join(',')).join('\n');
            const a = document.createElement('a');
            a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
            a.download = `transactions_${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
        }
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Transactions — NEXUS POS')

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
            <span class="stat-val" style="color:var(--green)" id="totalRevenue">₱{{ number_format(array_sum(array_column($transactions, 'total')), 2) }}</span>
            <span class="stat-label">Total Revenue</span>
        </div>
        <div class="stat-item">
            <span class="stat-val">{{ count($transactions) }}</span>
            <span class="stat-label">Transactions</span>
        </div>
        <div class="stat-item">
            <span class="stat-val" style="color:var(--accent)">
                ₱{{ count($transactions) > 0 ? number_format(array_sum(array_column($transactions, 'total')) / count($transactions), 2) : '0.00' }}
            </span>
            <span class="stat-label">Avg. Sale</span>
        </div>
        <div class="stat-item">
            <span class="stat-val" style="color:var(--amber)">{{ array_sum(array_column($transactions, 'item_count')) }}</span>
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
                    <tr data-date="{{ substr($tx['created_at'], 0, 10) }}">
                        <td style="font-family:var(--font-mono);font-size:11px;color:var(--text-muted)">
                            #{{ str_pad($tx['id'], 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="font-family:var(--font-mono);font-size:11px;">{{ $tx['created_at'] }}</td>
                        <td>
                            <span style="font-family:var(--font-mono);font-size:13px;font-weight:700">{{ $tx['item_count'] }}</span>
                            <span style="font-size:11px;color:var(--text-muted);"> items</span>
                        </td>
                        <td style="font-family:var(--font-mono)">₱{{ number_format($tx['subtotal'], 2) }}</td>
                        <td style="font-family:var(--font-mono);color:var(--red)">-₱{{ number_format($tx['discount'], 2) }}</td>
                        <td style="font-family:var(--font-mono);color:var(--text-secondary)">₱{{ number_format($tx['tax'], 2) }}</td>
                        <td style="font-family:var(--font-mono);font-weight:700;color:var(--accent)">₱{{ number_format($tx['total'], 2) }}</td>
                        <td style="font-family:var(--font-mono)">₱{{ number_format($tx['cash'], 2) }}</td>
                        <td style="font-family:var(--font-mono);color:var(--green)">₱{{ number_format($tx['change'], 2) }}</td>
                        <td><span class="badge badge-green">COMPLETE</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:60px;color:var(--text-muted);font-family:var(--font-mono);font-size:12px;">
                            No transactions yet. <a href="{{ route('pos.index') }}" style="color:var(--accent)">Start selling →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
    const rows = [['ID','Date','Items','Subtotal','Discount','Tax','Total','Cash','Change']];
    document.querySelectorAll('#txTable tbody tr[data-date]').forEach(row => {
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

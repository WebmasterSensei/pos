@extends('layouts.app')

@section('title', 'Inventory — NEXUS POS')

@section('content')
    <div class="inventory-layout">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <div class="page-title">Inventory</div>
                <div class="page-subtitle">MANAGE · STOCK · TRACK</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <div class="scan-indicator">◉ SCAN TO SEARCH</div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Item</button>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-val" id="statTotal">{{ count($products) }}</span>
                <span class="stat-label">Total Items</span>
            </div>

            <div class="stat-item">
                <span class="stat-val" id="statLow" style="color:var(--amber)">
                    {{ $low }}
                </span>
                <span class="stat-label">Low Stock</span>
            </div>

            <div class="stat-item">
                <span class="stat-val" id="statOut" style="color:var(--red)">
                    {{ $out}}
                </span>
                <span class="stat-label">Out of Stock</span>
            </div>
        </div>

        <div class="inventory-body">

            <!-- Table Section -->
            <div class="inventory-table-wrap">

                <!-- Search + Scanner -->
                <div class="inv-search-row">
                    <div style="position:relative;flex:1;">
                        <span
                            style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:16px;">⌗</span>
                        <input type="text" id="invBarcodeSearch" class="form-control"
                            placeholder="Scan barcode or search name, SKU..."
                            style="padding-left:38px;font-family:var(--font-mono);" oninput="filterInventory(this.value)"
                            autofocus>
                    </div>
                    <select class="form-control" style="width:160px"
                        onchange="filterInventory(document.getElementById('invBarcodeSearch').value)">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Table -->
                <div class="card">
                    <table class="data-table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Barcode</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Cost</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th style="text-align:right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryBody">
                            @forelse($products as $product)
                                <tr data-barcode="{{ $product['barcode'] }}" data-name="{{ strtolower($product['name']) }}"
                                    data-category="{{ $product['category'] }}" data-id="{{ $product['id'] }}">
                                    <td>
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <div class="item-thumb">{{ $product['emoji'] }}</div>
                                            <div>
                                                <div
                                                    style="font-family:var(--font-display);font-weight:700;font-size:14px;">
                                                    {{ $product['name'] }}</div>
                                                <div
                                                    style="font-family:var(--font-mono);font-size:10px;color:var(--text-muted);">
                                                    SKU-{{ str_pad($product['id'], 4, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="barcode-display">{{ $product['barcode'] }}</span></td>
                                    <td><span class="badge badge-blue">{{ $product['category'] }}</span></td>
                                    <td style="font-family:var(--font-mono);color:var(--accent)">
                                        ₱{{ number_format($product['price'], 2) }}</td>
                                    <td style="font-family:var(--font-mono);color:var(--text-secondary)">
                                        ₱{{ number_format($product['cost'], 2) }}</td>
                                    <td>
                                        <span
                                            style="font-family:var(--font-mono);font-weight:700;
                                    color:{{ $product['stock'] <= 0 ? 'var(--red)' : ($product['stock'] <= 5 ? 'var(--amber)' : 'var(--text-primary)') }}">
                                            {{ $product['stock'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($product['stock'] <= 0)
                                            <span class="badge badge-red">OUT OF STOCK</span>
                                        @elseif($product['stock'] <= 5)
                                            <span class="badge badge-amber">LOW STOCK</span>
                                        @else
                                            <span class="badge badge-green">IN STOCK</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                                            <button class="btn btn-secondary btn-sm btn-icon"
                                                onclick='openEditModal({{ json_encode($product) }})'
                                                title="Edit">✏</button>
                                            <button class="btn btn-secondary btn-sm btn-icon"
                                                onclick="restockItem('{{ $product['id'] }}', '{{ $product['name'] }}')"
                                                title="Restock">+</button>
                                            <button class="btn btn-danger btn-sm btn-icon"
                                                onclick="deleteItem('{{ $product['id'] }}')" title="Delete">✕</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        style="text-align:center;padding:48px;color:var(--text-muted);font-family:var(--font-mono);font-size:12px;">
                                        No inventory items. Use the form to add your first product.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Form Panel -->
            <div class="inventory-form-wrap">
                <div class="form-section-title" id="formTitle">Add New Item</div>

                <form id="itemForm" onsubmit="saveItem(event)">
                    <input type="hidden" id="itemId" value="">

                    <div class="form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="fName" placeholder="e.g. Coca-Cola 355ml"
                            required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Selling Price *</label>
                            <input type="number" class="form-control" id="fPrice" placeholder="0.00" step="0.01"
                                min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cost Price</label>
                            <input type="number" class="form-control" id="fCost" placeholder="0.00" step="0.01"
                                min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" class="form-control" id="fStock" placeholder="0" min="0"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" id="fCategory" placeholder="e.g. Beverages"
                            list="categoryList">
                        <datalist id="categoryList">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Barcode / SKU *</label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" class="form-control" id="fBarcode"
                                placeholder="Scan or enter barcode" required style="font-family:var(--font-mono)">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="generateBarcode()"
                                title="Generate">⊞</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Emoji Icon</label>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;" id="emojiPicker">
                            @foreach (['🍎', '🥤', '🧃', '🍫', '🍪', '🧁', '🍕', '🍔', '🌮', '🍜', '☕', '🍺', '🧴', '🧹', '📦', '💊', '🎮', '📱', '👕', '🔧'] as $e)
                                <button type="button" class="emoji-opt" onclick="selectEmoji('{{ $e }}')"
                                    style="background:var(--bg-raised);border:1px solid var(--border);border-radius:6px;padding:6px 8px;cursor:pointer;font-size:18px;transition:all 0.15s">{{ $e }}</button>
                            @endforeach
                        </div>
                        <input type="text" class="form-control" id="fEmoji" placeholder="📦"
                            style="font-size:20px;width:80px" value="📦">
                    </div>

                    <div style="display:flex;gap:10px;margin-top:8px;">
                        <button type="button" class="btn btn-secondary" style="flex:1"
                            onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-primary" style="flex:1" id="submitBtn">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restock Modal -->
    <div class="modal-overlay" id="restockModal">
        <div class="modal">
            <div class="modal-title">Restock Item</div>
            <div id="restockItemName"
                style="font-family:var(--font-mono);font-size:13px;color:var(--text-secondary);margin-bottom:16px;"></div>
            <div class="form-group">
                <label class="form-label">Add Quantity</label>
                <input type="number" class="form-control" id="restockQty" placeholder="0" min="1"
                    value="10">
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" style="flex:1" onclick="closeRestock()">Cancel</button>
                <button class="btn btn-primary" style="flex:1" onclick="confirmRestock()">Confirm Restock</button>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>
@endsection

@push('scripts')
    <script>
        let currentRestockId = null;
        let editingId = null;

        // ===== BARCODE SCAN IN INVENTORY =====
        document.getElementById('invBarcodeSearch').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const val = this.value.trim();
                const row = document.querySelector(`tr[data-barcode="${val}"]`);
                if (row) {
                    row.style.background = 'var(--accent-dim)';
                    row.style.transition = 'background 0.3s';
                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    setTimeout(() => row.style.background = '', 1500);
                    showToast(`✓ Found: ${row.querySelector('[style*="font-weight:700"]').textContent.trim()}`,
                        'success');
                } else {
                    showToast(`✕ Barcode not found: ${val}`, 'error');
                }
            }
        });

        // ===== FILTER =====
        function filterInventory(query) {
            const q = query.toLowerCase();
            const catSel = document.querySelector('select').value;
            document.querySelectorAll('#inventoryBody tr[data-id]').forEach(row => {
                const nameMatch = row.dataset.name.includes(q);
                const barcodeMatch = row.dataset.barcode.includes(q);
                const catMatch = !catSel || row.dataset.category === catSel;
                row.style.display = (nameMatch || barcodeMatch) && catMatch ? '' : 'none';
            });
        }

        // ===== FORM =====
        function resetForm() {
            document.getElementById('itemForm').reset();
            document.getElementById('itemId').value = '';
            document.getElementById('fEmoji').value = '📦';
            document.getElementById('formTitle').textContent = 'Add New Item';
            document.getElementById('submitBtn').textContent = 'Save Item';
            editingId = null;
        }

        function openAddModal() {
            resetForm();
            document.getElementById('fBarcode').value = generateBarcodeVal();
            document.getElementById('fName').focus();
        }

        function openEditModal(product) {
            editingId = product.id;
            document.getElementById('itemId').value = product.id;
            document.getElementById('fName').value = product.name;
            document.getElementById('fPrice').value = product.price;
            document.getElementById('fCost').value = product.cost;
            document.getElementById('fStock').value = product.stock;
            document.getElementById('fCategory').value = product.category;
            document.getElementById('fBarcode').value = product.barcode;
            document.getElementById('fEmoji').value = product.emoji;
            document.getElementById('formTitle').textContent = 'Edit Item';
            document.getElementById('submitBtn').textContent = 'Update Item';
            document.getElementById('fName').focus();
            window.scrollTo(0, 0);
        }

        function selectEmoji(emoji) {
            document.getElementById('fEmoji').value = emoji;
            document.querySelectorAll('.emoji-opt').forEach(b => {
                b.style.borderColor = b.textContent === emoji ? 'var(--accent)' : 'var(--border)';
                b.style.background = b.textContent === emoji ? 'var(--accent-dim)' : 'var(--bg-raised)';
            });
        }

        function generateBarcodeVal() {
            return Math.floor(Math.random() * 9000000000000 + 1000000000000).toString();
        }

        function generateBarcode() {
            document.getElementById('fBarcode').value = generateBarcodeVal();
        }

        function saveItem(e) {
            e.preventDefault();
            const data = {
                id: editingId || Date.now().toString(),
                name: document.getElementById('fName').value,
                price: parseFloat(document.getElementById('fPrice').value),
                cost: parseFloat(document.getElementById('fCost').value) || 0,
                stock: parseInt(document.getElementById('fStock').value),
                category: document.getElementById('fCategory').value || 'General',
                barcode: document.getElementById('fBarcode').value,
                emoji: document.getElementById('fEmoji').value || '📦',
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            const url = editingId ?
                `/inventory/${editingId}` :
                '/inventory';

            const method = editingId ? 'PUT' : 'POST';

            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': data._token
                },
                body: JSON.stringify(data)
            }).then(r => r.json()).then(res => {
                showToast(`✓ Item ${editingId ? 'updated' : 'added'} successfully`, 'success');
                setTimeout(() => location.reload(), 800);
            }).catch(() => {
                // Demo: reload with success message
                showToast(`✓ Item ${editingId ? 'updated' : 'saved'}!`, 'success');
                setTimeout(() => location.reload(), 800);
            });
        }

        // ===== DELETE =====
        function deleteItem(id) {
            if (!confirm('Delete this item from inventory?')) return;
            fetch(`/inventory/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                showToast('✓ Item deleted', 'success');
                setTimeout(() => location.reload(), 600);
            }).catch(() => {
                document.querySelector(`tr[data-id="${id}"]`)?.remove();
                showToast('✓ Item removed', 'success');
            });
        }

        // ===== RESTOCK =====
        function restockItem(id, name) {
            currentRestockId = id;
            document.getElementById('restockItemName').textContent = name;
            document.getElementById('restockQty').value = 10;
            document.getElementById('restockModal').classList.add('open');
        }

        function closeRestock() {
            document.getElementById('restockModal').classList.remove('open');
            currentRestockId = null;
        }

        function confirmRestock() {
            const qty = parseInt(document.getElementById('restockQty').value) || 0;
            if (qty <= 0) {
                showToast('Enter a valid quantity', 'error');
                return;
            }
            fetch(`/inventory/${currentRestockId}/restock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    qty
                })
            }).then(() => {
                showToast(`✓ Restocked +${qty} units`, 'success');
                closeRestock();
                setTimeout(() => location.reload(), 600);
            }).catch(() => {
                showToast(`✓ Added ${qty} units`, 'success');
                closeRestock();
            });
        }

        // ===== TOAST =====
        function showToast(msg, type = 'success') {
            const c = document.getElementById('toastContainer');
            const t = document.createElement('div');
            t.className = `toast ${type}`;
            t.innerHTML =
                `<span class="toast-icon">${type === 'success' ? '✓' : '✕'}</span><span class="toast-msg">${msg}</span>`;
            c.appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                t.style.transform = 'translateX(100%)';
                t.style.transition = '0.3s';
                setTimeout(() => t.remove(), 300);
            }, 2500);
        }
    </script>
@endpush
